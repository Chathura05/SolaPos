<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">👤 Cashier Performance Report</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.sales') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">📊 Sales Report</a>
                <a href="{{ route('reports.inventory') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">📦 Inventory Report</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filter --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                <form method="GET" action="{{ route('reports.cashier') }}" class="flex flex-wrap items-end gap-4">
                    @role('Admin')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Showroom</label>
                        <select name="showroom_id"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Showrooms</option>
                            @foreach($showrooms as $sr)
                                <option value="{{ $sr->id }}" {{ $showroomId == $sr->id ? 'selected' : '' }}>{{ $sr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endrole
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Period</label>
                        <select name="period" id="periodSelect"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="today"      {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="this_week"   {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="this_month"  {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month"  {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="custom"      {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>
                    <div id="customDates" class="{{ $period === 'custom' ? '' : 'hidden' }} flex gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}"
                                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}"
                                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                            Apply Filter
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

            {{-- Overall Summary --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg p-5 text-white">
                    <p class="text-xs font-medium text-indigo-100 uppercase tracking-wide">Total Revenue</p>
                    <p class="text-2xl font-bold mt-1">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($overallTotal, 2) }}</p>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-lg p-5 text-white">
                    <p class="text-xs font-medium text-emerald-100 uppercase tracking-wide">Total Sales</p>
                    <p class="text-2xl font-bold mt-1">{{ $overallCount }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg p-5 text-white">
                    <p class="text-xs font-medium text-amber-100 uppercase tracking-wide">Active Cashiers</p>
                    <p class="text-2xl font-bold mt-1">{{ $cashierStats->count() }}</p>
                </div>
            </div>

            {{-- Cashier Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($cashierStats as $index => $stat)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border-l-4
                        {{ $index === 0 ? 'border-yellow-400' : ($index === 1 ? 'border-gray-400' : ($index === 2 ? 'border-amber-600' : 'border-indigo-300')) }}
                        hover:shadow-md transition">

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-sm">
                                    {{ strtoupper(substr($stat->cashier_name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $stat->cashier_name }}</p>
                                    @if($index === 0)
                                        <span class="inline-flex items-center text-xs text-yellow-600 dark:text-yellow-400 font-medium">🏆 Top Performer</span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">#{{ $index + 1 }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Sales</p>
                                <p class="font-bold text-gray-800 dark:text-gray-200">{{ $stat->total_sales }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Revenue</p>
                                <p class="font-bold text-green-600 dark:text-green-400">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($stat->total_revenue, 2) }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Avg Sale</p>
                                <p class="font-bold text-blue-600 dark:text-blue-400">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($stat->avg_sale, 2) }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2.5 text-center">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Best Sale</p>
                                <p class="font-bold text-purple-600 dark:text-purple-400">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($stat->max_sale, 2) }}</p>
                            </div>
                        </div>

                        @if($overallTotal > 0)
                            <div class="mt-3">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    <span>Contribution</span>
                                    <span>{{ round(($stat->total_revenue / $overallTotal) * 100, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full"
                                         style="width: {{ round(($stat->total_revenue / $overallTotal) * 100) }}%"></div>
                                </div>
                            </div>
                        @endif

                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">
                            Last sale: {{ $stat->last_sale ? \Carbon\Carbon::parse($stat->last_sale)->diffForHumans() : '—' }}
                        </p>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-gray-800 rounded-xl shadow-sm p-8 text-center">
                        <p class="text-lg mb-1">📭</p>
                        <p class="text-gray-400">No cashier activity found for the selected period.</p>
                    </div>
                @endforelse
            </div>

            {{-- Comparison Table --}}
            @if($cashierStats->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">📋 Detailed Comparison</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cashier</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Sales Count</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Revenue</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Avg Sale</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Best Sale</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Discounts</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Contribution</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($cashierStats as $index => $stat)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                    <td class="px-4 py-3 text-center">
                                        @if($index === 0) 🥇
                                        @elseif($index === 1) 🥈
                                        @elseif($index === 2) 🥉
                                        @else <span class="text-gray-400">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $stat->cashier_name }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ $stat->total_sales }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-600 dark:text-green-400">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($stat->total_revenue, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($stat->avg_sale, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($stat->max_sale, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-orange-600 dark:text-orange-400">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($stat->total_discount, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-600 dark:text-indigo-400">
                                        {{ $overallTotal > 0 ? round(($stat->total_revenue / $overallTotal) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700/50">
                            <tr class="font-semibold">
                                <td colspan="2" class="px-4 py-3 text-gray-700 dark:text-gray-300">Total</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ $overallCount }}</td>
                                <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($overallTotal, 2) }}</td>
                                <td colspan="4" class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('periodSelect').addEventListener('change', function () {
            document.getElementById('customDates').classList.toggle('hidden', this.value !== 'custom');
        });
    </script>
    @endpush
</x-app-layout>
