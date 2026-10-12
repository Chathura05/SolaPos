<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShowroomReturn;
use App\Models\StockMovement;
use App\Traits\StockTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShowroomReturnController extends Controller
{
    use StockTrait;

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ShowroomReturn::with(['showroom', 'items.product'])->latest();

        // If Cashier, only show their own showroom returns
        if (!$user->hasRole('Admin')) {
            $query->where('showroom_id', $user->showroom_id);
        }

        $returns = $query->paginate(20);
        return view('returns.index', compact('returns'));
    }

    public function create()
    {
        $user = auth()->user();
        
        if (!$user->showroom_id) {
            return redirect()->route('dashboard')->with('error', 'Only showroom users can create returns.');
        }

        // Only active products
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('returns.create', compact('products'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->showroom_id) {
            return redirect()->route('dashboard')->with('error', 'Only showroom users can create returns.');
        }

        $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|distinct|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.reason' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $user) {
            $return = ShowroomReturn::create([
                'reference_number' => ShowroomReturn::generateReferenceNumber(),
                'showroom_id' => $user->showroom_id,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $return->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }
        });

        return redirect()->route('returns.index')->with('success', 'Return request submitted successfully.');
    }

    public function show($id)
    {
        $return = ShowroomReturn::with(['showroom', 'items.product', 'admin'])->findOrFail($id);
        if (!auth()->user()->hasRole('Admin') && auth()->user()->showroom_id !== $return->showroom_id) {
            abort(403);
        }
        return view('returns.show', compact('return'));
    }

    public function accept(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        DB::transaction(function () use ($id, $request) {
            $return = ShowroomReturn::lockForUpdate()->findOrFail($id);
            if ($return->status !== 'pending') {
                throw ValidationException::withMessages(['return' => 'Only pending returns can be accepted.']);
            }

            // Group existing requests too, since older requests may contain duplicate products.
            $items = $return->items()->orderBy('product_id')->get()->groupBy('product_id');
            foreach ($items as $productId => $productItems) {
                $quantity = (int) $productItems->sum('quantity');
                $currentStock = DB::table('showroom_product')
                    ->where('showroom_id', $return->showroom_id)
                    ->where('product_id', $productId)
                    ->lockForUpdate()->value('stock_quantity') ?? 0;
                if ($currentStock < $quantity) {
                    throw ValidationException::withMessages(['items' => 'Insufficient stock to accept this return.']);
                }

                $stock = $this->upsertShowroomStock($return->showroom_id, $productId, $currentStock - $quantity);
                StockMovement::create([
                    'product_id' => $productId,
                    'user_id' => auth()->id(),
                    'showroom_id' => $return->showroom_id,
                    'type' => 'return',
                    'quantity' => -$quantity,
                    'before_quantity' => $stock['before'],
                    'after_quantity' => $stock['after'],
                    'reference' => $return->reference_number,
                    'notes' => $productItems->pluck('reason')->filter()->implode('; '),
                ]);
            }

            $return->update([
                'status' => 'accepted',
                'admin_id' => auth()->id(),
                'notes' => $request->notes ? $return->notes . "\nAdmin Note: " . $request->notes : $return->notes,
            ]);
        }, 3);

        return redirect()->route('returns.index')->with('success', 'Return accepted and stock updated.');
    }

    public function reject(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        DB::transaction(function () use ($request, $id) {
            $return = ShowroomReturn::lockForUpdate()->findOrFail($id);
            if ($return->status !== 'pending') {
                throw ValidationException::withMessages(['return' => 'Only pending returns can be rejected.']);
            }
            $return->update(['status' => 'rejected', 'admin_id' => auth()->id(), 'notes' => $request->notes ?? $return->notes]);
        });

        return redirect()->route('returns.index')->with('success', 'Return request rejected.');
    }
}
