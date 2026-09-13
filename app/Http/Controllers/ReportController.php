<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Production;
use App\Models\Payroll;
use App\Models\Distribution;

class ReportController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | TOTAL DATA
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::count();

        $totalStock = Product::sum('stock');

        $totalProductions = Production::count();

        $totalPayroll = Payroll::sum('total_salary');

        $totalDistribution =
            Distribution::sum('qty_out');

        /*
        |--------------------------------------------------------------------------
        | CHART PRODUKSI
        |--------------------------------------------------------------------------
        */

        $chart = Production::select(
            'product_name'
        )
        ->selectRaw('SUM(qty_pass) as total_qty')
        ->groupBy('product_name')
        ->get();

        $chartLabels =
            $chart->pluck('product_name');

        $chartData =
            $chart->pluck('total_qty');

        return view(
            'report',
            compact(

                'totalProducts',

                'totalStock',

                'totalProductions',

                'totalPayroll',

                'totalDistribution',

                'chartLabels',

                'chartData'

            )
        );
    }
}