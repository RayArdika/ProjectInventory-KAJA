@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

@php
    $categories = $materials->pluck('category')->merge($products->pluck('category'))->filter()->unique()->values();
    $formatNumber = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
    $formatCurrency = fn ($value) => 'Rp' . number_format((float) $value, 0, ',', '.');
    $formatInputNumber = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    $criticalStockCount = $products
        ->concat($materials)
        ->filter(fn ($item) => (float) $item->stock <= 0)
        ->count();
    $stockStatus = function ($stock, $minimum, $warning = 0) {
        if ($stock <= 0) {
            return ['Critical', 'badge-danger'];
        }

        if ($minimum > 0 && $stock <= $minimum) {
            return ['Alert', 'badge-danger'];
        }

        if ($warning > 0 && $stock <= $warning) {
            return ['Warning', 'badge-warning'];
        }

        return ['Safe', 'badge-success'];
    };
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="card"><p class="text-gray-500 mb-2">Finished Goods</p><h2 class="text-4xl font-bold">{{ $products->count() }}</h2></div>
    <div class="card"><p class="text-gray-500 mb-2">Inventory Items</p><h2 class="text-4xl font-bold">{{ $materials->count() }}</h2></div>
    <div class="card"><p class="text-gray-500 mb-2">Categories</p><h2 class="text-4xl font-bold">{{ $categories->count() }}</h2></div>
    <div class="card"><p class="text-gray-500 mb-2">Critical Stock</p><h2 class="text-4xl font-bold text-red-500">{{ $criticalStockCount }}</h2></div>
</div>

<div class="card mb-8">
    <h2 class="text-2xl font-bold mb-6">Add Inventory Item</h2>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="/inventory/items" method="POST" class="inventory-entry-grid">
        @csrf

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Inventory Date</span>
            <input type="date" name="inventory_date" value="{{ now()->toDateString() }}" required>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Type</span>
            <select name="type" required>
                <option value="material">Material / Packaging</option>
                <option value="product">Finished Good</option>
            </select>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">SKU</span>
            <input value="{{ $nextProductSku }}" readonly>
            <span class="mt-2 block text-sm text-gray-500">Assigned automatically for finished goods.</span>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Name</span>
            <input name="name" required>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Category</span>
            <input name="category" list="inventoryCategories" placeholder="Find or type category">
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Unit</span>
            <input name="unit" value="pcs" readonly required>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Stock</span>
            <input type="number" step="1" min="0" name="stock" value="0">
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Minimum Stock</span>
            <input type="number" step="1" min="0" name="minimum_stock" value="0">
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Warning Stock</span>
            <input type="number" step="1" min="0" name="warning_stock" value="20">
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Cost / Unit</span>
            <div class="inventory-money-input">
                <span class="font-semibold text-gray-500">Rp</span>
                <input type="number" step="0.01" min="0" name="price_per_unit" value="0">
            </div>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Selling Price</span>
            <div class="inventory-money-input">
                <span class="font-semibold text-gray-500">Rp</span>
                <input type="number" step="0.01" min="0" name="selling_price" value="0">
            </div>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Worker Fee</span>
            <div class="inventory-money-input">
                <span class="font-semibold text-gray-500">Rp</span>
                <input type="number" step="1" min="0" name="worker_fee" value="0">
            </div>
        </label>

        <label class="inventory-entry-field">
            <span class="block mb-2 font-semibold">Note</span>
            <input name="note" placeholder="Optional note">
        </label>

        <button class="btn-green inventory-entry-submit">Save Item</button>
    </form>

    <datalist id="inventoryCategories">
        @foreach($categories as $category)
            <option value="{{ $category }}"></option>
        @endforeach
    </datalist>
</div>

