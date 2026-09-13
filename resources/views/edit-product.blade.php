@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')

<div class="max-w-2xl">

    <div class="card">

        <h2 class="text-3xl font-bold mb-8">
            Edit Produk
        </h2>

        <form
            action="/products/{{ $product->id }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    SKU
                </label>

                <input
                    type="text"
                    name="sku"
                    value="{{ $product->sku }}"
                    class="w-full bg-gray-100 rounded-2xl p-4">

            </div>

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    Nama Produk
                </label>

                <input
                    type="text"
                    name="product_name"
                    value="{{ $product->product_name }}"
                    class="w-full bg-gray-100 rounded-2xl p-4">

            </div>

            <div class="mb-5">

                <label class="block mb-2 font-semibold">
                    Stock
                </label>

                <input
                    type="number"
                    name="stock"
                    value="{{ $product->stock }}"
                    class="w-full bg-gray-100 rounded-2xl p-4">

            </div>

            <div class="mb-8">

                <label class="block mb-2 font-semibold">
                    Fee Pekerja
                </label>

                <input
                    type="number"
                    name="worker_fee"
                    value="{{ $product->worker_fee }}"
                    class="w-full bg-gray-100 rounded-2xl p-4">

            </div>

            <div class="flex gap-3">

                <button
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl">

                    Update Produk

                </button>

                <a
                    href="/products"
                    class="bg-gray-200 px-6 py-3 rounded-2xl">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>

@endsection