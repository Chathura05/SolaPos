<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">📊 Sales Report</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.cashier') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">👤 Cashier Report</a>
                <a href="{{ route('reports.inventory') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">📦 Inventory Report</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Period Filter --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-end gap-4">
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
                            <option value="yesterday"  {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
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

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">Rs. {{ number_format($totalRevenue, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Transactions</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $totalTransactions }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Avg Order</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">Rs. {{ number_format($avgOrderValue, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Discounts Given</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">Rs. {{ number_format($totalDiscount, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tax Collected</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">Rs. {{ number_format($totalTax, 2) }}</p>
                </div>
            </div>

            {{-- Payment Method Breakdown + Daily Trend --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Payment Breakdown --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">💳 Payment Method Breakdown</h4>
                    @forelse($paymentBreakdown as $pb)
                        <div class="flex items-center justify-between py-2.5 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-sm
                                    {{ $pb->payment_method === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                    {{ $pb->payment_method === 'card' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                    {{ $pb->payment_method === 'other' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                ">
                                    {{ $pb->payment_method === 'cash' ? '💵' : ($pb->payment_method === 'card' ? '💳' : '🔄') }}
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ ucfirst($pb->payment_method) }}</p>
                                    <p class="text-xs text-gray-400">{{ $pb->count }} transactions</p>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-gray-800 dark:text-gray-200">Rs. {{ number_format($pb->total, 2) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">No sales data available.</p>
                    @endforelse
                </div>

                {{-- Daily Trend (Last 7 Days) --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">📈 Daily Trend (Last 7 Days)</h4>
                    @if($dailyTrend->isNotEmpty())
                        @php $maxTotal = $dailyTrend->max('total') ?: 1; @endphp
                        <div class="space-y-3">
                            @foreach($dailyTrend as $day)
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($day->date)->format('D, M d') }}</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200">Rs. {{ number_format($day->total, 2) }} ({{ $day->count }})</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2.5 rounded-full transition-all duration-500"
                                             style="width: {{ round(($day->total / $maxTotal) * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-400 text-center py-4">No daily data available.</p>
                    @endif
                </div>
            </div>

            {{-- Sales Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">🧾 Transaction List</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date & Time</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Cashier</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Discount</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($sales as $sale)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $sale->invoice_number }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $sale->cashier?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $sale->customer_name ?: 'Walk-in' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                            {{ $sale->payment_method === 'card' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                            {{ $sale->payment_method === 'other' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                        ">{{ ucfirst($sale->payment_method) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">Rs. {{ number_format($sale->subtotal, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-orange-600 dark:text-orange-400">
                                        @if($sale->discount_amount > 0)
                                            -Rs. {{ number_format($sale->discount_amount, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-200">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('pos.receipt', $sale->id) }}"
                                           class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs" target="_blank">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                        <p class="text-lg mb-1">📭</p>
                                        <p>No sales found for the selected period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($sales->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $sales->links() }}
                    </div>
                @endif
            </div>

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
