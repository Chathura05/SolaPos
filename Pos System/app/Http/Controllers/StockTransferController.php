<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Showroom;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\StockTrait;

class StockTransferController extends Controller
{
    use StockTrait;

    /** Show transfer form */
    public function form()
    {
        $products  = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'barcode', 'unit']);
        $showrooms = Showroom::orderBy('name')->get(['id', 'name']);

        return view('inventory.transfer', compact('products', 'showrooms'));
    }

    /** Process stock transfer between showrooms */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_id'          => 'required|exists:products,id',
            'source_showroom_id'  => 'required|exists:showrooms,id',
            'dest_showroom_id'    => 'required|exists:showrooms,id|different:source_showroom_id',
            'quantity'            => 'required|integer|min:1',
            'notes'               => 'nullable|string',
        ]);

        $product  = Product::findOrFail($request->product_id);
        $sourceId = $request->source_showroom_id;
        $destId   = $request->dest_showroom_id;
        $qty      = $request->quantity;

        // Check source stock
        $sourcePivot = DB::table('showroom_product')
            ->where('showroom_id', $sourceId)
            ->where('product_id', $product->id)
            ->first();

        $sourceStock = $sourcePivot ? $sourcePivot->stock_quantity : 0;

        if ($sourceStock < $qty) {
            return back()->withInput()
                ->with('error', "Insufficient stock in source showroom. Available: {$sourceStock} {$product->unit}");
        }

        DB::beginTransaction();

        try {
            $sourceShowroom = Showroom::findOrFail($sourceId);
            $destShowroom   = Showroom::findOrFail($destId);
            $reference      = "Transfer: {$sourceShowroom->name} → {$destShowroom->name}";
            $notes          = $request->notes;

            // Deduct from source using shared helper
            ['before' => $sourceBefore, 'after' => $sourceAfter] = $this->upsertShowroomStock(
                $sourceId, $product->id, $sourceStock - $qty
            );

            // Read dest before-quantity, then add
            $destPivot  = DB::table('showroom_product')
                ->where('showroom_id', $destId)
                ->where('product_id', $product->id)
                ->first();
            $destBefore = $destPivot ? $destPivot->stock_quantity : 0;

            $this->upsertShowroomStock($destId, $product->id, $destBefore + $qty);

            // Log source movement as transfer_out
            StockMovement::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'showroom_id'     => $sourceId,
                'type'            => 'transfer_out',
                'quantity'        => -$qty,
                'before_quantity' => $sourceBefore,
                'after_quantity'  => $sourceAfter,
                'reference'       => $reference,
                'notes'           => $notes ?? "Transferred to {$destShowroom->name}",
            ]);

            // Log destination movement as transfer_in
            StockMovement::create([
                'product_id'      => $product->id,
                'user_id'         => auth()->id(),
                'showroom_id'     => $destId,
                'type'            => 'transfer_in',
                'quantity'        => $qty,
                'before_quantity' => $destBefore,
                'after_quantity'  => $destBefore + $qty,
                'reference'       => $reference,
                'notes'           => $notes ?? "Transferred from {$sourceShowroom->name}",
            ]);

            DB::commit();

            return redirect()->route('inventory.index')
                ->with('success', "Transferred {$qty} {$product->unit} of {$product->name} from {$sourceShowroom->name} to {$destShowroom->name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}
