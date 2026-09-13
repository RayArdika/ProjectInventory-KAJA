@extends('layouts.app')

@section('title', 'Master Data')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    <!-- FORM -->

    <div class="xl:col-span-1">

        <div class="card">

            <h2 class="text-3xl font-bold mb-8">
                Tambah Produk
            </h2>

            <form method="POST" action="/products">

                @csrf

                <div class="mb-5">

                    <label class="block mb-2 text-sm font-semibold">
                        SKU
                    </label>

                    <input
                    type="text"
                    name="sku"
                    placeholder="SKU Produk"
                    class="w-full rounded-2xl border-0 bg-gray-100 p-4">

                </div>

                <div class="mb-5">

                    <label class="block mb-2 text-sm font-semibold">
                        Nama Produk
                    </label>

                    <input
                    type="text"
                    name="product_name"
                    placeholder="Nama produk"
                    class="w-full rounded-2xl border-0 bg-gray-100 p-4">

                </div>

                <div class="mb-5">

                    <label class="block mb-2 text-sm font-semibold">
                        Stock
                    </label>

                    <input
                    type="number"
                    name="stock"
                    placeholder="0"
                    class="w-full rounded-2xl border-0 bg-gray-100 p-4">

                </div>

                <div class="mb-5">

                    <label class="block mb-2 text-sm font-semibold">
                        Tarif Pekerja
                    </label>

                    <input
                    type="number"
                    name="worker_fee"
                    placeholder="0"
                    class="w-full rounded-2xl border-0 bg-gray-100 p-4">

                </div>

                <button
                type="submit"
                style="
                width:100%;
                background:#16a34a;
                color:white;
                padding:16px;
                border-radius:18px;
                font-weight:600;
                border:none;
                cursor:pointer;
                ">

                    Simpan Produk

                </button>

            </form>

        </div>

    </div>


    <div class="card mb-6">

    <h2 class="text-2xl font-bold mb-5">
        Import Excel Produk
    </h2>

    <form
        action="/products/import"
        method="POST"
        enctype="multipart/form-data"
        class="flex gap-4 items-center">

        @csrf

        <input
            type="file"
            name="file"
            class="bg-gray-100 p-3 rounded-2xl"
            required>

        <button
            type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-2xl">

            Import Excel

        </button>

    </form>

</div>
    <!-- TABLE -->

    <div class="xl:col-span-2">

        <div class="card">

            <h2 class="text-3xl font-bold mb-8">
                Data Produk
            </h2>

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead>

                        <tr class="text-left text-gray-500 border-b">

                            <th class="pb-4">SKU</th>
                            <th class="pb-4">Produk</th>
                            <th class="pb-4">Stock</th>
                            <th class="pb-4">Fee</th>
                            <th class="pb-4">Action</th>

                        </tr>

                    </thead>

                    <tbody>

@foreach($products as $product)

<tr class="border-b">

    <td class="py-5">
        {{ $product->sku }}
    </td>

    <td>
        {{ $product->product_name }}
    </td>

    <td>
        {{ $product->stock }}
    </td>

    <td>
        Rp{{ number_format($product->worker_fee, 0, ',', '.') }}
    </td>

    <td class="py-5">

    <div class="flex gap-2">

        <!-- EDIT -->

        <a
            href="/products/{{ $product->id }}/edit"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl">

            Edit

        </a>

        <!-- DELETE -->

        <form
            action="/products/{{ $product->id }}"
            method="POST"
            onsubmit="return confirm('Hapus produk ini?')">

            @csrf
            @method('DELETE')

            <button
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl">

                Delete

            </button>

        </form>

    </div>

</td>
    </td>

</tr>

@endforeach

</tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection
