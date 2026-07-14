<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Products</h2>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('importModal').style.display = 'block'"
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                    📄 Import Excel
                </button>
                <a href="{{ route('products.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    + Add Product
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Barcode…"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="w-48">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Category</label>
                        <select name="category_id"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Filter</button>
                    <a href="{{ route('products.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Clear</a>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Barcode</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cost</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($product->image)
                                        <img src="{{ Storage::url($product->image) }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 text-lg">📦</div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $product->unit }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($product->barcode)<p class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $product->barcode }}</p>@else<p class="text-sm text-gray-400">—</p>@endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $product->category->name ?? '-' }}</p>
                                @if($product->subCategory)<p class="text-xs text-gray-400">{{ $product->subCategory->name }}</p>@endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($product->cost_price, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($product->selling_price, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-medium {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $product->total_stock ?? 0 }}
                                </span>
                                @if($product->isLowStock())<p class="text-xs text-red-400">Low Stock</p>@endif
                            </td>
                            <td class="px-4 py-3">
                                @if($product->is_active)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <a href="{{ route('products.show', $product) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded-lg transition">View</a>
                                <a href="{{ route('products.edit', $product) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-xs font-medium rounded-lg transition">Edit</a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-800 text-xs font-medium rounded-lg transition">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No products found. <a href="{{ route('products.create') }}" class="text-indigo-600 hover:underline">Add one now.</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $products->links() }}</div>
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity" 
                 onclick="document.getElementById('importModal').style.display = 'none'" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="text-green-600 dark:text-green-400">📄</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Import Products
                                </h3>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <p>Upload an Excel (.xlsx) or CSV file to bulk import products.</p>
                                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <p class="font-medium mb-1 text-gray-700 dark:text-gray-300">File format requirements:</p>
                                        <ul class="list-disc pl-5 space-y-1 text-xs">
                                            <li>First row must contain column headers.</li>
                                            <li>Required columns: <b>name</b>, <b>cost_price</b>, <b>selling_price</b>, <b>stock_quantity</b>, <b>unit</b>.</li>
                                            <li>Optional columns: <b>barcode</b>, <b>description</b>, <b>reorder_level</b>.</li>
                                        </ul>
                                        <div class="mt-3">
                                            <a href="{{ route('products.import.template') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium inline-flex items-center">
                                                ⬇️ Download Template
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select File</label>
                                        <input type="file" name="file" accept=".xlsx,.csv" required
                                               class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Import Data
                        </button>
                        <button type="button" onclick="document.getElementById('importModal').style.display = 'none'" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
