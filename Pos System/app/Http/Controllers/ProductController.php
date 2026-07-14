<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:10240',
        ]);

        try {
            // Ensure a default category exists for products lacking a category_id
            $defaultCat = Category::firstOrCreate(['name' => 'Uncategorized'], ['is_active' => true]);

            Excel::import(new ProductsImport, $request->file('file'));

            // Assign the default category to any product that still has none
            Product::whereNull('category_id')->update(['category_id' => $defaultCat->id]);

            return redirect()->back()->with('success', 'Products imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing products: ' . $e->getMessage());
        }
    }

    public function importTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=products_import_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['name', 'barcode', 'cost_price', 'selling_price', 'stock_quantity', 'reorder_level', 'unit', 'description'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Sample Product', '123456789', '10.50', '15.00', '100', '10', 'pcs', 'This is a sample']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'subCategory'])
            ->withSum('showrooms as total_stock', 'showroom_product.stock_quantity');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories    = Category::where('is_active', true)->orderBy('name')->get();
        $subCategories = collect();
        return view('products.create', compact('categories', 'subCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name'            => 'required|string|max:255',
            'barcode'         => 'nullable|string|max:100|unique:products',
            'description'     => 'nullable|string',
            'cost_price'      => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'reorder_level'   => 'required|integer|min:0',
            'unit'            => 'required|string|max:50',
            'image'           => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['image', 'stock_quantity']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        // Assign initial stock to the admin's showroom or the first available showroom.
        // Fix #4 — warn admin if no showroom is configured yet.
        $showroomId = auth()->user()->showroom_id ?? \App\Models\Showroom::first()?->id ?? null;
        if ($showroomId) {
            $product->showrooms()->attach($showroomId, ['stock_quantity' => $request->stock_quantity ?? 0]);
        } else {
            return redirect()->route('products.index')
                ->with('success', 'Product created successfully.')
                ->with('warning', 'No showroom is configured. Initial stock was not assigned — use Inventory → Stock In to add stock once a showroom is set up.');
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'subCategory');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories    = Category::where('is_active', true)->orderBy('name')->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)
            ->where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories', 'subCategories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id'     => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'name'            => 'required|string|max:255',
            'barcode'         => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'description'     => 'nullable|string',
            'cost_price'      => 'required|numeric|min:0',
            'selling_price'   => 'required|numeric|min:0',
            'stock_quantity'  => 'required|integer|min:0',
            'reorder_level'   => 'required|integer|min:0',
            'unit'            => 'required|string|max:50',
            'image'           => 'nullable|image|max:2048',
        ]);

        $data = $request->except(['image', 'stock_quantity']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // Update stock in showroom_product pivot
        $showroomId = auth()->user()->showroom_id ?? \App\Models\Showroom::first()?->id;
        if ($showroomId && $request->filled('stock_quantity')) {
            $pivot = \Illuminate\Support\Facades\DB::table('showroom_product')
                ->where('showroom_id', $showroomId)
                ->where('product_id', $product->id)
                ->first();

            if ($pivot) {
                \Illuminate\Support\Facades\DB::table('showroom_product')
                    ->where('showroom_id', $showroomId)
                    ->where('product_id', $product->id)
                    ->update(['stock_quantity' => $request->stock_quantity, 'updated_at' => now()]);
            } else {
                $product->showrooms()->attach($showroomId, ['stock_quantity' => $request->stock_quantity]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete product image from storage if it exists
        if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /** AJAX: get subcategories for a given category */
    public function getSubCategories(Request $request)
    {
        $subCategories = SubCategory::where('category_id', $request->category_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($subCategories);
    }
}
