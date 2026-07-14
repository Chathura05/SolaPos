<?php

namespace App\Http\Controllers;

use App\Models\ShowroomDispatch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\StockTrait;

class DispatchController extends Controller
{
    use StockTrait;

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ShowroomDispatch::with(['admin', 'showroom', 'items.product'])->latest();

        if ($user->hasRole('Cashier') && $user->showroom_id) {
            $query->where('showroom_id', $user->showroom_id);
        } elseif ($request->filled('showroom_id')) {
            $query->where('showroom_id', $request->showroom_id);
        }

        $dispatches = $query->paginate(20);
        $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);

        return view('dispatches.index', compact('dispatches', 'showrooms'));
    }

    public function show(ShowroomDispatch $dispatch)
    {
        $user = auth()->user();
        if ($user->hasRole('Cashier') && $user->showroom_id !== $dispatch->showroom_id) {
            abort(403, 'Unauthorized action.');
        }

        $dispatch->load(['admin', 'showroom', 'items.product']);
        return view('dispatches.show', compact('dispatch'));
    }

    public function accept(ShowroomDispatch $dispatch)
    {
        $user = auth()->user();
        
        // Ensure only the correct showroom cashier or admin can accept
        if ($user->hasRole('Cashier') && $user->showroom_id !== $dispatch->showroom_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($dispatch->status !== 'pending') {
            return back()->with('error', 'This dispatch is no longer pending.');
        }

        DB::beginTransaction();

        try {
            $dispatch->load('items.product');

            foreach ($dispatch->items as $item) {
                // Get current stock
                $currentStock = DB::table('showroom_product')
                    ->where('showroom_id', $dispatch->showroom_id)
                    ->where('product_id', $item->product_id)
                    ->value('stock_quantity') ?? 0;

                // Upsert stock
                $result = $this->upsertShowroomStock(
                    $dispatch->showroom_id,
                    $item->product_id,
                    $currentStock + $item->quantity
                );

                // Log movement
                StockMovement::create([
                    'product_id'      => $item->product_id,
                    'user_id'         => $user->id,
                    'showroom_id'     => $dispatch->showroom_id,
                    'type'            => 'stock_in',
                    'quantity'        => $item->quantity,
                    'before_quantity' => $result['before'],
                    'after_quantity'  => $result['after'],
                    'reference'       => 'Dispatch: ' . $dispatch->reference_number,
                    'notes'           => 'Accepted dispatch order.',
                ]);
            }

            $dispatch->update(['status' => 'accepted']);

            DB::commit();

            return redirect()->route('dispatches.index')->with('success', "Dispatch {$dispatch->reference_number} accepted successfully. Stock updated.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error accepting dispatch: ' . $e->getMessage());
        }
    }
}
