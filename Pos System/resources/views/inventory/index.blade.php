<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Inventory Overview</h2>
            <div class="flex gap-2">
                @role('Admin')
                <a href="{{ route('inventory.stock-in') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">+ Stock In</a>
                <a href="{{ route('inventory.import') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">📥 Import Stock</a>
                <a href="{{ route('inventory.stock-out') }}" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition">− Stock Out</a>
                <a href="{{ route('inventory.adjustment') }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-lg transition">⚖ Adjust</a>
                @endrole
                <a href="{{ route('inventory.history') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition">📋 History</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="px-4 py-3 bg-red-100 border border-red-400 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $totalProducts }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Low Stock</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $lowStockCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Out of Stock</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $outOfStockCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Stock Value (Cost)</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($totalStockValue, 2) }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                <form method="GET" class="flex gap-3 flex-wrap items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or Barcode..."
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Stock Status</label>
                        <select name="stock_status"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    @role('Admin')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Showroom</label>
                        <select name="showroom_id"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Showrooms</option>
                            @foreach($showrooms as $sr)
                                <option value="{{ $sr->id }}" {{ request('showroom_id') == $sr->id ? 'selected' : '' }}>{{ $sr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endrole
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Filter</button>
                    <a href="{{ route('inventory.index') }}" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Clear</a>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Category</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Current Stock</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reorder Level</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stock Value</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($products as $product)
                        @php
                            $stockStatus = $product->stock_quantity <= 0 ? 'out' : ($product->stock_quantity <= $product->reorder_level ? 'low' : 'ok');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</p>
                                @if($product->barcode)<p class="text-xs font-mono text-gray-400">{{ $product->barcode }}</p>@endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $product->category->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-bold {{ $stockStatus === 'out' ? 'text-red-600' : ($stockStatus === 'low' ? 'text-yellow-600' : 'text-gray-900 dark:text-gray-100') }}">
                                    {{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ $product->reorder_level }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">
                                {{ number_format($product->stock_quantity * $product->cost_price, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                @if($stockStatus === 'out')
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Out of Stock</span>
                                @elseif($stockStatus === 'low')
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">⚠ Low Stock</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">In Stock</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right space-x-1">
                                @role('Admin')
                                <a href="{{ route('inventory.stock-in') }}?product_id={{ $product->id }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-green-100 hover:bg-green-200 text-green-800 text-xs font-medium rounded-lg transition">+In</a>
                                <a href="{{ route('inventory.adjustment') }}?product_id={{ $product->id }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-xs font-medium rounded-lg transition">Adjust</a>
                                @endrole
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $products->links() }}</div>
            </div>

        </div>
    </div>
</x-app-layout>
