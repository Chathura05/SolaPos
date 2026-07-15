<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PromoCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile — all authenticated users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // POS — all authenticated users (Admin + Cashier)
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::post('/pos/send-receipt/{sale}', [PosController::class, 'sendReceipt'])->name('pos.send-receipt');
    Route::get('/pos/history', [PosController::class, 'history'])->name('pos.history');
    Route::get('/ajax/products/search', [PosController::class, 'searchProduct'])->name('ajax.products.search');
    
    // Parked Sales
    Route::post('/pos/hold', [PosController::class, 'holdSale']);
    Route::get('/pos/held-sales', [PosController::class, 'getHeldSales']);
    Route::delete('/pos/held-sales/{id}', [PosController::class, 'deleteHeldSale']);
    
    // Promos
    Route::post('/ajax/promos/validate', [PosController::class, 'validatePromo']);

    // Quotes
    Route::get('/quotes/{quote}/json', [\App\Http\Controllers\QuoteController::class, 'getJson'])->name('quotes.json');
    Route::post('/quotes/{quote}/convert', [\App\Http\Controllers\QuoteController::class, 'convert'])->name('quotes.convert');
    Route::resource('quotes', \App\Http\Controllers\QuoteController::class)->only(['index', 'store', 'show', 'destroy']);

    // Customers — Cashier can view, search, and create
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/ajax/customers/search', [CustomerController::class, 'search'])->name('ajax.customers.search');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::post('/customers/{customer}/payments', [CustomerController::class, 'addPayment'])->name('customers.payments.store');

    // Inventory Management (Available to Cashier & Admin)
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/history', [InventoryController::class, 'history'])->name('inventory.history');

    // Reports available to Cashier
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');

    // Pending Dispatches (Available to Cashier & Admin)
    Route::get('/dispatches', [\App\Http\Controllers\DispatchController::class, 'index'])->name('dispatches.index');
    Route::get('/dispatches/{dispatch}', [\App\Http\Controllers\DispatchController::class, 'show'])->name('dispatches.show');
    Route::post('/dispatches/{dispatch}/accept', [\App\Http\Controllers\DispatchController::class, 'accept'])->name('dispatches.accept');

    // Returns (Available to Cashier & Admin)
    Route::get('/returns', [\App\Http\Controllers\ShowroomReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/create', [\App\Http\Controllers\ShowroomReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns', [\App\Http\Controllers\ShowroomReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/{return}', [\App\Http\Controllers\ShowroomReturnController::class, 'show'])->name('returns.show');

    // ═══════════════════════════════════════════════════
    // ADMIN ONLY — Product, Category, Supplier, Other Reports, Customer CUD
    // ═══════════════════════════════════════════════════
    Route::middleware('role:Admin')->group(function () {
        // Settings, Showrooms & Users
        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'store'])->name('settings.store');
        Route::resource('showrooms', \App\Http\Controllers\ShowroomController::class)->except(['show']);
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);

        // Product Management
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('sub-categories', SubCategoryController::class)->except(['show']);
        Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
        Route::get('/products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template');
        Route::resource('products', ProductController::class);

        // AJAX
        Route::get('/ajax/sub-categories', [ProductController::class, 'getSubCategories'])->name('ajax.sub-categories');

        // Customer Edit/Delete (Admin only) — MUST be before {customer} wildcard
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::patch('/customers/{customer}', [CustomerController::class, 'update']);
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Supplier Management
        Route::resource('suppliers', SupplierController::class);

        // Reports (Admin only)
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/cashier', [ReportController::class, 'cashier'])->name('reports.cashier');
        Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');

        // Sale Refund (Admin only)
        Route::post('/pos/refund/{sale}', [PosController::class, 'refund'])->name('pos.refund');
        Route::post('/pos/refund-partial/{sale}', [PosController::class, 'partialRefund'])->name('pos.partial-refund');

        // Stock Transfer between showrooms (Admin only)
        Route::get('/inventory/transfer', [\App\Http\Controllers\StockTransferController::class, 'form'])->name('inventory.transfer');
        Route::post('/inventory/transfer', [\App\Http\Controllers\StockTransferController::class, 'transfer'])->name('inventory.transfer.store');

        // Inventory Updates (Admin only)
        Route::get('/inventory/import', [InventoryController::class, 'importForm'])->name('inventory.import');
        Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import.store');
        Route::get('/inventory/import/template', [InventoryController::class, 'importTemplate'])->name('inventory.import.template');


        Route::get('/inventory/stock-in', [InventoryController::class, 'stockInForm'])->name('inventory.stock-in');
        Route::post('/inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in.store');
        Route::get('/inventory/stock-out', [InventoryController::class, 'stockOutForm'])->name('inventory.stock-out');
        Route::post('/inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out.store');
        Route::get('/inventory/adjustment', [InventoryController::class, 'adjustmentForm'])->name('inventory.adjustment');
        Route::post('/inventory/adjustment', [InventoryController::class, 'adjustment'])->name('inventory.adjustment.store');

        // Returns Actions (Admin only)
        Route::post('/returns/{return}/accept', [\App\Http\Controllers\ShowroomReturnController::class, 'accept'])->name('returns.accept');
        Route::post('/returns/{return}/reject', [\App\Http\Controllers\ShowroomReturnController::class, 'reject'])->name('returns.reject');
        // Promotions
        Route::resource('promos', PromoCodeController::class)->except(['show']);
        Route::patch('/promos/{promo}/toggle-status', [PromoCodeController::class, 'toggleStatus'])->name('promos.toggle-status');
    });

    // Customer show — AFTER admin routes to prevent {customer} from matching "create"
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
});

require __DIR__.'/auth.php';
