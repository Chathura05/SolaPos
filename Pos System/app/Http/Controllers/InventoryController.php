<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\StockTrait;

class InventoryController extends Controller
{
    use StockTrait;

    /** Inventory overview — all products with current stock */
    public function index(Request $request)
    {
        $user             = auth()->user();
        $selectedShowroom = $request->get('showroom_id');

        if ($user->hasRole('Cashier') && $user->showroom_id) {
            $showroomId = $user->showroom_id;
        } else {
            $showroomId = $selectedShowroom ?: null;
        }

        [$stockSql, $stockBindings] = $this->stockSubquery($showroomId);

        $query = Product::with('category')->select(
            'products.id', 'products.name', 'products.barcode', 'products.category_id',
            'products.reorder_level', 'products.unit', 'products.cost_price', 'products.selling_price', 'products.is_active'
        )->selectRaw("{$stockSql} as stock_quantity", $stockBindings);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereRaw("{$stockSql} <= reorder_level", $stockBindings)
                      ->whereRaw("{$stockSql} > 0", $stockBindings);
            } elseif ($request->stock_status === 'out') {
                $query->whereRaw("{$stockSql} <= 0", $stockBindings);
            }
        }

        $products        = $query->latest()->paginate(20)->withQueryString();
        $totalProducts   = Product::count();
        $lowStockCount   = Product::where('is_active', true)
            ->whereRaw("{$stockSql} <= reorder_level", $stockBindings)
            ->whereRaw("{$stockSql} > 0", $stockBindings)
            ->count();
        $outOfStockCount = Product::where('is_active', true)
            ->whereRaw("{$stockSql} <= 0", $stockBindings)
            ->count();

        $stockValueQuery = DB::table('showroom_product')
            ->join('products', 'products.id', '=', 'showroom_product.product_id');
        if ($showroomId) {
            $stockValueQuery->where('showroom_product.showroom_id', $showroomId);
        }
        $totalStockValue = $stockValueQuery->sum(DB::raw('showroom_product.stock_quantity * products.cost_price'));

        $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);

        return view('inventory.index', compact(
            'products', 'totalProducts', 'lowStockCount', 'outOfStockCount', 'totalStockValue', 'showrooms'
        ));
    }

    /** Shared helper: load products and showrooms for stock forms */
    private function stockFormData(): array
    {
        $user     = auth()->user();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'barcode', 'unit']);

        if ($user->hasRole('Cashier') && $user->showroom_id) {
            $showrooms = \App\Models\Showroom::where('id', $user->showroom_id)->get(['id', 'name']);
        } else {
            $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);
        }

        return compact('products', 'showrooms');
    }

    /** Show Stock In form */
    public function stockInForm()
    {
        return view('inventory.stock-in', $this->stockFormData());
    }

    /** Process Stock In */
    public function stockIn(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'showroom_id' => 'required|exists:showrooms,id',
            'quantity'    => 'required|integer|min:1',
            'reference'   => 'nullable|string|max:255',
            'notes'       => 'nullable|string',
        ]);

        $product    = Product::findOrFail($request->product_id);
        $showroomId = $request->showroom_id;

        $currentStock = DB::table('showroom_product')
            ->where('showroom_id', $showroomId)
            ->where('product_id', $product->id)
            ->value('stock_quantity') ?? 0;

        ['before' => $before, 'after' => $after] = $this->upsertShowroomStock(
            $showroomId, $product->id, $currentStock + $request->quantity
        );

        StockMovement::create([
            'product_id'      => $product->id,
            'user_id'         => auth()->id(),
            'showroom_id'     => $showroomId,
            'type'            => 'stock_in',
            'quantity'        => $request->quantity,
            'before_quantity' => $before,
            'after_quantity'  => $after,
            'reference'       => $request->reference,
            'notes'           => $request->notes,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', "Added {$request->quantity} units to {$product->name}. New stock: {$after}");
    }

    /** Show Stock Out form */
    public function stockOutForm()
    {
        return view('inventory.stock-out', $this->stockFormData());
    }

    /** Process Stock Out */
    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'showroom_id' => 'required|exists:showrooms,id',
            'quantity'    => 'required|integer|min:1',
            'reference'   => 'nullable|string|max:255',
            'notes'       => 'nullable|string',
        ]);

        $product    = Product::findOrFail($request->product_id);
        $showroomId = $request->showroom_id;

        $currentStock = DB::table('showroom_product')
            ->where('showroom_id', $showroomId)
            ->where('product_id', $product->id)
            ->value('stock_quantity') ?? 0;

        if ($currentStock < $request->quantity) {
            return back()->with('error', "Insufficient stock in this showroom. Available: {$currentStock} {$product->unit}");
        }

        ['before' => $before, 'after' => $after] = $this->upsertShowroomStock(
            $showroomId, $product->id, $currentStock - $request->quantity
        );

        StockMovement::create([
            'product_id'      => $product->id,
            'user_id'         => auth()->id(),
            'showroom_id'     => $showroomId,
            'type'            => 'stock_out',
            'quantity'        => -$request->quantity,
            'before_quantity' => $before,
            'after_quantity'  => $after,
            'reference'       => $request->reference,
            'notes'           => $request->notes,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', "Removed {$request->quantity} units from {$product->name}. New stock: {$after}");
    }

    /** Show Adjustment form */
    public function adjustmentForm()
    {
        return view('inventory.adjustment', $this->stockFormData());
    }

    /** Process stock adjustment (set exact count) */
    public function adjustment(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'showroom_id'  => 'required|exists:showrooms,id',
            'new_quantity' => 'required|integer|min:0',
            'notes'        => 'nullable|string',
        ]);

        $product    = Product::findOrFail($request->product_id);
        $showroomId = $request->showroom_id;
        $newQty     = $request->new_quantity;

        ['before' => $before, 'after' => $after] = $this->upsertShowroomStock(
            $showroomId, $product->id, $newQty
        );

        $diff = $after - $before;

        StockMovement::create([
            'product_id'      => $product->id,
            'user_id'         => auth()->id(),
            'showroom_id'     => $showroomId,
            'type'            => 'adjustment',
            'quantity'        => $diff,
            'before_quantity' => $before,
            'after_quantity'  => $after,
            'reference'       => 'Manual Adjustment',
            'notes'           => $request->notes,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', "Stock for {$product->name} adjusted from {$before} to {$after}.");
    }

    /** Full movement history with optional showroom filter for Admins */
    public function history(Request $request)
    {
        $user  = auth()->user();
        $query = StockMovement::with(['product', 'user', 'showroom'])->latest();

        if ($user->hasRole('Cashier') && $user->showroom_id) {
            // Cashiers only see their own showroom
            $query->where('showroom_id', $user->showroom_id);
        } elseif ($request->filled('showroom_id')) {
            // Fix #7 — Admins can filter by showroom
            $query->where('showroom_id', $request->showroom_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->get('export')) {
            $exportMovements = $query->get();
            if ($request->get('export') === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.inventory_history_pdf', ['movements' => $exportMovements]);
                return $pdf->download('inventory-history-' . date('Y-m-d') . '.pdf');
            }
            if ($request->get('export') === 'csv') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\InventoryHistoryExport($exportMovements), 
                    'inventory-history-' . date('Y-m-d') . '.csv'
                );
            }
        }

        $movements = $query->paginate(25)->withQueryString();
        $products  = Product::orderBy('name')->get(['id', 'name', 'barcode']);
        $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);

        return view('inventory.history', compact('movements', 'products', 'showrooms'));
    }

    /** Show Stock Import form */
    public function importForm()
    {
        $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);
        return view('inventory.import', compact('showrooms'));
    }

    /** Process Stock Import */
    public function import(Request $request)
    {
        $request->validate([
            'showroom_id' => 'required|exists:showrooms,id',
            'file'        => 'required|mimes:xlsx,csv|max:10240',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\StockImport($request->showroom_id),
                $request->file('file')
            );
            return redirect()->route('inventory.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing stock: ' . $e->getMessage());
        }
    }

    /** Download Stock Import template */
    public function importTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=stock_import_template.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['barcode', 'quantity'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['123456789', '50']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
