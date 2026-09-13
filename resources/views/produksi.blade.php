@extends('layouts.app')

@section('title', 'Production')

@section('content')

@php
    $formatQuantity = fn ($value) => number_format((int) $value, 0, '.', ',');
@endphp

<style>
    .production-history-heading {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        align-items: end;
        gap: 24px;
        margin-bottom: 24px;
    }

    .production-filter {
        display: grid;
        grid-template-columns: 180px 170px 220px auto;
        align-items: end;
        gap: 10px;
    }

    .production-filter .filter-actions {
        display: flex;
        gap: 8px;
    }

    .production-filter .filter-actions .btn-green {
        width: auto;
        min-height: 52px;
        white-space: nowrap;
    }

    .production-filter .reset-button {
        min-height: 52px;
        padding: 0 20px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
    }

    @media (max-width: 1180px) {
        .production-history-heading {
            grid-template-columns: 1fr;
        }

        .production-filter {
            grid-template-columns: repeat(3, minmax(160px, 1fr)) auto;
        }
    }

    @media (max-width: 760px) {
        .production-filter {
            grid-template-columns: 1fr;
        }

        .production-filter .filter-actions .btn-green {
            flex: 1;
        }
    }
</style>

<div class="space-y-6">

    <div class="card">
        <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
            <div>
                <h2 class="text-2xl font-bold mb-2">Operational Work Entry</h2>
                <p class="text-gray-500">Record the activity, item, and completed quantity.</p>
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="flex gap-2 flex-wrap">
                    <a href="/work-activities" class="bg-gray-200 hover:bg-gray-300 px-5 py-3 rounded-xl font-semibold">
                        Activity Master
                    </a>
                </div>
            @endif
        </div>

        <form action="/produksi" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            @csrf

            <label class="block">
                <span class="block mb-2 font-semibold">Production Date <span class="text-red-500">*</span></span>
                <input
                    type="date"
                    name="production_date"
                    value="{{ old('production_date', now()->toDateString()) }}"
                    min="{{ now()->startOfMonth()->toDateString() }}"
                    max="{{ now()->toDateString() }}"
                    class="w-full"
                    required>
            </label>

            <label class="block">
                <span class="block mb-2 font-semibold">Worker <span class="text-red-500">*</span></span>
                @if(auth()->user()->role === 'admin')
                    <select name="worker_name" class="w-full" required>
                        <option value="">Select worker</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->name }}" @selected(old('worker_name') === $worker->name)>
                                {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" value="{{ auth()->user()->name }}" class="w-full" disabled>
                @endif
            </label>

            <label class="block">
                <span class="block mb-2 font-semibold">Activity <span class="text-red-500">*</span></span>
                <select id="jobSelect" name="activity_name" class="w-full" required>
                    <option value="">Select activity</option>
                    @foreach($jobOptions as $job)
                        <option value="{{ $job }}" @selected(old('activity_name') === $job)>
                            {{ $job }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="block mb-2 font-semibold">SKU / Item <span class="text-red-500">*</span></span>
                <select id="productSelect" name="product_id" class="w-full" required>
                    <option value="">Select SKU / item</option>
                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-jobs='@json($product->workActivities->pluck("activity_name")->values())'
                            @selected((string) old('product_id') === (string) $product->id)>
                            {{ $product->sku }} - {{ $product->product_name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="block mb-2 font-semibold">Completed Quantity <span class="text-red-500">*</span></span>
                <input type="number" name="qty_pass" value="{{ old('qty_pass') }}" min="1" step="1" placeholder="Minimum 1" class="w-full" required>
            </label>

            <label class="block">
                <span class="block mb-2 font-semibold">Rejected Quantity <span class="text-red-500">*</span></span>
                <input type="number" name="qty_reject" value="{{ old('qty_reject', 0) }}" min="0" step="1" placeholder="Minimum 0" class="w-full" required>
            </label>

            <label class="block">
                <span class="block mb-2 font-semibold">Note</span>
                <input name="note" value="{{ old('note') }}" placeholder="Required when rejected quantity is greater than 0" class="w-full">
            </label>

            <button type="submit" class="btn-green w-full">Submit Production</button>
        </form>

        @if($jobOptions->isEmpty() || $products->isEmpty())
            <p class="text-red-500 mt-4 font-semibold">No job or product is available.</p>
        @endif
    </div>

    <div class="card">
        <div class="production-history-heading">
            <div>
                <h2 class="text-2xl font-bold">Production History</h2>
                <p class="text-gray-500 mt-1">Submitted production entries and approval status</p>
            </div>

            <form method="GET" action="/produksi" class="production-filter">
                <label class="block">
                    <span class="block mb-2 text-sm font-semibold text-gray-600">Production Date</span>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full">
                </label>

                <label class="block">
                    <span class="block mb-2 text-sm font-semibold text-gray-600">Status</span>
                    <select name="status" class="w-full">
                        <option value="">All Status</option>
                        <option value="Approved" @selected(request('status') === 'Approved')>Approved</option>
                        <option value="Returned" @selected(request('status') === 'Returned')>Returned</option>
                        <option value="Pending" @selected(request('status') === 'Pending')>Pending</option>
                    </select>
                </label>

                <label class="block">
                    <span class="block mb-2 text-sm font-semibold text-gray-600">Search Production</span>
                    <input name="search" type="search" value="{{ request('search') }}" placeholder="Worker, activity, item, or note" class="w-full">
                </label>

                <div class="filter-actions">
                    <button class="btn-green" type="submit" title="Apply filter">
                        <i class="fa-solid fa-magnifying-glass mr-2"></i>Search
                    </button>
                @if(request()->hasAny(['date', 'status', 'search']))
                        <a href="/produksi" class="reset-button bg-gray-200 hover:bg-gray-300 font-semibold" title="Reset filter">
                            <i class="fa-solid fa-rotate-left"></i>Reset
                        </a>
                @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px]">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-4">Date</th>
                        <th class="pb-4">Input Time</th>
                        <th class="pb-4">Worker</th>
                        <th class="pb-4">Activity</th>
                        <th class="pb-4">SKU / Item</th>
                        <th class="pb-4">Completed<br>Quantity</th>
                        <th class="pb-4">Rejected<br>Quantity</th>
                        <th class="pb-4">Total<br>Quantity</th>
                        <th class="pb-4">Note</th>
                        <th class="pb-4">Status</th>
                        <th class="pb-4">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($productions as $item)
                        <tr class="productionRow border-b">
                            <td class="py-5">{{ $item->production_date }}</td>
                            <td>{{ $item->created_at->timezone(config('app.timezone'))->format('H:i') }}</td>
                            <td>{{ $item->worker_name }}</td>
                            <td>{{ $item->activity_name ?: '-' }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $formatQuantity($item->qty_pass) }}</td>
                            <td>{{ $formatQuantity($item->qty_reject) }}</td>
                            <td class="font-semibold">{{ $formatQuantity($item->qty_pass + $item->qty_reject) }}</td>
                            <td>{{ $item->note ?: '-' }}</td>
                            <td>
                                @if($item->status == 'Approved')
                                    <span class="badge-success statusText">Approved</span>
                                @elseif($item->status == 'Returned')
                                    <span class="badge-danger statusText">Returned</span>
                                @else
                                    <span class="badge-warning statusText">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($item->status, ['Pending', 'Returned'], true))
                                    <div class="flex gap-2 flex-wrap">
                                        <a href="/produksi/{{ $item->id }}/edit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl">
                                            {{ $item->status === 'Returned' ? 'Correct & Resubmit' : 'Edit' }}
                                        </a>

                                        <form action="/produksi/{{ $item->id }}" method="POST" onsubmit="return confirm('Delete this production entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl">Delete</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">Processed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-6 text-gray-500">No production data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $productions->links() }}
        </div>
    </div>

</div>

<script>
const jobSelect = document.getElementById('jobSelect');
const productSelect = document.getElementById('productSelect');

function filterProducts() {
    const selectedJob = jobSelect.value;

    Array.from(productSelect.options).forEach(option => {
        if (!option.value) {
            return;
        }

        const jobs = JSON.parse(option.dataset.jobs || '[]');
        option.hidden = selectedJob !== '' && !jobs.includes(selectedJob);
        option.disabled = option.hidden;
    });

    const selectedProduct = productSelect.selectedOptions[0];
    if (selectedProduct?.disabled) {
        productSelect.value = '';
    }
}

jobSelect.addEventListener('change', filterProducts);
filterProducts();
</script>

@endsection
