<?php

namespace App\Http\Controllers;

use App\Mail\SaleReceiptMail;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Traits\StockTrait;

class PosController extends Controller
{
    use StockTrait;

    /** Main POS terminal screen */
    public function index()
    {
        return view('pos.index');
    }

    /** AJAX: search products by name or barcode */
    public function searchProduct(Request $request)
    {
        $query      = $request->get('q', '');
        $showroomId = auth()->user()->showroom_id;

        if (!$showroomId) {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->leftJoin('showroom_product', function ($join) use ($showroomId) {
                $join->on('products.id', '=', 'showroom_product.product_id')
                     ->where('showroom_product.showroom_id', '=', $showroomId);
            })
            ->select(
                'products.id',
                'products.name',
                'products.barcode',
                'products.selling_price',
                DB::raw('COALESCE(showroom_product.stock_quantity, 0) as stock_quantity'),
                'products.unit',
                'products.image'
            )
            ->orderByRaw('CASE WHEN products.barcode = ? THEN 1 ELSE 2 END', [$query])
            ->orderBy('products.name')
            ->limit(50)
            ->get();

        return response()->json($products);
    }

    /** Process the sale checkout */
    public function checkout(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'discount_type'  => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'tax_percent'    => 'required|numeric|min:0|max:100',
            'paid_amount'    => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,other',
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'send_email'     => 'nullable|boolean',
            'points_redeemed'=> 'nullable|integer|min:0',
            'promo_code'     => 'nullable|string|max:255',
        ]);

        $showroomId = auth()->user()->showroom_id;
        if (!$showroomId) {
            return response()->json(['success' => false, 'message' => 'No showroom assigned to this user.'], 403);
        }

        DB::beginTransaction();

