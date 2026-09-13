@extends('layouts.app')

@section('title', 'Edit Production')

@section('content')

<div class="max-w-3xl">

    <div class="card">

        <!-- HEADER -->

        <div class="flex justify-between items-center mb-8">

            <div>

                <h2 class="text-3xl font-bold">
                    Edit Production
                </h2>

                <p class="text-gray-500 mt-2">
                    Correct or update the submitted production entry
                </p>

            </div>

            <!-- STATUS -->

            <div>

                @if($production->status == 'Approved')

                    <span class="bg-green-100 text-green-600 px-5 py-3 rounded-2xl font-semibold">
                        Approved
                    </span>

                @elseif($production->status == 'Returned')

                    <span class="bg-red-100 text-red-600 px-5 py-3 rounded-2xl font-semibold">
                        Returned
                    </span>

                @else

                    <span class="bg-yellow-100 text-yellow-700 px-5 py-3 rounded-2xl font-semibold">
                        Pending
                    </span>

                @endif

            </div>

        </div>

        <!-- FORM -->

        <form
            action="/produksi/{{ $production->id }}"
            method="POST">

            @csrf
            @method('PUT')

            <!-- TANGGAL -->

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    Production Date <span class="text-red-500">*</span>
                </label>

                <input
                    type="date"
                    name="production_date"
                    value="{{ $production->production_date }}"
                    min="{{ now()->startOfMonth()->toDateString() }}"
                    max="{{ now()->toDateString() }}"
                    class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400"
                    required>

            </div>

            <!-- PEKERJA -->

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    Worker <span class="text-red-500">*</span>
                </label>

                @if(auth()->user()->role === 'admin')
                    <select
                        name="worker_name"
                        class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400"
                        required>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->name }}" @selected(old('worker_name', $production->worker_name) === $worker->name)>
                                {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input
                        type="text"
                        value="{{ auth()->user()->name }}"
                        class="w-full bg-gray-100 border-0 rounded-2xl p-4"
                        disabled>
                @endif

            </div>

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    Activity <span class="text-red-500">*</span>
                </label>

                <select
                    id="jobSelect"
                    name="activity_name"
                    class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400"
                    required>

                    <option value="">Select activity</option>
                    @foreach($jobOptions as $job)
                        <option
                            value="{{ $job }}"
                            @selected(old('activity_name', $production->activity_name) === $job)>
                            {{ $job }}
                        </option>
                    @endforeach

                </select>

            </div>

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    SKU / Item <span class="text-red-500">*</span>
                </label>

                <select
                    id="productSelect"
                    name="product_id"
                    class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400"
                    required>

                    <option value="">Select SKU / item</option>
                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            data-jobs='@json($product->workActivities->pluck("activity_name")->values())'
                            @selected(
                                (string) old('product_id', $production->workActivity?->product_id) ===
                                (string) $product->id
                            )>
                            {{ $product->sku }} - {{ $product->product_name }}
                        </option>
                    @endforeach

                </select>

            </div>
            <!-- QTY PASS -->

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    Completed Quantity <span class="text-red-500">*</span>
                </label>

                <input
                    type="number"
                    name="qty_pass"
                    value="{{ old('qty_pass', $production->qty_pass) }}"
                    min="1"
                    step="1"
                    placeholder="Minimum 1"
                    class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400"
                    required>

            </div>

            <!-- QTY REJECT -->

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    Rejected Quantity <span class="text-red-500">*</span>
                </label>

                <input
                    type="number"
                    name="qty_reject"
                    value="{{ old('qty_reject', $production->qty_reject) }}"
                    min="0"
                    step="1"
                    placeholder="Minimum 0"
                    class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400"
                    required>

            </div>

            <div class="mb-8">

                <label class="block mb-2 font-semibold">
                    Note
                </label>

                <input
                    name="note"
                    value="{{ $production->note }}"
                    placeholder="Required when rejected quantity is greater than 0"
                    class="w-full bg-gray-100 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-green-400">

            </div>

            <!-- BUTTON -->

            <div class="flex gap-3">

                <button
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-2xl font-semibold transition">

                    {{ $production->status === 'Returned' ? 'Resubmit Production' : 'Update Production' }}

                </button>

                <a
                    href="/produksi"
                    class="bg-gray-200 hover:bg-gray-300 px-6 py-4 rounded-2xl font-semibold transition">

                    Cancel

                </a>

            </div>

        </form>

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
