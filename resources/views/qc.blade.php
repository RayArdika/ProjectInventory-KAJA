@extends('layouts.app')

@section('title', 'QC Approval')

@section('content')

@php
    $formatQuantity = fn ($value) => number_format((int) $value, 0, '.', ',');
@endphp

<div class="card">

    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">

        <div class="min-w-0">
            <h2 class="text-3xl font-bold">
                QC Approval
            </h2>

            <p class="text-gray-500 mt-1">
                Review pending entries, approve valid data, or return incorrect data for correction
            </p>
        </div>

        <input
            id="searchQc"
            type="text"
            placeholder="Search QC..."
            class="w-full min-w-0 bg-gray-100 rounded-2xl px-5 py-3 outline-none lg:w-80">
    </div>

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead>

                <tr class="text-left text-gray-500 border-b">

                    <th class="py-4">Date</th>
                    <th>Worker</th>
                    <th>Activity</th>
                    <th>Product</th>
                    <th>Completed</th>
                    <th>Rejected</th>
                    <th>Total</th>
                    <th>Note</th>
                    <th>Status</th>
                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($productions as $item)

                <tr class="qcRow border-b">

                    <td class="py-5">
                        {{ $item->production_date }}
                    </td>

                    <td>
                        {{ $item->worker_name }}
                    </td>

                    <td>
                        {{ $item->activity_name ?: '-' }}
                    </td>

                    <td>
                        {{ $item->product_name }}
                    </td>

                    <td>
                        {{ $formatQuantity($item->qty_pass) }}
                    </td>

                    <td>
                        {{ $formatQuantity($item->qty_reject) }}
                    </td>

                    <td>
                        {{ $formatQuantity($item->qty_pass + $item->qty_reject) }}
                    </td>

                    <td>
                        {{ $item->note ?: '-' }}
                    </td>

                    <td>
                        <span class="bg-yellow-100 text-yellow-600 px-4 py-2 rounded-xl text-sm">
                            Pending
                        </span>
                    </td>

                    <td class="py-4">

        <div class="flex gap-2">

            <form action="/produksi/{{ $item->id }}/approve" method="POST">
                @csrf

                <button
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-xl">
                    Approve
                </button>

            </form>

            <form action="/produksi/{{ $item->id }}/reject" method="POST" class="flex gap-2 items-center">
                @csrf

                <input
                    name="return_note"
                    placeholder="Correction reason"
                    class="min-w-[190px]"
                    required>

                <button
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-xl whitespace-nowrap">
                    Correction
                </button>

            </form>

        </div>

</td>

                </tr>

                @empty
                    <tr>
                        <td colspan="10" class="py-6 text-gray-500">No pending production entries.</td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

</div>
<script>

const qcSearch =
    document.getElementById('searchQc');

const qcRows =
    document.querySelectorAll('.qcRow');

qcSearch.addEventListener('keyup', function () {

    const keyword =
        this.value.toLowerCase();

    qcRows.forEach(row => {

        const text =
            row.innerText.toLowerCase();

        if (text.includes(keyword)) {

            row.style.display = '';

        } else {

            row.style.display = 'none';

        }

    });

});

</script>
@endsection