        try {
            $subtotal      = 0;
            $saleItemsData = [];

            // Pre-fetch all products and stock pivots to prevent N+1 queries
            $itemIds  = collect($request->items)->pluck('id')->toArray();
            $products = Product::whereIn('id', $itemIds)->get()->keyBy('id');
            $pivots   = DB::table('showroom_product')
                ->where('showroom_id', $showroomId)
                ->whereIn('product_id', $itemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($request->items as $item) {
                $product = $products->get($item['id']);
                if (!$product) {
                    throw new \Exception("Product not found: " . $item['id']);
                }

                $pivot        = $pivots->get($product->id);
                $currentStock = $pivot ? $pivot->stock_quantity : 0;

                if ($currentStock < $item['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for: {$product->name} (available: {$currentStock})"
                    ], 422);
                }

                $itemSubtotal = $product->selling_price * $item['qty'];
                $subtotal    += $itemSubtotal;

                $saleItemsData[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $product->selling_price,
                    'quantity'     => $item['qty'],
                    'subtotal'     => $itemSubtotal,
                ];

                // Deduct stock using shared helper
                ['before' => $beforeQty, 'after' => $afterQty] = $this->upsertShowroomStock(
                    $showroomId, $product->id, $currentStock - $item['qty']
                );

                // Log stock movement
                StockMovement::create([
                    'product_id'      => $product->id,
                    'user_id'         => auth()->id(),
                    'showroom_id'     => $showroomId,
                    'type'            => 'sale',
                    'quantity'        => -$item['qty'],
                    'before_quantity' => $beforeQty,
                    'after_quantity'  => $afterQty,
                    'reference'       => 'POS Sale',
                    'notes'           => 'Sold via POS terminal',
                ]);
            }

            // Check Promo Code
            $promoCodeStr = $request->promo_code;
            if ($promoCodeStr) {
                $promo = \App\Models\PromoCode::where('code', strtoupper($promoCodeStr))->first();
                if ($promo && $promo->isValid($subtotal)) {
                    $promo->increment('uses_count');
                    $discountType = $promo->type;
                    $discountValue = (float) $promo->value;
                } else {
                    $discountType   = $request->discount_type;
                    $discountValue  = (float) $request->discount_value;
                }
            } else {
                $discountType   = $request->discount_type;
                $discountValue  = (float) $request->discount_value;
            }

            // Calculate discount
            $discountAmount = ($discountType === 'percent')
                ? round($subtotal * $discountValue / 100, 2)
                : $discountValue;

            $afterDiscount = $subtotal - $discountAmount;

            // Calculate tax
            $taxPercent = (float) $request->tax_percent;
            $taxAmount  = round($afterDiscount * $taxPercent / 100, 2);

            $totalAmount  = $afterDiscount + $taxAmount;
            
            // Handle Customer & Loyalty Points Redemption
            $pointsRedeemed = (int) $request->points_redeemed;
            $customerId = $request->customer_id;
            $customer = null;
            if ($customerId) {
                $customer = \App\Models\Customer::find($customerId);
            } elseif ($request->customer_phone) {
                $customer = \App\Models\Customer::where('phone', $request->customer_phone)->first();
                $customerId = $customer?->id;
            }

            if ($pointsRedeemed > 0) {
                if (!$customer || $customer->loyalty_points < $pointsRedeemed) {
                    throw new \Exception("Insufficient loyalty points available.");
                }
                $redemptionValue = (float) setting('loyalty_redemption_value', 1);
                $pointsDiscount = $pointsRedeemed * $redemptionValue;
                
                // Deduct points discount from total amount (but don't go below 0)
                $totalAmount = max(0, $totalAmount - $pointsDiscount);
                // We add the points discount to the overall discount_amount for accounting
                $discountAmount += $pointsDiscount;
            }

            // Calculate Points Earned based on Final Total
            $loyaltyEarningRate = (float) setting('loyalty_earning_rate', 100);
            $pointsEarned = 0;
            if ($loyaltyEarningRate > 0) {
                $pointsEarned = (int) floor($totalAmount / $loyaltyEarningRate);
            }

            // Update Customer Points
            if ($customer) {
                $customer->loyalty_points = $customer->loyalty_points - $pointsRedeemed + $pointsEarned;
                $customer->save();
            }

            $paidAmount   = (float) $request->paid_amount;
            $changeAmount = max(0, $paidAmount - $totalAmount);



            // Create Sale
            $sale = Sale::create([
                'invoice_number'  => Sale::generateInvoiceNumber(),
                'user_id'         => auth()->id(),
                'showroom_id'     => $showroomId,
                'customer_id'     => $customerId,
                'customer_name'   => $request->customer_name,
                'customer_phone'  => $request->customer_phone,
                'subtotal'        => $subtotal,
                'discount_type'   => $discountType,
                'discount_value'  => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_percent'     => $taxPercent,
                'tax_amount'      => $taxAmount,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $changeAmount,
                'payment_method'  => $request->payment_method,
                'notes'           => $request->notes,
                'status'          => 'completed',
                'points_earned'   => $pointsEarned,
                'points_redeemed' => $pointsRedeemed,
            ]);

            // Create sale items
            foreach ($saleItemsData as $itemData) {
                $sale->items()->create($itemData);
            }

            DB::commit();

            // Dispatch queued e-bill email if requested
            $emailSent = false;
            if ($request->filled('customer_email') && $request->boolean('send_email')) {
                Mail::to($request->customer_email)->queue(new SaleReceiptMail($sale));
                $emailSent = true;
            }

            return response()->json([
                'success'     => true,
                'sale_id'     => $sale->id,
                'invoice'     => $sale->invoice_number,
                'receipt_url' => route('pos.receipt', $sale->id),
                'email_sent'  => $emailSent,
                'email'       => $emailSent ? $request->customer_email : null,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Show printable receipt */
    public function receipt(Sale $sale)
    {
        $sale->load('items', 'cashier');
        return view('pos.receipt', compact('sale'));
    }

    /** Sales history with optional status/date/invoice filters */
    public function history(Request $request)
    {
        $query = Sale::with(['cashier', 'items'])->latest();

        // If user belongs to a showroom, they only see their showroom's sales.
        // If they are an Admin without a showroom, they see all.
        if (auth()->user()->showroom_id) {
            $query->where('showroom_id', auth()->user()->showroom_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('invoice')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice . '%');
        }

        // Fix #11 — filter by sale status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->get('export')) {
            $exportSales = $query->get();
            if ($request->get('export') === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.sales_history_pdf', ['sales' => $exportSales]);
                return $pdf->download('sales-history-' . date('Y-m-d') . '.pdf');
            }
            if ($request->get('export') === 'csv') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\SalesHistoryExport($exportSales), 
                    'sales-history-' . date('Y-m-d') . '.csv'
                );
            }
        }

        $sales = $query->paginate(20)->withQueryString();

        $todayTotalQuery = Sale::whereDate('created_at', today());
        if (auth()->user()->showroom_id) {
            $todayTotalQuery->where('showroom_id', auth()->user()->showroom_id);
        }
        $todayTotal = $todayTotalQuery->sum('total_amount');

        return view('pos.history', compact('sales', 'todayTotal'));
    }

