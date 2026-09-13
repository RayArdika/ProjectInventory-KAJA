@extends('layouts.app')

@section('title', 'Payroll')

@section('content')

@php
    $formatCurrency = fn ($value) => 'Rp' . number_format((float) $value, 0, ',', '.');
    $formatQuantity = fn ($value) => number_format((int) $value, 0, '.', ',');
@endphp

<div class="card">
    <div class="mb-7">
        <h2 class="text-3xl font-bold">Employee Payroll</h2>
        <p class="text-gray-500 mt-2">Filter payroll before viewing or exporting the report.</p>
    </div>

    <form method="GET" action="/payroll" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3 items-end mb-8">
        <label>
            <span class="block mb-2 text-sm font-semibold">Date From</span>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full">
        </label>

        <label>
            <span class="block mb-2 text-sm font-semibold">Date To</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full">
        </label>

        @if(auth()->user()->role === 'admin')
            <label>
                <span class="block mb-2 text-sm font-semibold">Worker</span>
                <select name="worker" class="w-full">
                    <option value="">All Workers</option>
                    @foreach($workers as $worker)
                        <option value="{{ $worker }}" @selected(request('worker') === $worker)>{{ $worker }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <label>
            <span class="block mb-2 text-sm font-semibold">Activity</span>
            <select name="activity" class="w-full">
                <option value="">All Activities</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity }}" @selected(request('activity') === $activity)>{{ $activity }}</option>
                @endforeach
            </select>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="btn-green">Apply Filter</button>
            <a href="/payroll" class="bg-gray-200 hover:bg-gray-300 px-5 py-3 rounded-xl font-semibold flex items-center">Reset</a>
        </div>
    </form>

    <div class="flex justify-end mb-5">
        <a href="{{ url('/payroll/pdf') . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}" class="btn-green inline-block">
            Export Filtered PDF
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1050px]">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-4">Production Date</th>
                    <th class="pb-4">Worker</th>
                    <th class="pb-4">Activity</th>
                    <th class="pb-4">Product</th>
                    <th class="pb-4">Result</th>
                    <th class="pb-4">Unit</th>
                    <th class="pb-4">Fee / Unit</th>
                    <th class="pb-4">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                    <tr class="border-b">
                        <td class="py-5">{{ $payroll->production?->production_date ?? '-' }}</td>
                        <td>{{ $payroll->worker_name }}</td>
                        <td>{{ $payroll->activity_name ?: '-' }}</td>
                        <td>{{ $payroll->product_name }}</td>
                        <td>{{ $formatQuantity($payroll->qty) }}</td>
                        <td>{{ $payroll->unit ?: 'pcs' }}</td>
                        <td>{{ $formatCurrency($payroll->fee) }}</td>
                        <td class="font-bold text-green-600">{{ $formatCurrency($payroll->total_salary) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-gray-500">No payroll data matches the selected filter.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t-2">
                    <td colspan="7" class="py-5 text-right text-lg font-bold">Grand Total</td>
                    <td class="py-5 text-lg font-bold text-green-600">{{ $formatCurrency($grandTotal) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