<div class="card mb-8">
    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <div>
            <h2 class="text-3xl font-bold">Operational Inventory</h2>
            <p class="text-gray-500 mt-2">Jamu ingredients and packaging used by active products</p>
        </div>

        <div class="inventory-filter-select flex gap-3 flex-wrap">
            <select id="filterCategory">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>

            <input id="searchInventory" type="text" placeholder="Search..." class="min-w-[260px]">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1700px]">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-4">Category</th>
                    <th class="pb-4">Item</th>
                    <th class="pb-4">Unit</th>
                    <th class="pb-4">Stock</th>
                    <th class="pb-4">Minimum</th>
                    <th class="pb-4">Cost</th>
                    <th class="pb-4">Selling Price</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $material)
                    @php [$label, $class] = $stockStatus($material->stock, $material->minimum_stock); @endphp
                    <tr class="inventoryRow border-b" data-category="{{ strtolower($material->category) }}">
                        <form action="/inventory/items/material/{{ $material->id }}" method="POST">
                            @csrf
                            @method('PUT')
                            <td><input name="category" list="inventoryCategories" value="{{ $material->category }}"></td>
                            <td><input name="name" value="{{ $material->material_name }}"></td>
                            <td><input name="unit" value="pcs" readonly required></td>
                            <td><input type="number" step="1" min="0" name="stock" value="{{ (int) $material->stock }}"></td>
                            <td><input type="number" step="1" min="0" name="minimum_stock" value="{{ (int) $material->minimum_stock }}"></td>
                            <td>
                                <div class="flex items-center gap-2 min-w-[150px]">
                                    <span class="font-semibold text-gray-500">Rp</span>
                                    <input type="number" step="0.01" min="0" name="price_per_unit" value="{{ $formatInputNumber($material->price_per_unit) }}">
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 min-w-[150px]">
                                    <span class="font-semibold text-gray-500">Rp</span>
                                    <input type="number" step="0.01" min="0" name="selling_price" value="{{ $formatInputNumber($material->selling_price) }}">
                                </div>
                            </td>
                            <td><span class="{{ $class }}">{{ $label }}</span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl">Update</button>
                        </form>
                                    <form action="/inventory/items/material/{{ $material->id }}" method="POST" onsubmit="return confirm('Delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl">Delete</button>
                                    </form>
                                </div>
                            </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-6 text-gray-500">No inventory data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h2 class="text-3xl font-bold mb-2">Finished Goods</h2>
    <p class="mb-8 text-gray-500">SKU is generated automatically in ascending order.</p>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1500px]">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-4">SKU</th>
                    <th class="pb-4">Product</th>
                    <th class="pb-4">Category</th>
                    <th class="pb-4">Unit</th>
                    <th class="pb-4">Stock</th>
                    <th class="pb-4">Minimum</th>
                    <th class="pb-4">Warning</th>
                    <th class="pb-4">Cost / Unit</th>
                    <th class="pb-4">Selling Price</th>
                    <th class="pb-4">Worker Fee</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php [$label, $class] = $stockStatus($product->stock, $product->minimum_stock, $product->warning_stock); @endphp
                    <tr class="border-b">
                        <form action="/inventory/items/product/{{ $product->id }}" method="POST">
                            @csrf
                            @method('PUT')
                            <td><input value="{{ $product->sku }}" readonly></td>
                            <td><input name="name" value="{{ $product->product_name }}"></td>
                            <td><input name="category" list="inventoryCategories" value="{{ $product->category }}"></td>
                            <td><input name="unit" value="pcs" readonly required></td>
                            <td><input type="number" step="1" min="0" name="stock" value="{{ (int) $product->stock }}"></td>
                            <td><input type="number" step="1" min="0" name="minimum_stock" value="{{ (int) $product->minimum_stock }}"></td>
                            <td><input type="number" step="1" min="0" name="warning_stock" value="{{ (int) $product->warning_stock }}"></td>
                            <td>
                                <div class="flex items-center gap-2 min-w-[150px]">
                                    <span class="font-semibold text-gray-500">Rp</span>
                                    <input type="number" step="0.01" min="0" name="price_per_unit" value="{{ $formatInputNumber($product->price_per_unit) }}">
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 min-w-[150px]">
                                    <span class="font-semibold text-gray-500">Rp</span>
                                    <input type="number" step="0.01" min="0" name="selling_price" value="{{ $formatInputNumber($product->selling_price) }}">
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2 min-w-[150px]">
                                    <span class="font-semibold text-gray-500">Rp</span>
                                    <input type="number" step="1" min="0" name="worker_fee" value="{{ $formatInputNumber($product->worker_fee) }}">
                                </div>
                            </td>
                            <td><span class="{{ $class }}">{{ $label }}</span></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-xl">Update</button>
                        </form>
                                    <form action="/inventory/items/product/{{ $product->id }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl">Delete</button>
                                    </form>
                                </div>
                            </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="py-6 text-gray-500">No finished goods found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-8">
    <h2 class="text-3xl font-bold mb-2">Inventory Activity Log</h2>
    <p class="text-gray-500 mb-8">Timestamp is recorded automatically when the inventory action is submitted.</p>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1100px]">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="pb-4">Input Date</th>
                    <th class="pb-4">Timestamp</th>
                    <th class="pb-4">User</th>
                    <th class="pb-4">Action</th>
                    <th class="pb-4">Type</th>
                    <th class="pb-4">SKU</th>
                    <th class="pb-4">Item</th>
                    <th class="pb-4">Category</th>
                    <th class="pb-4">Stock</th>
                    <th class="pb-4">Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryLogs as $log)
                    <tr class="border-b">
                        <td class="py-5">{{ $log->inventory_date }}</td>
                        <td>{{ $log->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->user_name }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ ucfirst($log->item_type) }}</td>
                        <td>{{ $log->sku ?: '-' }}</td>
                        <td>{{ $log->item_name }}</td>
                        <td>{{ $log->category }}</td>
                        <td>{{ $formatNumber($log->stock) }} {{ $log->unit }}</td>
                        <td>{{ $log->note ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="py-6 text-gray-500">No inventory activity found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
const inventorySearch = document.getElementById('searchInventory');
const filterCategory = document.getElementById('filterCategory');
const inventoryRows = document.querySelectorAll('.inventoryRow');

function filterInventory() {
    const keyword = inventorySearch.value.toLowerCase();
    const category = filterCategory.value.toLowerCase();

    inventoryRows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowCategory = row.dataset.category;

        row.style.display = text.includes(keyword) && (category === '' || rowCategory === category) ? '' : 'none';
    });
}

inventorySearch.addEventListener('keyup', filterInventory);
filterCategory.addEventListener('change', filterInventory);
</script>

@endsection