    /** Send (or re-send) the e-bill receipt email for a given sale */
    public function sendReceipt(Request $request, Sale $sale)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        Mail::to($request->email)->queue(new SaleReceiptMail($sale));

        return response()->json([
            'success' => true,
            'message' => 'E-Bill queued to ' . $request->email,
        ]);
    }

    /** Process a full refund for a completed sale (Admin only) */
    public function refund(Sale $sale)
    {
        if ($sale->status !== 'completed') {
            return back()->with('error', 'This sale has already been refunded.');
        }

        DB::beginTransaction();

        try {
            $sale->load('items');

            foreach ($sale->items as $item) {
                $showroomId = $sale->showroom_id;
                $newStock   = DB::table('showroom_product')
                    ->where('showroom_id', $showroomId)
                    ->where('product_id', $item->product_id)
                    ->value('stock_quantity') ?? 0;

                ['before' => $before, 'after' => $after] = $this->upsertShowroomStock(
                    $showroomId, $item->product_id, $newStock + $item->quantity
                );

                // Log return stock movement
                StockMovement::create([
                    'product_id'      => $item->product_id,
                    'user_id'         => auth()->id(),
                    'showroom_id'     => $showroomId,
                    'type'            => 'return',
                    'quantity'        => $item->quantity,
                    'before_quantity' => $before,
                    'after_quantity'  => $after,
                    'reference'       => "Refund - {$sale->invoice_number}",
                    'notes'           => "Refunded sale {$sale->invoice_number}",
                ]);
            }

            // Mark sale as refunded
            $sale->update(['status' => 'refunded']);

            DB::commit();

            return back()->with('success', "Sale {$sale->invoice_number} has been refunded. Stock has been restored.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }

    /** Process a partial refund for a sale */
    public function partialRefund(Request $request, Sale $sale)
    {
        $request->validate([
            'items'       => 'required|array|min:1',
            'items.*.id'  => 'required|exists:sale_items,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if (!in_array($sale->status, ['completed', 'partially_refunded'])) {
            return response()->json(['success' => false, 'message' => 'Invalid sale status for refund.'], 400);
        }

        DB::beginTransaction();

        try {
            $sale->load('items');
            $refundAmount = 0;
            $showroomId   = $sale->showroom_id;

            foreach ($request->items as $reqItem) {
                $saleItem = $sale->items->firstWhere('id', $reqItem['id']);
                if (!$saleItem) {
                    throw new \Exception("Item ID {$reqItem['id']} does not belong to this sale.");
                }

                $availableToRefund = $saleItem->quantity - $saleItem->refunded_quantity;
                if ($reqItem['qty'] > $availableToRefund) {
                    throw new \Exception("Cannot refund {$reqItem['qty']} of {$saleItem->product_name}. Only {$availableToRefund} available to refund.");
                }

                $refundQty        = $reqItem['qty'];
                $itemRefundAmount  = $refundQty * $saleItem->unit_price;
                $refundAmount     += $itemRefundAmount;

                // Update sale item refunded_quantity (now in $fillable so this works)
                $saleItem->update([
                    'refunded_quantity' => $saleItem->refunded_quantity + $refundQty
                ]);

                // Restore stock using shared helper
                $currentStock = DB::table('showroom_product')
                    ->where('showroom_id', $showroomId)
                    ->where('product_id', $saleItem->product_id)
                    ->value('stock_quantity') ?? 0;

                ['before' => $before, 'after' => $after] = $this->upsertShowroomStock(
                    $showroomId, $saleItem->product_id, $currentStock + $refundQty
                );

                // Log return stock movement
                StockMovement::create([
                    'product_id'      => $saleItem->product_id,
                    'user_id'         => auth()->id(),
                    'showroom_id'     => $showroomId,
                    'type'            => 'return',
                    'quantity'        => $refundQty,
                    'before_quantity' => $before,
                    'after_quantity'  => $after,
                    'reference'       => "Partial Refund - {$sale->invoice_number}",
                    'notes'           => "Refunded {$refundQty}x {$saleItem->product_name}",
                ]);
            }

            // Update sale refunded_amount (now in $fillable so this works)
            $sale->update([
                'refunded_amount' => $sale->refunded_amount + $refundAmount,
                'status'          => 'partially_refunded'
            ]);

            // Check if fully refunded
            $totalOriginalItems = $sale->items->sum('quantity');
            $totalRefundedItems = $sale->fresh()->items->sum('refunded_quantity');

            if ($totalRefundedItems >= $totalOriginalItems) {
                $sale->update(['status' => 'refunded']);
            }

            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Successfully refunded ' . setting('currency_symbol', 'Rs.') . ' ' . number_format($refundAmount, 2),
                'refunded_amount' => $refundAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Park/Hold a sale */
    public function holdSale(Request $request)
    {
        $request->validate([
            'reference_note' => 'required|string|max:255',
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'customer_id'    => 'nullable|exists:customers,id',
        ]);

        $showroomId = auth()->user()->showroom_id;
        if (!$showroomId) {
            return response()->json(['success' => false, 'message' => 'No showroom assigned to this user.'], 403);
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $itemsData = [];

            $itemIds = collect($request->items)->pluck('id')->toArray();
            $products = Product::whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($request->items as $reqItem) {
                $product = $products->get($reqItem['id']);
                $subtotal = $product->selling_price * $reqItem['qty'];
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity'   => $reqItem['qty'],
                    'unit_price' => $product->selling_price,
                    'subtotal'   => $subtotal,
                ];
            }

            $heldSale = \App\Models\HeldSale::create([
                'reference_note' => $request->reference_note,
                'customer_id'    => $request->customer_id,
                'showroom_id'    => $showroomId,
                'user_id'        => auth()->id(),
                'total_amount'   => $totalAmount,
            ]);

            foreach ($itemsData as &$item) {
                $item['held_sale_id'] = $heldSale->id;
                $item['created_at']   = now();
                $item['updated_at']   = now();
            }

            \App\Models\HeldSaleItem::insert($itemsData);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Sale parked successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Fetch parked sales */
    public function getHeldSales()
    {
        $showroomId = auth()->user()->showroom_id;
        if (!$showroomId) return response()->json([]);

        $heldSales = \App\Models\HeldSale::with(['items.product', 'customer'])
            ->where('showroom_id', $showroomId)
            ->latest()
            ->get();

        return response()->json($heldSales);
    }

    /** Delete parked sale */
    public function deleteHeldSale($id)
    {
        $showroomId = auth()->user()->showroom_id;
        $heldSale = \App\Models\HeldSale::where('id', $id)->where('showroom_id', $showroomId)->firstOrFail();
        $heldSale->delete();

        return response()->json(['success' => true]);
    }

    /** Validate Promo Code */
    public function validatePromo(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'cart_total' => 'required|numeric'
        ]);

        $promo = \App\Models\PromoCode::where('code', strtoupper($request->code))->first();

        if (!$promo || !$promo->isValid($request->cart_total)) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired promo code.']);
        }

        return response()->json([
            'success' => true,
            'type' => $promo->type,
            'value' => $promo->value,
            'message' => 'Promo code applied!'
        ]);
    }
}
