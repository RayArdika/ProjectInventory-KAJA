<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $payrolls = $this->filteredQuery($request)
            ->with('production')
            ->latest()
            ->get();

        $workers = Payroll::query()
            ->when(
                auth()->user()->role !== 'admin',
                fn ($query) => $query->where('worker_name', auth()->user()->name)
            )
            ->distinct()
            ->orderBy('worker_name')
            ->pluck('worker_name');

        $activities = Payroll::query()
            ->when(
                auth()->user()->role !== 'admin',
                fn ($query) => $query->where('worker_name', auth()->user()->name)
            )
            ->whereNotNull('activity_name')
            ->distinct()
            ->orderBy('activity_name')
            ->pluck('activity_name');

        $grandTotal = $payrolls->sum('total_salary');

        return view(
            'payroll',
            compact('payrolls', 'workers', 'activities', 'grandTotal')
        );
    }

    public function exportPdf(Request $request)
    {
        $payrolls = $this->filteredQuery($request)
            ->with('production')
            ->latest()
            ->get();

        $grandTotal = $payrolls->sum('total_salary');
        $filters = $request->only(['date_from', 'date_to', 'worker', 'activity']);
        $logoPath = public_path('images/LOGO HIJAU.png');
        $logoData = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView(
            'payroll-pdf',
            compact('payrolls', 'grandTotal', 'filters', 'logoData')
        )->setPaper('a4', 'landscape');

        return $pdf->download('laporan-payroll.pdf');
    }

    private function filteredQuery(Request $request): Builder
    {
        return Payroll::query()
            ->when(
                auth()->user()->role !== 'admin',
                fn ($query) => $query->where('worker_name', auth()->user()->name)
            )
            ->when(
                auth()->user()->role === 'admin' && $request->filled('worker'),
                fn ($query) => $query->where('worker_name', $request->worker)
            )
            ->when(
                $request->filled('activity'),
                fn ($query) => $query->where('activity_name', $request->activity)
            )
            ->when(
                $request->filled('date_from'),
                fn ($query) => $query->whereHas(
                    'production',
                    fn ($production) => $production->whereDate(
                        'production_date',
                        '>=',
                        $request->date_from
                    )
                )
            )
            ->when(
                $request->filled('date_to'),
                fn ($query) => $query->whereHas(
                    'production',
                    fn ($production) => $production->whereDate(
                        'production_date',
                        '<=',
                        $request->date_to
                    )
                )
            );
    }
}
