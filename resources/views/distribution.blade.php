@extends('layouts.app')

@section('title', 'Distribution')

@section('content')
@php
    $formatNumber = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
    $formatCurrency = fn ($value) => 'Rp' . number_format((float) $value, 0, ',', '.');
    $formatCurrencyInput = fn ($value) => number_format((float) $value, 0, ',', '.');
    $formItems = old('items', [['product_id' => '', 'qty_out' => 1, 'price' => 0]]);
@endphp

<style>
    .distribution-items{display:grid;gap:12px}
    .distribution-item-row{display:grid;grid-template-columns:minmax(240px,2fr) minmax(120px,.7fr) minmax(150px,1fr) auto;gap:12px;align-items:end;border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc}
    .distribution-item-row label span{display:block;margin-bottom:7px;font-size:13px;font-weight:700;color:#334155}
    .distribution-item-row input,.distribution-item-row select{width:100%;min-height:48px;border:0;border-radius:12px;background:#fff;padding:11px 13px}
    .distribution-remove{min-height:48px;border:0;border-radius:12px;background:#ef4444;color:#fff;padding:0 16px;font-weight:700}
    .distribution-add{min-height:46px;border:0;border-radius:12px;background:#2563eb;color:#fff;padding:0 18px;font-weight:700}
    .distribution-preview{display:flex;flex-wrap:wrap;gap:22px;border-top:1px solid #e2e8f0;padding-top:16px;color:#475569}
    .distribution-preview strong{display:block;color:#0f172a;font-size:18px}
    .distribution-native-select{width:100%;min-height:58px;border:0;border-radius:18px;background:#f5f7fb;padding:16px}
    @media(max-width:760px){.distribution-item-row{grid-template-columns:minmax(0,1fr)}.distribution-remove{width:100%}}
</style>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card"><p class="text-gray-500 mb-2">Total Sales Amount</p><h2 class="text-4xl font-bold">{{ $formatCurrency($totalSalesAmount) }}</h2></div>
        <div class="card"><p class="text-gray-500 mb-2">Total Unpaid</p><h2 class="text-4xl font-bold text-red-500">{{ $formatCurrency($totalUnpaidAmount) }}</h2></div>
        <div class="card"><p class="text-gray-500 mb-2">Top Client</p><h2 class="text-2xl font-bold">{{ optional($topClients->first())->destination ?? '-' }}</h2></div>
    </div>

    <div class="card">
        <div class="mb-6">
            <h2 class="text-2xl font-bold">Create Distribution</h2>
            <p class="mt-1 text-sm text-gray-500">Add one or more approved products to the same transaction.</p>
        </div>

        <form action="/distribution" method="POST" class="space-y-5" id="distributionForm">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label><span class="block mb-2 font-semibold">Date</span><input type="date" name="distribution_date" value="{{ old('distribution_date', now()->toDateString()) }}" class="w-full" required></label>
                <label><span class="block mb-2 font-semibold">Client</span><input type="text" name="destination" value="{{ old('destination') }}" placeholder="Client name" class="w-full" required></label>
                <label><span class="block mb-2 font-semibold">Client Phone</span><input type="tel" name="client_phone" value="{{ old('client_phone') }}" placeholder="10-13 digit phone number" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13" class="w-full" required></label>
                <label>
                    <span class="block mb-2 font-semibold">Payment Method</span>
                    <select name="payment_method" class="native-select distribution-native-select" required>
                        @foreach(['Transfer', 'Cash', 'Credit', 'Other'] as $method)
                            <option value="{{ $method }}" @selected(old('payment_method', 'Transfer') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div><h3 class="text-lg font-bold">Products</h3><p class="text-sm text-gray-500">Each product can only be selected once.</p></div>
                    <button type="button" class="distribution-add" id="addDistributionItem">Add Product</button>
                </div>
                <div class="distribution-items" id="distributionItems">
                    @foreach($formItems as $index => $formItem)
                        <div class="distribution-item-row">
                            <label>
                                <span>Approved Product</span>
                                <select name="items[{{ $index }}][product_id]" class="native-select distribution-product" required>
                                    <option value="">Select product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ round((float) $product->selling_price) }}" @selected((string) ($formItem['product_id'] ?? '') === (string) $product->id)>
                                            {{ $product->sku }} - {{ $product->product_name }} (Stock {{ $formatNumber($product->stock) }})
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label><span>Quantity</span><input type="number" name="items[{{ $index }}][qty_out]" class="distribution-qty" min="1" step="1" value="{{ $formItem['qty_out'] ?? 1 }}" required></label>
                            <label><span>Price / Unit</span><input type="text" inputmode="numeric" name="items[{{ $index }}][price]" class="distribution-price currency-input" value="{{ $formatCurrencyInput($formItem['price'] ?? 0) }}" required></label>
                            <button type="button" class="distribution-remove" title="Remove product">Remove</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label><span class="block mb-2 font-semibold">Shipping Cost</span><input id="shippingCost" type="text" inputmode="numeric" name="shipping_cost" value="{{ $formatCurrencyInput(old('shipping_cost', 0)) }}" class="w-full currency-input"></label>
                <label><span class="block mb-2 font-semibold">Tax</span><input id="distributionTax" type="text" inputmode="numeric" name="tax" value="{{ $formatCurrencyInput(old('tax', 0)) }}" class="w-full currency-input"></label>
                <label>
                    <span class="block mb-2 font-semibold">Payment Status</span>
                    <select id="createPaymentStatus" name="payment_status" class="native-select distribution-native-select w-full" required>
                        @foreach(['Unpaid', 'Partial', 'Paid'] as $status)
                            <option value="{{ $status }}" @selected(old('payment_status', 'Unpaid') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <label><span class="block mb-2 font-semibold">Paid Amount</span><input id="createPaidAmount" type="text" inputmode="numeric" name="paid_amount" value="{{ $formatCurrencyInput(old('paid_amount', 0)) }}" class="w-full currency-input"><span id="createPaymentHint" class="mt-2 block text-sm text-gray-500"></span></label>
                <label class="md:col-span-2 xl:col-span-4"><span class="block mb-2 font-semibold">Note</span><input type="text" name="note" value="{{ old('note') }}" placeholder="Optional note" class="w-full"></label>
            </div>

            <div class="distribution-preview">
                <div>Subtotal<strong id="previewSubtotal">Rp0</strong></div>
                <div>Total<strong id="previewTotal">Rp0</strong></div>
            </div>

            <button type="submit" class="btn-green w-full" @disabled($products->isEmpty())>Submit Distribution</button>
        </form>

        @if($products->isEmpty())<p class="mt-4 font-semibold text-red-500">No approved product with available stock is ready for distribution.</p>@endif
    </div>

    <div class="card">
        <h2 class="text-2xl font-bold mb-6">Top Clients</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="border-b text-left text-gray-500"><th class="pb-4">Rank</th><th class="pb-4">Client</th><th class="pb-4">Orders</th><th class="pb-4">Qty</th><th class="pb-4">Sales Amount</th></tr></thead>
                <tbody>
                    @forelse($topClients as $index => $client)
                        <tr class="border-b"><td class="py-4">{{ $index + 1 }}</td><td>{{ $client->destination }}</td><td>{{ $client->total_orders }}</td><td>{{ $client->total_qty }}</td><td>{{ $formatCurrency($client->total_amount) }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-gray-500">No client ranking data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="mb-8"><h2 class="text-2xl font-bold">Distribution History</h2><p class="mt-2 text-gray-500">Filter transactions, export reports, or print an invoice.</p></div>
        <form method="GET" action="/distribution" class="mb-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
            <label><span class="block mb-2 font-semibold">Client</span><select name="client"><option value="">All Clients</option>@foreach($clients as $client)<option value="{{ $client }}" @selected(request('client') === $client)>{{ $client }}</option>@endforeach</select></label>
            <label class="date-field"><span class="block mb-2 font-semibold">Date From</span><input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full"></label>
            <label class="date-field"><span class="block mb-2 font-semibold">Date To</span><input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full"></label>
            <label><span class="block mb-2 font-semibold">Payment Status</span><select name="payment_status" class="native-select distribution-native-select"><option value="">All Status</option>@foreach(['Unpaid', 'Partial', 'Paid'] as $status)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ $status }}</option>@endforeach</select></label>
            <div class="distribution-filter-actions md:col-span-2 xl:col-span-4">
                <button type="submit" class="distribution-filter-button distribution-filter-apply">Apply Filter</button>
                <a href="/distribution" class="distribution-filter-button distribution-filter-reset">Reset</a>
                <button type="submit" formaction="/distribution/pdf" class="distribution-filter-button distribution-filter-export">Export Report PDF</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1250px]">
                <thead><tr class="border-b text-left text-gray-500"><th class="pb-4">Invoice</th><th class="pb-4">Date</th><th class="pb-4">Client</th><th class="pb-4">Products</th><th class="pb-4">Qty</th><th class="pb-4">Total</th><th class="pb-4">Payment</th><th class="pb-4">Paid</th><th class="pb-4">Unpaid</th><th class="pb-4">Note</th><th class="pb-4">Action</th></tr></thead>
                <tbody>
                    @forelse($distributions as $item)
                        @php
                            $historyItems = $item->items->isNotEmpty() ? $item->items : collect([(object) ['product_name' => $item->product_name, 'qty_out' => $item->qty_out, 'unit' => 'pcs']]);
                        @endphp
                        <tr class="border-b align-top">
                            <td class="py-5 font-semibold">{{ $item->invoice_number ?: '#'.$item->id }}</td>
                            <td>{{ $item->distribution_date }}</td>
                            <td>{{ $item->destination }}<div class="text-xs text-gray-500">{{ $item->client_phone }}</div></td>
                            <td>@foreach($historyItems as $line)<div>{{ $line->product_name }}</div>@endforeach</td>
                            <td>@foreach($historyItems as $line)<div>{{ $line->qty_out }} {{ $line->unit }}</div>@endforeach</td>
                            <td class="font-semibold">{{ $formatCurrency($item->total_amount) }}</td>
                            <td>@if($item->payment_status === 'Paid')<span class="badge-success">Paid</span>@elseif($item->payment_status === 'Partial')<span class="badge-warning">Partial</span>@else<span class="badge-danger">Unpaid</span>@endif<div class="mt-2 text-xs text-gray-500">{{ $item->payment_method }}</div></td>
                            <td>{{ $formatCurrency($item->paid_amount) }}</td>
                            <td>{{ $formatCurrency(max($item->total_amount - $item->paid_amount, 0)) }}</td>
                            <td>{{ $item->note ?: '-' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('distribution.invoice', $item) }}" class="rounded-xl bg-green-600 px-3 py-2 font-semibold text-white">Invoice</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="py-6 text-gray-500">No distribution data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $distributions->links() }}</div>
    </div>
</div>

<template id="distributionItemTemplate">
    <div class="distribution-item-row">
        <label><span>Approved Product</span><select name="items[__INDEX__][product_id]" class="native-select distribution-product" required><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}" data-price="{{ round((float) $product->selling_price) }}">{{ $product->sku }} - {{ $product->product_name }} (Stock {{ $formatNumber($product->stock) }})</option>@endforeach</select></label>
        <label><span>Quantity</span><input type="number" name="items[__INDEX__][qty_out]" class="distribution-qty" min="1" step="1" value="1" required></label>
        <label><span>Price / Unit</span><input type="text" inputmode="numeric" name="items[__INDEX__][price]" class="distribution-price currency-input" value="0" required></label>
        <button type="button" class="distribution-remove" title="Remove product">Remove</button>
    </div>
</template>

<script>
const distributionItems = document.getElementById('distributionItems');
const itemTemplate = document.getElementById('distributionItemTemplate');
const addItemButton = document.getElementById('addDistributionItem');
const paymentStatus = document.getElementById('createPaymentStatus');
const paidAmount = document.getElementById('createPaidAmount');
let itemIndex = {{ count($formItems) }};

function formatRupiah(value) {
    return 'Rp' + Math.round(Number(value) || 0).toLocaleString('id-ID');
}

function parseCurrency(value) {
    return Number(String(value).replace(/\D/g, '')) || 0;
}

function formatCurrencyInput(input) {
    input.value = parseCurrency(input.value).toLocaleString('id-ID');
}

function syncPaidAmount(total) {
    if (paymentStatus.value === 'Paid') paidAmount.value = Math.round(total).toLocaleString('id-ID');
    if (paymentStatus.value === 'Unpaid') paidAmount.value = '0';
}

function updateDistributionTotals() {
    let subtotal = 0;
    distributionItems.querySelectorAll('.distribution-item-row').forEach(row => {
        subtotal += (Number(row.querySelector('.distribution-qty').value) || 0) * parseCurrency(row.querySelector('.distribution-price').value);
    });
    const total = subtotal + parseCurrency(document.getElementById('shippingCost').value) + parseCurrency(document.getElementById('distributionTax').value);
    document.getElementById('previewSubtotal').textContent = formatRupiah(subtotal);
    document.getElementById('previewTotal').textContent = formatRupiah(total);
    syncPaidAmount(total);
}

function updateRemoveButtons() {
    const buttons = distributionItems.querySelectorAll('.distribution-remove');
    buttons.forEach(button => button.disabled = buttons.length === 1);
}

function updatePaymentInput() {
    const status = paymentStatus.value;
    paidAmount.readOnly = status !== 'Partial';
    document.getElementById('createPaymentHint').textContent = status === 'Paid' ? 'Paid amount automatically matches the total.' : status === 'Unpaid' ? 'Paid amount automatically becomes Rp0.' : 'Enter an amount greater than Rp0 and lower than the total.';
    updateDistributionTotals();
}

addItemButton.addEventListener('click', () => {
    distributionItems.insertAdjacentHTML('beforeend', itemTemplate.innerHTML.replaceAll('__INDEX__', itemIndex++));
    updateRemoveButtons();
    updateDistributionTotals();
});

distributionItems.addEventListener('click', event => {
    if (!event.target.classList.contains('distribution-remove')) return;
    event.target.closest('.distribution-item-row').remove();
    updateRemoveButtons();
    updateDistributionTotals();
});

distributionItems.addEventListener('change', event => {
    if (event.target.classList.contains('distribution-product')) {
        const row = event.target.closest('.distribution-item-row');
        const priceInput = row.querySelector('.distribution-price');
        const selectedPrice = event.target.selectedOptions[0]?.dataset.price;
        if (parseCurrency(priceInput.value) === 0 && selectedPrice) {
            priceInput.value = selectedPrice;
            formatCurrencyInput(priceInput);
        }
    }
    updateDistributionTotals();
});

document.getElementById('distributionForm').addEventListener('input', event => {
    if (event.target.classList.contains('currency-input')) formatCurrencyInput(event.target);
    updateDistributionTotals();
});
document.getElementById('distributionForm').addEventListener('submit', () => {
    document.querySelectorAll('#distributionForm .currency-input').forEach(input => input.value = parseCurrency(input.value));
});
paymentStatus.addEventListener('change', updatePaymentInput);
document.querySelectorAll('#distributionForm .currency-input').forEach(formatCurrencyInput);
updateRemoveButtons();
updatePaymentInput();
updateDistributionTotals();
</script>
@endsection
