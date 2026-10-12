<?php

namespace App\Http\Controllers;

use App\Mail\SaleReceiptMail;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Traits\StockTrait;
use App\Services\SaleRefundService;

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
            'items.*.id'     => 'required|integer|distinct|exists:products,id',
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

        if ($request->discount_type === 'percent' && (float) $request->discount_value > 100) {
            throw ValidationException::withMessages([
                'discount_value' => 'A percentage discount cannot exceed 100%.',
            ]);
        }

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
            $products = Product::whereIn('id', $itemIds)->where('is_active', true)->get()->keyBy('id');
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

                $itemSubtotal = round($product->selling_price * $item['qty'], 2);
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
                $promo = \App\Models\PromoCode::where('code', strtoupper($promoCodeStr))->lockForUpdate()->first();
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

            $discountAmount = round(min($subtotal, max(0, $discountAmount)), 2);

            $afterDiscount = $subtotal - $discountAmount;

            // Handle Customer & Loyalty Points Redemption
            $pointsRedeemed = (int) $request->points_redeemed;
            $customerId = $request->customer_id;
            $customer = null;
            if ($customerId) {
                $customer = \App\Models\Customer::lockForUpdate()->find($customerId);
                if (!$customer) {
                    throw ValidationException::withMessages(['customer_id' => 'The selected customer is unavailable.']);
                }
            } elseif ($request->customer_phone) {
                $customer = \App\Models\Customer::where('phone', $request->customer_phone)->lockForUpdate()->first();
                $customerId = $customer?->id;
            }

            if ($customer && $customer->showroom_id && $customer->showroom_id !== $showroomId) {
                throw ValidationException::withMessages([
                    'customer_id' => 'The selected customer does not belong to this showroom.',
                ]);
            }

            if ($pointsRedeemed > 0) {
                if (!$customer || $customer->loyalty_points < $pointsRedeemed) {
                    throw ValidationException::withMessages(['points_redeemed' => 'Insufficient loyalty points available.']);
                }
                $redemptionValue = (float) setting('loyalty_redemption_value', 1);
                $pointsDiscount = round($pointsRedeemed * $redemptionValue, 2);
                if ($redemptionValue <= 0 || $pointsDiscount > $afterDiscount) {
                    throw ValidationException::withMessages(['points_redeemed' => 'Redeemed points cannot exceed the remaining purchase amount.']);
                }
                $afterDiscount -= $pointsDiscount;
                $discountAmount += $pointsDiscount;
            }

            // Match the terminal: all discounts, including loyalty, reduce the taxable amount.
            $taxPercent = (float) $request->tax_percent;
            $taxAmount = round($afterDiscount * $taxPercent / 100, 2);
            $totalAmount = round($afterDiscount + $taxAmount, 2);

            // Calculate Points Earned based on Final Total
            $loyaltyEarningRate = (float) setting('loyalty_earning_rate', 100);
            $pointsEarned = 0;
            if ($loyaltyEarningRate > 0) {
                $pointsEarned = (int) floor($totalAmount / $loyaltyEarningRate);
            }

            $paidAmount   = round((float) $request->paid_amount, 2);
            $changeAmount = max(0, $paidAmount - $totalAmount);

            if ($paidAmount < $totalAmount && !$customer) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'A customer is required when recording an outstanding balance.',
                ]);
            }

            // Update Customer Points & Balance
            if ($customer) {
                $customer->loyalty_points = $customer->loyalty_points - $pointsRedeemed + $pointsEarned;
                
                if ($paidAmount < $totalAmount) {
                    $customer->balance += ($totalAmount - $paidAmount);
                }
                
                $customer->save();
            }



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

            // Dispatch e-bill email if requested
            $emailSent = false;
            $emailError = null;
            if ($request->filled('customer_email') && $request->boolean('send_email')) {
                try {
                    Mail::to($request->customer_email)->send(new SaleReceiptMail($sale));
                    $emailSent = true;
                } catch (\Exception $mailEx) {
                    $emailError = $mailEx->getMessage();
                    \Log::error('SaleReceiptMail failed for sale #' . $sale->id . ': ' . $mailEx->getMessage());
                }
            }

            return response()->json([
                'success'     => true,
                'sale_id'     => $sale->id,
                'invoice'     => $sale->invoice_number,
                'receipt_url' => route('pos.receipt', $sale->id),
                'email_sent'  => $emailSent,
                'email'       => $emailSent ? $request->customer_email : null,
                'email_error' => $emailError,
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS checkout failed', ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Checkout failed. Please try again.'], 500);
        }
    }

    /** Show printable receipt */
    public function receipt(Sale $sale)
    {
        $this->authorize('view', $sale);
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
        $this->authorize('view', $sale);
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        Mail::to($request->email)->queue(new SaleReceiptMail($sale));

        return response()->json([
            'success' => true,
            'message' => 'E-Bill queued to ' . $request->email,
        ]);
    }

    /** Return all items that have not already been refunded. */
    public function refund(Sale $sale, SaleRefundService $refunds)
    {
        try {
            $result = $refunds->refund($sale);
            return back()->with('success', $this->refundMessage($result) . ' Stock has been restored.');
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            Log::error('Full refund failed', ['sale_id' => $sale->id, 'exception' => $e]);
            return back()->with('error', 'Refund failed. Please try again.');
        }
    }

    public function partialRefund(Request $request, Sale $sale, SaleRefundService $refunds)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|distinct|exists:sale_items,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        try {
            $result = $refunds->refund($sale, $request->items);
            return response()->json(array_merge($result, [
                'success' => true,
                'message' => $this->refundMessage($result),
            ]));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Partial refund failed', ['sale_id' => $sale->id, 'exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Refund failed. Please try again.'], 500);
        }
    }

    private function refundMessage(array $result): string
    {
        $currency = setting('currency_symbol', 'Rs.');
        if ($result['balance_cancelled'] > 0) {
            return 'Refund value ' . $currency . ' ' . number_format($result['refunded_amount'], 2)
                . ': return ' . $currency . ' ' . number_format($result['cash_refund'], 2)
                . ' to the customer; ' . $currency . ' ' . number_format($result['balance_cancelled'], 2)
                . ' outstanding balance cancelled.';
        }

        return 'Successfully refunded ' . $currency . ' ' . number_format($result['refunded_amount'], 2);
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
            Log::error('Holding sale failed', ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Unable to hold the sale. Please try again.'], 500);
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
