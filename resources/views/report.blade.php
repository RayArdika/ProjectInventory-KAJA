@extends('layouts.app')

@section('title', 'Report')

@section('content')

<!-- TOP -->

<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">

    <div class="card">

        <p class="text-gray-500">
            Produk
        </p>

        <h1 class="text-4xl font-bold mt-4">

            {{ $totalProducts }}

        </h1>

    </div>

    <div class="card">

        <p class="text-gray-500">
            Stock
        </p>

        <h1 class="text-4xl font-bold mt-4">

            {{ $totalStock }}

        </h1>

    </div>

    <div class="card">

        <p class="text-gray-500">
            Produksi
        </p>

        <h1 class="text-4xl font-bold mt-4">

            {{ $totalProductions }}

        </h1>

    </div>

    <div class="card">

        <p class="text-gray-500">
            Distribusi
        </p>

        <h1 class="text-4xl font-bold mt-4">

            {{ $totalDistribution }}

        </h1>

    </div>

    <div class="card">

        <p class="text-gray-500">
            Payroll
        </p>

        <h1 class="text-3xl font-bold mt-4">

            Rp{{ number_format($totalPayroll, 0, ',', '.') }}

        </h1>

    </div>

</div>

<!-- CHART -->

<div class="card">

    <div class="mb-8">

        <h2 class="text-3xl font-bold">

            Production Analytics

        </h2>

        <p class="text-gray-500 mt-2">

            Statistik produksi realtime

        </p>

    </div>

    <canvas id="reportChart"></canvas>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx =
    document.getElementById('reportChart');

new Chart(ctx, {

    type: 'bar',

    data: {

        labels: @json($chartLabels),

        datasets: [{

            label: 'Qty Produksi',

            data: @json($chartData),

            borderWidth: 2,

            borderRadius: 20,

            backgroundColor:
                'rgba(59,130,246,0.5)',

            borderColor:
                'rgba(59,130,246,1)',

        }]
    },

    options: {

        responsive: true,

    }

});

</script>

@endsection
