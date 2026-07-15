<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['user', 'customer'])->latest();

        if (auth()->user()->showroom_id) {
            $query->where('showroom_id', auth()->user()->showroom_id);
        }

        $quotes = $query->paginate(20);

        return view('quotes.index', compact('quotes'));
    }

    public function show(Quote $quote)
    {
        if (auth()->user()->showroom_id && $quote->showroom_id !== auth()->user()->showroom_id) {
            abort(403);
        }

        $quote->load('items.product', 'customer', 'user', 'showroom');
        return view('quotes.show', compact('quote'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'discount_type'  => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
            'tax_percent'    => 'required|numeric|min:0|max:100',
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'notes'          => 'nullable|string',
        ]);

        $showroomId = auth()->user()->showroom_id;
        if (!$showroomId) {
            return response()->json(['success' => false, 'message' => 'No showroom assigned.'], 403);
        }

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $itemsData = [];
            $itemIds = collect($request->items)->pluck('id')->toArray();
            $products = Product::whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($request->items as $reqItem) {
                $product = $products->get($reqItem['id']);
                if (!$product) throw new \Exception("Product not found");

                $itemSubtotal = $product->selling_price * $reqItem['qty'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'quantity'     => $reqItem['qty'],
                    'unit_price'   => $product->selling_price,
                    'subtotal'     => $itemSubtotal,
                ];
            }

            $discountAmount = ($request->discount_type === 'percent')
                ? round($subtotal * $request->discount_value / 100, 2)
                : $request->discount_value;

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = round($afterDiscount * $request->tax_percent / 100, 2);
            $totalAmount = $afterDiscount + $taxAmount;

            $quote = Quote::create([
                'quote_number'    => Quote::generateQuoteNumber(),
                'user_id'         => auth()->id(),
                'showroom_id'     => $showroomId,
                'customer_id'     => $request->customer_id,
                'customer_name'   => $request->customer_name,
                'customer_phone'  => $request->customer_phone,
                'subtotal'        => $subtotal,
                'discount_type'   => $request->discount_type,
                'discount_value'  => $request->discount_value,
                'discount_amount' => $discountAmount,
                'tax_percent'     => $request->tax_percent,
                'tax_amount'      => $taxAmount,
                'total_amount'    => $totalAmount,
                'notes'           => $request->notes,
                'status'          => 'pending',
            ]);

            foreach ($itemsData as $itemData) {
                $quote->items()->create($itemData);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Quote created successfully',
                'quote_id' => $quote->id,
                'print_url' => route('quotes.show', $quote)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Quote $quote)
    {
        abort(403, 'Deleting quotes is disabled.');
    }

    public function getJson(Quote $quote)
    {
        if (auth()->user()->showroom_id && $quote->showroom_id !== auth()->user()->showroom_id) {
            abort(403);
        }
        $quote->load('items.product', 'customer');
        return response()->json($quote);
    }

    public function convert(Quote $quote)
    {
        if (auth()->user()->showroom_id && $quote->showroom_id !== auth()->user()->showroom_id) {
            abort(403);
        }
        $quote->update(['status' => 'converted']);
        return response()->json(['success' => true]);
    }
}
