<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Welcome Banner --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-bold">Welcome back, {{ auth()->user()->name }}! 👋</h3>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/20 border border-white/30">
                        {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                    </span>
                </div>
                <p class="text-indigo-200 text-sm mt-1">{{ now()->format('l, d F Y') }}</p>
                <div class="mt-4 flex gap-3 flex-wrap">
                    <a href="{{ route('pos.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-700 font-bold text-sm rounded-xl hover:bg-indigo-50 transition shadow">
                        ⚡ Open POS Terminal
                    </a>
                    @role('Admin')
                    <a href="{{ route('reports.sales') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-indigo-500 bg-opacity-40 text-white font-medium text-sm rounded-xl hover:bg-opacity-60 transition border border-white/20">
                        📊 View Reports
                    </a>
                    @endrole
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- CASHIER DASHBOARD (simplified) --}}
            {{-- ═══════════════════════════════════════════════ --}}
            @if($isCashier)
                {{-- Cashier KPI Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">My Sales Today</p>
                            <span class="text-lg">💰</span>
                        </div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($todaySales, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $todayCount }} transactions</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">My Sales This Month</p>
                            <span class="text-lg">📅</span>
                        </div>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($monthSales, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $monthCount }} transactions</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 hover:shadow-md transition col-span-2">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Quick Actions</p>
                            <span class="text-lg">⚡</span>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
                            <a href="{{ route('pos.index') }}" class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-3 text-center hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition">
                                <div class="text-2xl mb-1">🛒</div>
                                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">New Sale</p>
                            </a>
                            <a href="{{ route('pos.history') }}" class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 text-center hover:bg-purple-100 dark:hover:bg-purple-900/40 transition">
                                <div class="text-2xl mb-1">🧾</div>
                                <p class="text-xs font-semibold text-purple-700 dark:text-purple-300">My History</p>
                            </a>
                            <a href="{{ route('inventory.index') }}" class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center hover:bg-green-100 dark:hover:bg-green-900/40 transition">
                                <div class="text-2xl mb-1">🏭</div>
                                <p class="text-xs font-semibold text-green-700 dark:text-green-300">Inventory</p>
                            </a>
                            <a href="{{ route('customers.index') }}" class="bg-teal-50 dark:bg-teal-900/20 rounded-lg p-3 text-center hover:bg-teal-100 dark:hover:bg-teal-900/40 transition">
                                <div class="text-2xl mb-1">👥</div>
                                <p class="text-xs font-semibold text-teal-700 dark:text-teal-300">Customers</p>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Cashier Lower Section (Chart + Recent Sales) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    
                    {{-- Weekly Sales Trend --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">📈 My Sales Trend (Last 7 Days)</h4>
                        </div>
                        <div class="w-full" style="height: 250px;">
                            <canvas id="salesTrendChart"></canvas>
                        </div>
                    </div>

                    {{-- Cashier Recent Sales --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">🧾 My Recent Sales</h4>
                            <a href="{{ route('pos.history') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View All →</a>
                        </div>
                        @forelse($recentSales as $sale)
                            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                <div>
                                    <p class="text-sm font-mono font-semibold text-indigo-600 dark:text-indigo-400">{{ $sale->invoice_number }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $sale->created_at->format('M d, h:i A') }} · {{ $sale->customer_name ?: 'Walk-in' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->total_amount, 2) }}</p>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                        {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                        {{ ucfirst($sale->payment_method) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-6">No sales yet. Start your first sale!</p>
                        @endforelse
                    </div>
                </div>

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- ADMIN DASHBOARD (full) --}}
            {{-- ═══════════════════════════════════════════════ --}}
            @else
                {{-- KPI Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Today's Sales</p>
                            <span class="text-lg">💰</span>
                        </div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-2">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($todaySales, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $todayCount }} transactions</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">This Month</p>
                            <span class="text-lg">📅</span>
                        </div>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-2">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($monthSales, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $monthCount }} transactions</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Products</p>
                            <span class="text-lg">📦</span>
                        </div>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $totalProducts }}</p>
                        @if($outOfStockCount > 0)
                            <p class="text-xs text-red-500 mt-1">⚠️ {{ $outOfStockCount }} out of stock</p>
                        @else
                            <p class="text-xs text-green-500 mt-1">All items in stock</p>
                        @endif
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Customers</p>
                            <span class="text-lg">👥</span>
                        </div>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2">{{ $totalCustomers }}</p>
                        <p class="text-xs text-gray-400 mt-1">Registered customers</p>
                    </div>
                </div>

                {{-- Main Content Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Left: Weekly Trend + Top Products --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Weekly Sales Trend --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">📈 Sales Trend (Last 7 Days)</h4>
                                <a href="{{ route('reports.sales') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View Full Report →</a>
                            </div>
                            <div class="w-full" style="height: 250px;">
                                <canvas id="salesTrendChart"></canvas>
                            </div>
                        </div>

                        {{-- Top Selling Products --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">🏆 Top Selling Products (This Month)</h4>
                                <a href="{{ route('products.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View All →</a>
                            </div>
                            <div class="w-full flex justify-center" style="height: 250px;">
                                <canvas id="topProductsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Right Sidebar --}}
                    <div class="space-y-6">

                        {{-- Quick Access --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Quick Access</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('pos.index') }}" class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-3 text-center hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition group">
                                    <div class="text-2xl mb-1">⚡</div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">POS</p>
                                </a>
                                <a href="{{ route('products.index') }}" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center hover:bg-blue-100 dark:hover:bg-blue-900/40 transition group">
                                    <div class="text-2xl mb-1">📦</div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-blue-600">Products</p>
                                </a>
                                <a href="{{ route('inventory.index') }}" class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center hover:bg-green-100 dark:hover:bg-green-900/40 transition group">
                                    <div class="text-2xl mb-1">🏭</div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-green-600">Inventory</p>
                                </a>
                                <a href="{{ route('categories.index') }}" class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 text-center hover:bg-purple-100 dark:hover:bg-purple-900/40 transition group">
                                    <div class="text-2xl mb-1">🗂</div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-purple-600">Categories</p>
                                </a>
                                <a href="{{ route('customers.index') }}" class="bg-teal-50 dark:bg-teal-900/20 rounded-lg p-3 text-center hover:bg-teal-100 dark:hover:bg-teal-900/40 transition group">
                                    <div class="text-2xl mb-1">👥</div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-teal-600">Customers</p>
                                </a>
                                <a href="{{ route('suppliers.index') }}" class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 text-center hover:bg-amber-100 dark:hover:bg-amber-900/40 transition group">
                                    <div class="text-2xl mb-1">🚚</div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-amber-600">Suppliers</p>
                                </a>
                            </div>
                        </div>

                        {{-- Low Stock Alerts --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">⚠️ Low Stock Alerts</h4>
                                <a href="{{ route('reports.inventory', ['filter' => 'low_stock']) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View All →</a>
                            </div>
                            @forelse($lowStockProducts as $product)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-400">Reorder at: {{ $product->reorder_level }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $product->total_stock <= 0 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                        {{ $product->total_stock }} left
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-4">✅ All stock levels are healthy!</p>
                            @endforelse
                        </div>

                        {{-- Recent Sales --}}
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">🧾 Recent Sales</h4>
                                <a href="{{ route('pos.history') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View All →</a>
                            </div>
                            @forelse($recentSales as $sale)
                                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <div>
                                        <p class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400">{{ $sale->invoice_number }}</p>
                                        <p class="text-xs text-gray-400">{{ $sale->created_at->diffForHumans() }} · {{ $sale->cashier?->name }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->total_amount, 2) }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-4">No recent sales.</p>
                            @endforelse
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare Data for Weekly Trend
        const weeklyData = @json($weeklyTrend);
        const trendLabels = weeklyData.map(item => new Date(item.date).toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' }));
        const trendValues = weeklyData.map(item => item.total);

        // Prepare Data for Top Products (Admin only)
        @if(isset($topProducts))
        const topProducts = @json($topProducts);
        const tpLabels = topProducts.map(item => item.product_name);
        const tpValues = topProducts.map(item => item.total_qty);
        @endif

        // Render Sales Trend Chart (Line)
        if(document.getElementById('salesTrendChart')) {
            new Chart(document.getElementById('salesTrendChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: trendValues,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(156, 163, 175, 0.1)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Render Top Products Chart (Doughnut)
        if(document.getElementById('topProductsChart')) {
            new Chart(document.getElementById('topProductsChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: tpLabels,
                    datasets: [{
                        data: tpValues,
                        backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } } 
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
