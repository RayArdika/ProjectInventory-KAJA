@extends('layouts.app')

@section('title', 'Work Activities')

@section('content')

@php
    $formatCurrency = fn ($value) => 'Rp' . number_format((float) $value, 0, ',', '.');
    $formatNumber = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
@endphp

<style>
    .work-activity-checkbox{
        appearance:auto !important;
        -webkit-appearance:checkbox !important;
        width:20px !important;
        height:20px !important;
        min-height:20px !important;
        flex:0 0 20px;
        padding:0 !important;
        border-radius:4px !important;
        background:#fff !important;
        accent-color:#22c55e;
        cursor:pointer;
    }
</style>

<div class="space-y-6">
    <div class="card">
        <h2 class="text-2xl font-bold mb-2">Add Work Activity</h2>
        <p class="text-gray-500 mb-6">Configure the fee and materials consumed for one completed unit.</p>

        @if($errors->any())
            <div class="mb-5 bg-red-50 text-red-600 border border-red-100 rounded-2xl p-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/work-activities" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <label>
                    <span class="block mb-2 font-semibold">SKU / Product</span>
                    <select name="product_id" required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->sku }} - {{ $product->product_name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="block mb-2 font-semibold">Activity</span>
                    <input name="activity_name" value="{{ old('activity_name') }}" placeholder="Packing Kantong" required>
                </label>

                <label>
                    <span class="block mb-2 font-semibold">Unit</span>
                    <input name="unit" value="{{ old('unit', 'pcs') }}" readonly required>
                </label>

                <label>
                    <span class="block mb-2 font-semibold">Fee / Unit</span>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-500">Rp</span>
                        <input type="number" name="fee_per_unit" min="0" step="0.01" value="{{ old('fee_per_unit', 0) }}" required>
                    </div>
                </label>

                <div class="flex flex-col justify-end gap-3 pb-3">
                    <label class="flex items-center gap-2"><input class="work-activity-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Active</label>
                </div>
            </div>

            <div>
                <h3 class="font-bold mb-3">Materials per Completed Unit</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @for($i = 0; $i < 3; $i++)
                        <div class="grid grid-cols-[1fr_120px] gap-2">
                            <select name="material_id[]">
                                <option value="">No material</option>
                                @foreach($materials as $material)
                                    <option value="{{ $material->id }}" @selected(old("material_id.$i") == $material->id)>
                                        {{ $material->material_name }} ({{ $material->unit }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" name="qty_needed[]" min="0.001" step="0.001" value="{{ old("qty_needed.$i") }}" placeholder="Qty">
                        </div>
                    @endfor
                </div>
            </div>

            <button class="btn-green">Save Work Activity</button>
        </form>
    </div>

    <div class="card">
        <h2 class="text-2xl font-bold mb-6">Activity Configuration</h2>

        <div class="space-y-5">
            @forelse($activities as $activity)
                <div id="activity-{{ $activity->id }}" class="border rounded-xl p-5 scroll-mt-8 target:ring-4 target:ring-green-200">
                    <form action="/work-activities/{{ $activity->id }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                            <label>
                                <span class="block mb-2 text-sm font-semibold">SKU / Product</span>
                                <select name="product_id" required>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" @selected(old('product_id', $activity->product_id) == $product->id)>
                                            {{ $product->sku }} - {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                <span class="block mb-2 text-sm font-semibold">Activity</span>
                                <input name="activity_name" value="{{ old('activity_name', $activity->activity_name) }}" required>
                            </label>

                            <label>
                                <span class="block mb-2 text-sm font-semibold">Unit</span>
                                <input name="unit" value="{{ old('unit', $activity->unit) }}" readonly required>
                            </label>

                            <label>
                                <span class="block mb-2 text-sm font-semibold">Fee / Unit</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-500">Rp</span>
                                    <input type="number" name="fee_per_unit" min="0" step="0.01" value="{{ old('fee_per_unit', (float) $activity->fee_per_unit) }}" required>
                                </div>
                            </label>

                            <div class="flex flex-col justify-end gap-3 pb-3">
                                <label class="flex items-center gap-2"><input class="work-activity-checkbox" type="checkbox" name="is_active" value="1" @checked(old('is_active', $activity->is_active))> Active</label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @for($i = 0; $i < 3; $i++)
                                @php $requirement = $activity->materialRequirements->get($i); @endphp
                                <div class="grid grid-cols-[1fr_120px] gap-2">
                                    <select name="material_id[]">
                                        <option value="">No material</option>
                                        @foreach($materials as $material)
                                            <option value="{{ $material->id }}" @selected(old("material_id.$i", $requirement?->material_id) == $material->id)>
                                                {{ $material->material_name }} ({{ $material->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="qty_needed[]" min="0.001" step="0.001" value="{{ old("qty_needed.$i", $requirement ? (float) $requirement->qty_needed : '') }}" placeholder="Qty">
                                </div>
                            @endfor
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-500">
                                Fee: <strong>{{ $formatCurrency($activity->fee_per_unit) }}/{{ $activity->unit }}</strong>
                                @foreach($activity->materialRequirements as $requirement)
                                    <span class="ml-3">{{ $requirement->material->material_name }}: {{ $formatNumber($requirement->qty_needed) }} {{ $requirement->material->unit }}</span>
                                @endforeach
                            </div>
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-3 rounded-xl">Update</button>
                        </div>
                    </form>

                    <form action="/work-activities/{{ $activity->id }}" method="POST" class="mt-3" onsubmit="return confirm('Delete this work activity?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-500 font-semibold">Delete activity</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500">No work activity has been configured.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection
