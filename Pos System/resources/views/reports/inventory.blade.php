<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">📦 Inventory Report</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.sales') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">📊 Sales Report</a>
                <a href="{{ route('reports.cashier') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">👤 Cashier Report</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Products</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $totalProducts }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 {{ $lowStockCount > 0 ? 'ring-2 ring-yellow-400' : '' }}">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Low Stock ⚠️</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $lowStockCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 {{ $outOfStockCount > 0 ? 'ring-2 ring-red-400' : '' }}">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Out of Stock 🚫</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $outOfStockCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Stock Value (Cost)</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($totalStockValue, 2) }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                <form method="GET" action="{{ route('reports.inventory') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Filter</label>
                        <select name="filter"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Products</option>
                            <option value="low_stock" {{ $filter === 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock Only</option>
                            <option value="out_of_stock" {{ $filter === 'out_of_stock' ? 'selected' : '' }}>🚫 Out of Stock</option>
                        </select>
                    </div>
                    @role('Admin')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Showroom</label>
                        <select name="showroom_id"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Showrooms</option>
                            @foreach($showrooms as $sr)
                                <option value="{{ $sr->id }}" {{ request('showroom_id') == $sr->id ? 'selected' : '' }}>{{ $sr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endrole
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Product name or Barcode..."
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                            Apply
                        </button>
                        <button type="submit" name="export" value="pdf"
                                class="px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition shadow-sm" title="Export as PDF">
                            📄 PDF
                        </button>
                        <button type="submit" name="export" value="excel"
                                class="px-4 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition shadow-sm" title="Export as Excel">
                            📊 Excel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Products Stock Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">📋 Stock Levels</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Stock Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Reorder Level</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Unit Cost</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Stock Value</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($products as $product)
                                @php
                                    $isLow = $product->total_stock <= $product->reorder_level && $product->total_stock > 0;
                                    $isOut = $product->total_stock <= 0;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition {{ $isOut ? 'bg-red-50/50 dark:bg-red-900/10' : ($isLow ? 'bg-yellow-50/50 dark:bg-yellow-900/10' : '') }}">
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $product->name }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $product->barcode ?: '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $product->category?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-bold {{ $isOut ? 'text-red-600 dark:text-red-400' : ($isLow ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-800 dark:text-gray-200') }}">
                                        {{ $product->total_stock }} {{ $product->unit }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">{{ $product->reorder_level }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($product->cost_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($product->total_stock * $product->cost_price, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($isOut)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400">Out of Stock</span>
                                        @elseif($isLow)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400">Low Stock</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400">In Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                        <p class="text-lg mb-1">📭</p>
                                        <p>No products found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($products->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

            {{-- Recent Stock Movements --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">🔄 Recent Stock Movements</h4>
                    <a href="{{ route('inventory.history') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View All →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty Change</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">By</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($recentMovements as $mv)
                                @php $badge = $mv->typeBadge(); @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $mv->created_at->format('M d, h:i A') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $mv->product?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ $mv->quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $mv->user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $mv->reference }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">No recent movements.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
