@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $formatCurrency = fn ($value) => 'Rp' . number_format((float) $value, 0, ',', '.');
    $formatStock = fn ($value) => number_format((float) $value, 0, ',', '.');
@endphp

<form method="GET" action="/dashboard" class="card mb-8 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
    <label class="date-field">
        <span class="block mb-2 font-semibold">Date</span>
        <input type="date" name="date" value="{{ $date }}" class="w-full">
    </label>

    <label>
        <span class="block mb-2 font-semibold">Client</span>
        <select name="client">
            <option value="">All Clients</option>
            @foreach($clients as $item)
                <option value="{{ $item }}" @selected($client === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </label>

    <label>
        <span class="block mb-2 font-semibold">Category</span>
        <select name="category">
            <option value="">All Categories</option>
            @foreach($categories as $item)
                <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </label>

    <div class="flex gap-3">
        <button class="btn-green flex-1">Apply</button>
        <a href="/dashboard" class="bg-gray-100 text-gray-700 px-5 py-4 rounded-2xl font-semibold">Reset</a>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 mb-8">
    <div class="card"><p class="text-gray-500">Total Sales Amount</p><h1 class="text-4xl font-bold mt-3">{{ $formatCurrency($totalSalesAmount) }}</h1><p class="text-gray-400 mt-2">{{ $totalSales }} items distributed</p></div>
    <div class="card"><p class="text-gray-500">Total Unpaid</p><h1 class="text-4xl font-bold text-red-500 mt-3">{{ $formatCurrency($totalUnpaidAmount) }}</h1></div>
    <div class="card"><p class="text-gray-500">Total Production</p><h1 class="text-5xl font-bold mt-3">{{ $totalProductions }}</h1></div>
    <div class="card"><p class="text-gray-500">Total Attendance</p><h1 class="text-5xl font-bold mt-3">{{ $totalAttendance }}</h1></div>
    <div class="card"><p class="text-gray-500">Work Results</p><h1 class="text-5xl font-bold mt-3">{{ $totalWorkResults }}</h1></div>
</div>

@if($lowProductStocks->count() > 0 || $lowMaterialStocks->count() > 0)
    <div class="mb-8 bg-red-50 border border-red-200 rounded-3xl p-6">
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-red-600">Critical Stock Alert</h2>
            <p class="text-red-400">Only items with stock below their configured minimum stock are shown.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($lowProductStocks as $item)
                <div class="bg-white rounded-2xl p-5 border border-red-100">
                    <h3 class="font-bold text-lg">{{ $item->product_name }}</h3>
                    <p class="text-red-500 mt-2 font-semibold">Stock: {{ $formatStock($item->stock) }} {{ $item->unit }}</p>
                    <p class="text-gray-500 text-sm">Minimum: {{ $formatStock($item->minimum_stock) }}</p>
                </div>
            @endforeach

            @foreach($lowMaterialStocks as $item)
                <div class="bg-white rounded-2xl p-5 border border-red-100">
                    <h3 class="font-bold text-lg">{{ $item->material_name }}</h3>
                    <p class="text-red-500 mt-2 font-semibold">Stock: {{ $formatStock($item->stock) }} {{ $item->unit }}</p>
                    <p class="text-gray-500 text-sm">Minimum: {{ $formatStock($item->minimum_stock) }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
    <div class="card">
        <h2 class="text-2xl font-bold mb-2">Production by Variant</h2>
        <p class="text-gray-500 mb-6">Filtered by selected date, category, and client where applicable</p>
        <canvas id="productionChart"></canvas>
    </div>

    <div class="card">
        <h2 class="text-2xl font-bold mb-6">Sales by Client</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="text-left text-gray-500 border-b"><th class="pb-4">Client</th><th class="pb-4">Qty</th><th class="pb-4">Amount</th></tr></thead>
                <tbody>
                    @forelse($salesByClient as $row)
                        <tr class="border-b"><td class="py-4">{{ $row->destination }}</td><td>{{ $row->total_qty }}</td><td>{{ $formatCurrency($row->total_amount) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-5 text-gray-500">No sales data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
    <div class="card">
        <h2 class="text-2xl font-bold mb-6">Top Clients</h2>
        <div class="space-y-3">
            @forelse($clientRanking as $index => $row)
                <div class="border-b pb-3">
                    <div class="flex justify-between"><span>{{ $index + 1 }}. {{ $row->destination }}</span><strong>{{ $row->total_qty }}</strong></div>
                    <div class="text-gray-500 text-sm">{{ $row->total_orders }} orders - {{ $formatCurrency($row->total_amount) }}</div>
                </div>
            @empty
                <p class="text-gray-500">No client ranking found.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <h2 class="text-2xl font-bold mb-6">Production by Worker</h2>
        <div class="space-y-3">
            @forelse($productionByWorker as $row)
                <div class="flex justify-between border-b pb-3"><span>{{ $row->worker_name }}</span><strong>{{ $row->total_qty }}</strong></div>
            @empty
                <p class="text-gray-500">No production data found.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <h2 class="text-2xl font-bold mb-6">Attendance</h2>
        <div class="space-y-3">
            @forelse($attendanceRows as $row)
                <div class="flex justify-between border-b pb-3"><span>{{ $row->production_date }} - {{ $row->worker_name }}</span><strong>Present</strong></div>
            @empty
                <p class="text-gray-500">No attendance data found.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <h2 class="text-2xl font-bold mb-6">Work Results</h2>
        <div class="space-y-3">
            @forelse($workResults as $row)
                <div class="border-b pb-3">
                    <div class="font-semibold">{{ $row->production_date }} - {{ $row->worker_name }}</div>
                    <div class="text-gray-500">{{ $row->product_name }}: {{ $row->total_qty }}</div>
                </div>
            @empty
                <p class="text-gray-500">No work result data found.</p>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('productionChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Passed Qty',
            data: @json($chartData),
            borderWidth: 2,
            borderRadius: 12,
            backgroundColor: 'rgba(34,197,94,0.5)',
            borderColor: 'rgba(22,163,74,1)',
        }]
    },
    options: { responsive: true }
});
</script>

@endsection
