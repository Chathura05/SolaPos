<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\StockTrait;

class ReportController extends Controller
{
    use StockTrait;

    /**
     * Apply a period filter to a query builder.
     * Extracts the duplicated switch logic into a single reusable method.
     */
    private function applyPeriodFilter($query, string $period, ?string $dateFrom = null, ?string $dateTo = null)
    {
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', today()->subDay());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'last_month':
                $lm = now()->subMonth();
                $query->whereMonth('created_at', $lm->month)
                      ->whereYear('created_at', $lm->year);
                break;
            case 'this_year':
                $query->whereYear('created_at', now()->year);
                break;
            case 'custom':
                if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
                break;
            case 'all_time':
            default:
                // No date filter — return all records
                break;
        }

        return $query;
    }



    /**
     * Sales Report — daily / monthly / custom range
     */
    public function sales(Request $request)
    {
        $period   = $request->get('period', 'today');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $showroomId = $request->get('showroom_id');

        $query = Sale::with('cashier')->where('status', 'completed');
        
        if ($showroomId) {
            $query->where('showroom_id', $showroomId);
        }

        $this->applyPeriodFilter($query, $period, $dateFrom, $dateTo);

        $sales = $query->latest()->paginate(25)->withQueryString();

        // Summary stats for filtered period (reuse the same helper)
        $summaryQuery = Sale::where('status', 'completed');
        if ($showroomId) {
            $summaryQuery->where('showroom_id', $showroomId);
        }
        $this->applyPeriodFilter($summaryQuery, $period, $dateFrom, $dateTo);

        $totalRevenue     = (clone $summaryQuery)->sum('total_amount');
        $totalTransactions = (clone $summaryQuery)->count();
        $totalDiscount    = (clone $summaryQuery)->sum('discount_amount');
        $totalTax         = (clone $summaryQuery)->sum('tax_amount');
        $avgOrderValue    = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0;

        // Payment method breakdown
        $paymentBreakdown = (clone $summaryQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Daily trend (last 7 days)
        $dailyTrendQuery = Sale::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay());
            
        if ($showroomId) {
            $dailyTrendQuery->where('showroom_id', $showroomId);
        }

        $dailyTrend = $dailyTrendQuery->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("COUNT(*) as count"),
                DB::raw("SUM(total_amount) as total")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);
        $showroomName = $showroomId ? \App\Models\Showroom::find($showroomId)?->name ?? 'Unknown Showroom' : 'All Showrooms';

        if ($request->get('export')) {
            $exportSales = $query->latest()->get();
            $exportData = [
                'sales' => $exportSales,
                'period' => $period,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'showroomName' => $showroomName,
                'totalRevenue' => $totalRevenue,
                'totalTransactions' => $totalTransactions,
                'totalDiscount' => $totalDiscount,
                'totalTax' => $totalTax,
                'avgOrderValue' => $avgOrderValue
            ];

            if ($request->get('export') === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.sales', $exportData);
                return $pdf->download('sales-report-' . date('Y-m-d') . '.pdf');
            }

            if ($request->get('export') === 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ReportExport('reports.exports.sales', $exportData),
                    'sales-report-' . date('Y-m-d') . '.xlsx'
                );
            }
        }

        return view('reports.sales', compact(
            'sales', 'period', 'dateFrom', 'dateTo', 'showroomId', 'showrooms',
            'totalRevenue', 'totalTransactions', 'totalDiscount', 'totalTax', 'avgOrderValue',
            'paymentBreakdown', 'dailyTrend'
        ));
    }

    /**
     * Cashier Performance Report
     */
    public function cashier(Request $request)
    {
        $period   = $request->get('period', 'this_month');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $showroomId = $request->get('showroom_id');

        $query = Sale::where('status', 'completed');
        
        if ($showroomId) {
            $query->where('showroom_id', $showroomId);
        }

        $this->applyPeriodFilter($query, $period, $dateFrom, $dateTo);

        $cashierStats = (clone $query)
            ->select(
                'user_id',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('AVG(total_amount) as avg_sale'),
                DB::raw('MAX(total_amount) as max_sale'),
                DB::raw('MIN(created_at) as first_sale'),
                DB::raw('MAX(created_at) as last_sale')
            )
            ->groupBy('user_id')
            ->get();

        // Eager-load all relevant users in a single query (fixes N+1)
        $userIds = $cashierStats->pluck('user_id')->unique()->filter();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $cashierStats = $cashierStats->map(function ($stat) use ($users) {
                $user = $users->get($stat->user_id);
                $stat->cashier = $user;
                $stat->cashier_name = $user ? $user->name : 'Unknown';
                return $stat;
            })
            ->sortByDesc('total_revenue');

        $overallTotal = $cashierStats->sum('total_revenue');
        $overallCount = $cashierStats->sum('total_sales');

        $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);
        $showroomName = $showroomId ? \App\Models\Showroom::find($showroomId)?->name ?? 'Unknown Showroom' : 'All Showrooms';

        if ($request->get('export')) {
            $exportData = [
                'cashierStats' => $cashierStats,
                'period' => $period,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'showroomName' => $showroomName,
                'overallTotal' => $overallTotal,
                'overallCount' => $overallCount
            ];

            if ($request->get('export') === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.cashier', $exportData);
                return $pdf->download('cashier-report-' . date('Y-m-d') . '.pdf');
            }

            if ($request->get('export') === 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ReportExport('reports.exports.cashier', $exportData),
                    'cashier-report-' . date('Y-m-d') . '.xlsx'
                );
            }
        }

        return view('reports.cashier', compact(
            'cashierStats', 'period', 'dateFrom', 'dateTo', 'showroomId', 'showrooms',
            'overallTotal', 'overallCount'
        ));
    }

    /**
     * Inventory Report — stock levels, low-stock alerts, movement summary
     */
    public function inventory(Request $request)
    {
        $user = auth()->user();
        $selectedShowroom = $request->get('showroom_id');
        
        if ($user->hasRole('Cashier') && $user->showroom_id) {
            $showroomId = $user->showroom_id;
        } else {
            $showroomId = $selectedShowroom ?: null;
        }
        
        // Fix #18 — null-safe lookup prevents fatal error if showroom was deleted
        $showroomName = $showroomId ? \App\Models\Showroom::find($showroomId)?->name ?? 'Unknown Showroom' : 'All Showrooms';
        
        $filter = $request->get('filter', 'all'); // all, low_stock, out_of_stock

        $query = Product::with('category', 'subCategory')->where('is_active', true);
        
        // Build showroom-aware stock subquery with parameterized bindings
        [$stockSql, $stockBindings] = $this->stockSubquery($showroomId);

        $query->select('products.*')
              ->selectRaw("{$stockSql} as total_stock", $stockBindings);

        switch ($filter) {
            case 'low_stock':
                $query->whereRaw("{$stockSql} <= reorder_level", $stockBindings)
                      ->whereRaw("{$stockSql} > 0", $stockBindings);
                break;
            case 'out_of_stock':
                $query->whereRaw("{$stockSql} <= 0", $stockBindings);
                break;
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->get('export')) {
            $exportProducts = $query->orderBy('total_stock', 'asc')->get();
            $exportData = [
                'products' => $exportProducts,
                'filter' => $filter,
                'showroomName' => $showroomName
            ];

            if ($request->get('export') === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.inventory', $exportData);
                return $pdf->download('inventory-report-' . date('Y-m-d') . '.pdf');
            }

            if ($request->get('export') === 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ReportExport('reports.exports.inventory', $exportData),
                    'inventory-report-' . date('Y-m-d') . '.xlsx'
                );
            }
        }

        $products = $query->orderBy('total_stock', 'asc')->paginate(25)->withQueryString();

        // Summary — scoped to showroom for cashiers
        $totalProducts    = Product::where('is_active', true)->count();
        $lowStockCount    = Product::where('is_active', true)
            ->whereRaw("{$stockSql} <= reorder_level", $stockBindings)
            ->whereRaw("{$stockSql} > 0", $stockBindings)
            ->count();
        $outOfStockCount  = Product::where('is_active', true)
            ->whereRaw("{$stockSql} <= 0", $stockBindings)
            ->count();
        
        $stockValueQuery = DB::table('showroom_product')
            ->join('products', 'products.id', '=', 'showroom_product.product_id');
        if ($showroomId) {
            $stockValueQuery->where('showroom_product.showroom_id', $showroomId);
        }
        $totalStockValue = $stockValueQuery->sum(DB::raw('showroom_product.stock_quantity * products.cost_price'));

        // Recent movements — scoped to showroom for cashiers
        $movementsQuery = StockMovement::with('product', 'user')->latest();
        if ($showroomId) {
            $movementsQuery->where('showroom_id', $showroomId);
        }
        $recentMovements = $movementsQuery->limit(10)->get();

        $showrooms = \App\Models\Showroom::orderBy('name')->get(['id', 'name']);

        return view('reports.inventory', compact(
            'products', 'filter',
            'totalProducts', 'lowStockCount', 'outOfStockCount', 'totalStockValue',
            'recentMovements', 'showrooms', 'showroomId'
        ));
    }

    /**
     * Customer Report — top customers, spending analytics
     */
    public function customers(Request $request)
    {
        $period = $request->get('period', 'all_time');

        $salesQuery = Sale::where('status', 'completed');

        $this->applyPeriodFilter($salesQuery, $period);

        // Top customers by spending (from sales records)
        $topCustomers = (clone $salesQuery)
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->select(
                'customer_name',
                'customer_phone',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('AVG(total_amount) as avg_order'),
                DB::raw('MAX(created_at) as last_purchase')
            )
            ->groupBy('customer_name', 'customer_phone')
            ->orderByDesc('total_spent')
            ->limit(20)
            ->get();

        // Summary
        $totalCustomersInDB  = Customer::count();
        $activeCustomers     = Customer::where('is_active', true)->count();
        $customersWithSales  = (clone $salesQuery)
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->distinct('customer_phone')->count('customer_phone');
        $walkInSales = (clone $salesQuery)
            ->where(function ($q) {
                $q->whereNull('customer_phone')->orWhere('customer_phone', '');
            })->count();

        return view('reports.customers', compact(
            'topCustomers', 'period',
            'totalCustomersInDB', 'activeCustomers', 'customersWithSales', 'walkInSales'
        ));
    }
}
