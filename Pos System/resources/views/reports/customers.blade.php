<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">👥 Customer Report</h2>
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
                <form method="GET" action="{{ route('reports.customers') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Period</label>
                        <select name="period"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all_time"   {{ $period === 'all_time' ? 'selected' : '' }}>All Time</option>
                            <option value="this_month"  {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month"  {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="this_year"   {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        Apply
                    </button>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Registered Customers</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $totalCustomersInDB }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Active</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $activeCustomers }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Customers with Sales</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $customersWithSales }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Walk-in Sales</p>
                    <p class="text-2xl font-bold text-gray-600 dark:text-gray-400 mt-1">{{ $walkInSales }}</p>
                </div>
            </div>

            {{-- Top Customers --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">🏆 Top Customers by Spending</h4>
                </div>

                @if($topCustomers->isNotEmpty())
                    {{-- Top 3 Cards --}}
                    <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($topCustomers->take(3) as $index => $cust)
                            <div class="rounded-xl p-5 text-center
                                {{ $index === 0 ? 'bg-gradient-to-br from-yellow-400 to-amber-500 text-white shadow-lg' : '' }}
                                {{ $index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-400 text-gray-900 shadow-md' : '' }}
                                {{ $index === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-700 text-white shadow-md' : '' }}
                            ">
                                <div class="text-3xl mb-2">
                                    @if($index === 0) 🥇 @elseif($index === 1) 🥈 @else 🥉 @endif
                                </div>
                                <p class="font-bold text-lg">{{ $cust->customer_name ?: 'Unknown' }}</p>
                                <p class="text-sm opacity-80">{{ $cust->customer_phone }}</p>
                                <div class="mt-3 text-2xl font-bold">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($cust->total_spent, 2) }}</div>
                                <p class="text-sm opacity-75 mt-1">{{ $cust->total_orders }} orders · Avg {{ setting('currency_symbol', 'Rs.') }} {{ number_format($cust->avg_order, 2) }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Full Ranking Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Phone</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Orders</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Spent</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Avg Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Last Purchase</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($topCustomers as $index => $cust)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition {{ $index < 3 ? 'bg-amber-50/30 dark:bg-amber-900/5' : '' }}">
                                        <td class="px-4 py-3 text-center">
                                            @if($index === 0) 🥇
                                            @elseif($index === 1) 🥈
                                            @elseif($index === 2) 🥉
                                            @else <span class="text-gray-400">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $cust->customer_name ?: 'Unknown' }}</td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $cust->customer_phone }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ $cust->total_orders }}</td>
                                        <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($cust->total_spent, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($cust->avg_order, 2) }}</td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">{{ \Carbon\Carbon::parse($cust->last_purchase)->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <p class="text-lg mb-1">📭</p>
                        <p class="text-gray-400">No customer purchase data found for the selected period.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
