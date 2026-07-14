<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">📋 Stock Movement History</h2>
            <a href="{{ route('inventory.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">← Back to Inventory</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                <form method="GET" class="flex gap-3 flex-wrap items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Product</label>
                        <select name="product_id"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Products</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                        <select name="type"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Types</option>
                            <option value="stock_in"     {{ request('type') === 'stock_in'     ? 'selected' : '' }}>Stock In</option>
                            <option value="stock_out"    {{ request('type') === 'stock_out'    ? 'selected' : '' }}>Stock Out</option>
                            <option value="adjustment"   {{ request('type') === 'adjustment'   ? 'selected' : '' }}>Adjustment</option>
                            <option value="sale"         {{ request('type') === 'sale'         ? 'selected' : '' }}>Sale</option>
                            <option value="return"       {{ request('type') === 'return'       ? 'selected' : '' }}>Return</option>
                            <option value="transfer_in"  {{ request('type') === 'transfer_in'  ? 'selected' : '' }}>Transfer In</option>
                            <option value="transfer_out" {{ request('type') === 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                        </select>
                    </div>
                    @role('Admin')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Showroom</label>
                        <select name="showroom_id"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Showrooms</option>
                            @foreach($showrooms as $sr)
                                <option value="{{ $sr->id }}" {{ request('showroom_id') == $sr->id ? 'selected' : '' }}>
                                    {{ $sr->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endrole
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Filter</button>
                        <a href="{{ route('inventory.history') }}" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Clear</a>
                    </div>

                    <div class="flex gap-2 ml-auto">
                        <button type="submit" name="export" value="pdf"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition shadow-sm" title="Export as PDF">
                            PDF
                        </button>
                        <button type="submit" name="export" value="csv"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-sm" title="Export as CSV">
                            CSV
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                            @role('Admin')
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Showroom</th>
                            @endrole
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qty Change</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Before</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">After</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">By</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($movements as $move)
                        @php $badge = $move->typeBadge(); @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $move->product->name ?? '—' }}</p>
                                @if($move->product->barcode)<p class="text-xs font-mono text-gray-400">{{ $move->product->barcode }}</p>@endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </td>
                            @role('Admin')
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $move->showroom->name ?? 'Global/All' }}</td>
                            @endrole
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-bold {{ $move->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $move->quantity > 0 ? '+' : '' }}{{ $move->quantity }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-sm text-gray-500 dark:text-gray-400">{{ $move->before_quantity }}</td>
                            <td class="px-5 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $move->after_quantity }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $move->reference ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $move->user->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $move->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">No stock movements found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $movements->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
