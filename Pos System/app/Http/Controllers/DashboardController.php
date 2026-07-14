<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isCashier = $user->hasRole('Cashier');

        // ─── Cashier: simplified data (own showroom sales only) ───
        if ($isCashier && $user->showroom_id) {
            // Consolidated: fetch today + month aggregates in two queries
            $todayStats = Sale::whereDate('created_at', today())
                ->where('status', 'completed')
                ->where('showroom_id', $user->showroom_id)
                ->selectRaw('SUM(total_amount) as total, COUNT(*) as count')
                ->first();

            $monthStats = Sale::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'completed')
                ->where('showroom_id', $user->showroom_id)
                ->selectRaw('SUM(total_amount) as total, COUNT(*) as count')
                ->first();

            $todaySales  = $todayStats->total ?? 0;
            $todayCount  = $todayStats->count ?? 0;
            $monthSales  = $monthStats->total ?? 0;
            $monthCount  = $monthStats->count ?? 0;

            $recentSales = Sale::where('showroom_id', $user->showroom_id)
                ->where('status', 'completed')
                ->latest()
                ->limit(10)
                ->get();

            $weeklyTrend = Sale::where('showroom_id', $user->showroom_id)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->select(
                    DB::raw("DATE(created_at) as date"),
                    DB::raw("SUM(total_amount) as total"),
                    DB::raw("COUNT(*) as count")
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return view('dashboard', compact(
                'isCashier', 'todaySales', 'todayCount',
                'monthSales', 'monthCount', 'recentSales', 'weeklyTrend'
            ));
        }

        // ─── Admin: full dashboard data ───
        $todaySales     = Sale::whereDate('created_at', today())->where('status', 'completed')->sum('total_amount');
        $todayCount     = Sale::whereDate('created_at', today())->where('status', 'completed')->count();

        $monthSales     = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');
        $monthCount     = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->count();

        $totalProducts    = Product::where('is_active', true)->count();
        // Fix #5 — low-stock and out-of-stock are mutually exclusive:
        // low_stock = stock > 0 AND stock <= reorder_level
        // out_of_stock = stock <= 0
        $lowStockProducts = Product::where('is_active', true)
            ->select('products.*')
            ->selectRaw('COALESCE((SELECT SUM(stock_quantity) FROM showroom_product WHERE product_id = products.id), 0) as total_stock')
            ->whereRaw('COALESCE((SELECT SUM(stock_quantity) FROM showroom_product WHERE product_id = products.id), 0) <= reorder_level')
            ->whereRaw('COALESCE((SELECT SUM(stock_quantity) FROM showroom_product WHERE product_id = products.id), 0) > 0')
            ->orderBy('total_stock')
            ->limit(5)
            ->get();
        $outOfStockCount  = Product::where('is_active', true)
            ->whereRaw('COALESCE((SELECT SUM(stock_quantity) FROM showroom_product WHERE product_id = products.id), 0) <= 0')
            ->count();

        $totalCustomers = Customer::count();

        $recentSales = Sale::with('cashier')
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', now()->month)
            ->whereYear('sales.created_at', now()->year)
            ->select(
                'sale_items.product_name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('sale_items.product_name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        $weeklyTrend = Sale::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("SUM(total_amount) as total"),
                DB::raw("COUNT(*) as count")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard', compact(
            'isCashier',
            'todaySales', 'todayCount',
            'monthSales', 'monthCount',
            'totalProducts', 'lowStockProducts', 'outOfStockCount',
            'totalCustomers', 'recentSales', 'topProducts', 'weeklyTrend'
        ));
    }
}
