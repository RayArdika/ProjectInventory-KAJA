<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Models\Production;
use App\Models\Payroll;
use App\Models\Material;
use App\Models\Distribution;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\WorkActivityController;
use App\Http\Controllers\WorkerController;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role == 'admin') {

            return redirect('/dashboard');

        }

        /*
        |--------------------------------------------------------------------------
        | WORKER
        |--------------------------------------------------------------------------
        */

        return redirect('/produksi');

    }

    return redirect('/login');

});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| ADMIN ONLY
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | WORKER MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::get('/workers', [WorkerController::class, 'index'])->name('workers.index');
    Route::post('/workers', [WorkerController::class, 'store'])->name('workers.store');
    Route::put('/workers/{worker}', [WorkerController::class, 'update'])->name('workers.update');
    Route::patch('/workers/{worker}/reset-password', [WorkerController::class, 'resetPassword'])
        ->name('workers.reset-password');
    Route::patch('/workers/{worker}/status', [WorkerController::class, 'toggleStatus'])
        ->name('workers.toggle-status');
    Route::delete('/workers/{worker}', [WorkerController::class, 'destroy'])->name('workers.destroy');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        $date = request('date');
        $client = request('client');
        $category = request('category');

        $totalProducts = Product::count();

        $totalStock = Product::sum('stock');

        $totalPayroll = Payroll::sum('total_salary');

        $productionQuery = Production::where('status', 'Approved');
        $distributionQuery = Distribution::query();

        if ($date) {
            $productionQuery->whereDate('production_date', $date);
            $distributionQuery->whereDate('distribution_date', $date);
        }

        if ($client) {
            $distributionQuery->where('destination', $client);
        }

        if ($category) {
            $productNames = Product::where('category', $category)
                ->pluck('product_name');

            $productionQuery->whereIn('product_name', $productNames);
            $distributionQuery->whereHas('items', fn ($query) =>
                $query->whereIn('product_name', $productNames)
            );
        }

        $totalProductions = (clone $productionQuery)->sum('qty_pass');
        $totalSales = (clone $distributionQuery)->sum('qty_out');
        $totalSalesAmount = (clone $distributionQuery)->sum('total_amount');
        $totalUnpaidAmount = (clone $distributionQuery)
            ->selectRaw(
                'SUM(CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END) as total'
            )
            ->value('total') ?? 0;
        $totalAttendance = (clone $productionQuery)
            ->distinct('worker_name')
            ->count('worker_name');
        $totalWorkResults = (clone $productionQuery)
            ->selectRaw('SUM(qty_pass + qty_reject) as total')
            ->value('total') ?? 0;

        $topProduct = Production::where('status', 'Approved')
            ->select('product_name')
            ->selectRaw('SUM(qty_pass) as total')
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->first();

        $topWorker = Production::where('status', 'Approved')
            ->select('worker_name')
            ->selectRaw('SUM(qty_pass + qty_reject) as total')
            ->groupBy('worker_name')
            ->orderByDesc('total')
            ->first();

        $productionByVariant = (clone $productionQuery)->select('product_name')
            ->selectRaw('SUM(qty_pass) as total_qty')
            ->groupBy('product_name')
            ->get();

        $productionByWorker = (clone $productionQuery)->select('worker_name')
            ->selectRaw('SUM(qty_pass) as total_qty')
            ->groupBy('worker_name')
            ->orderByDesc('total_qty')
            ->get();

        $salesByClient = (clone $distributionQuery)->select('destination')
            ->selectRaw('SUM(qty_out) as total_qty')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->groupBy('destination')
            ->orderByDesc('total_qty')
            ->get();

        $clientRanking = (clone $distributionQuery)->select('destination')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(qty_out) as total_qty')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->groupBy('destination')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $workResults = (clone $productionQuery)->select('production_date', 'worker_name', 'product_name')
            ->selectRaw('SUM(qty_pass + qty_reject) as total_qty')
            ->groupBy('production_date', 'worker_name', 'product_name')
            ->latest('production_date')
            ->limit(10)
            ->get();

        $attendanceRows = (clone $productionQuery)->select('production_date', 'worker_name')
            ->selectRaw('COUNT(*) as total_entries')
            ->groupBy('production_date', 'worker_name')
            ->latest('production_date')
            ->limit(10)
            ->get();

        $chartLabels = $productionByVariant->pluck('product_name');

        $chartData = $productionByVariant->pluck('total_qty');

        $lowProductStocks = Product::where(function ($query) {
            $query->where('stock', '<=', 0)
                ->orWhere(function ($query) {
                    $query->where('minimum_stock', '>', 0)
                        ->whereColumn('stock', '<=', 'minimum_stock');
                });
        })
            ->get();

        $lowMaterialStocks = Material::where(function ($query) {
            $query->where('stock', '<=', 0)
                ->orWhere(function ($query) {
                    $query->where('minimum_stock', '>', 0)
                        ->whereColumn('stock', '<=', 'minimum_stock');
                });
        })
            ->get();

        $clients = Distribution::select('destination')
            ->whereNotNull('destination')
            ->distinct()
            ->orderBy('destination')
            ->pluck('destination');

        $categories = Product::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboard', compact(

            'totalProducts',
            'totalStock',
            'totalPayroll',
            'totalProductions',
            'totalSales',
            'totalSalesAmount',
            'totalUnpaidAmount',
            'totalAttendance',
            'totalWorkResults',
            'topProduct',
            'topWorker',
            'chartLabels',
            'chartData',
            'productionByVariant',
            'productionByWorker',
            'salesByClient',
            'clientRanking',
            'workResults',
            'attendanceRows',
            'lowProductStocks',
            'lowMaterialStocks',
            'clients',
            'categories',
            'date',
            'client',
            'category'

        ));

    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | QC
    |--------------------------------------------------------------------------
    */

    Route::get('/qc',
        [ProductionController::class, 'qc']);

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    Route::get('/products', fn () => redirect('/inventory'));

    Route::post('/products',
        [ProductController::class, 'store']);

    Route::get('/products/{id}/edit',
        [ProductController::class, 'edit']);

    Route::put('/products/{id}',
        [ProductController::class, 'update']);

    Route::delete('/products/{id}',
        [ProductController::class, 'destroy']);

    Route::post('/products/import',
        [ProductController::class, 'import']);

    /*
    |--------------------------------------------------------------------------
    | INVENTORY
    |--------------------------------------------------------------------------
    */

    Route::get('/inventory', [InventoryController::class, 'index']);

    Route::post('/inventory/items', [InventoryController::class, 'store']);

    Route::put('/inventory/items/{type}/{id}', [InventoryController::class, 'update']);

    Route::delete('/inventory/items/{type}/{id}', [InventoryController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | PRODUCTION MASTER
    |--------------------------------------------------------------------------
    */

    Route::get('/work-activities', [WorkActivityController::class, 'index']);

    Route::post('/work-activities', [WorkActivityController::class, 'store']);

    Route::put('/work-activities/{workActivity}', [WorkActivityController::class, 'update']);

    Route::delete('/work-activities/{workActivity}', [WorkActivityController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | DISTRIBUTION
    |--------------------------------------------------------------------------
    */

    Route::get('/distribution',
        [DistributionController::class, 'index']);

    Route::post('/distribution',
        [DistributionController::class, 'store']);

    Route::get('/distribution/pdf',
        [DistributionController::class, 'exportPdf']);

    Route::get('/distribution/{distribution}/invoice',
        [DistributionController::class, 'exportInvoice'])
        ->name('distribution.invoice');
    /*
    | Financial transactions are audit records. Saved distributions cannot be
    | edited or deleted to prevent sales, payment, and stock manipulation.
    */

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    Route::get('/activity-log',
        [ActivityLogController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | REPORT
    |--------------------------------------------------------------------------
    */

    Route::get('/report', fn () => redirect('/dashboard'));

});

/*
|--------------------------------------------------------------------------
| ALL USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PRODUKSI
    |--------------------------------------------------------------------------
    */

    Route::get('/produksi',
        [ProductionController::class, 'index']);

    Route::post('/produksi',
        [ProductionController::class, 'store']);

    Route::get('/produksi/{id}/edit',
        [ProductionController::class, 'edit']);

    Route::put('/produksi/{id}',
        [ProductionController::class, 'update']);

    Route::delete('/produksi/{id}',
        [ProductionController::class, 'destroy']);

    Route::post('/produksi/{id}/approve',
        [ProductionController::class, 'approve']);

    Route::post('/produksi/{id}/reject',
        [ProductionController::class, 'reject']);

    /*
    |--------------------------------------------------------------------------
    | PAYROLL
    |--------------------------------------------------------------------------
    */

    Route::get('/payroll',
        [PayrollController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | EXPORT PDF
    |--------------------------------------------------------------------------
    */

    Route::get('/payroll/pdf',
        [PayrollController::class, 'exportPdf']);

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    Route::get('/logout', function () {

        Auth::logout();

        return redirect('/login');

    });

});
