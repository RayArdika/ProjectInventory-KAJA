@extends('layouts.app')

@section('title', 'Edit Distribution')

@section('content')
@php
    $formatNumber = fn ($value) => rtrim(rtrim(number_format((float) $value, 3, ',', '.'), '0'), ',');
    $formatCurrencyInput = fn ($value) => number_format((float) $value, 0, ',', '.');
    $formItems = old('items', $items->map(fn ($item) => [
        'product_id' => $item->product_id,
        'qty_out' => $item->qty_out,
        'price' => (float) $item->price,
    ])->values()->all());
@endphp

<style>
    .distribution-items{display:grid;gap:12px}
    .distribution-item-row{display:grid;grid-template-columns:minmax(240px,2fr) minmax(120px,.7fr) minmax(150px,1fr) auto;gap:12px;align-items:end;border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc}
    .distribution-item-row label span{display:block;margin-bottom:7px;font-size:13px;font-weight:700;color:#334155}
    .distribution-item-row input,.distribution-item-row select{width:100%;min-height:48px;border:0;border-radius:12px;background:#fff;padding:11px 13px}
    .distribution-remove{min-height:48px;border:0;border-radius:12px;background:#ef4444;color:#fff;padding:0 16px;font-weight:700}
    .distribution-add{min-height:46px;border:0;border-radius:12px;background:#2563eb;color:#fff;padding:0 18px;font-weight:700}
    .distribution-native-select{width:100%;min-height:58px;border:0;border-radius:18px;background:#f5f7fb;padding:16px}
    @media(max-width:760px){.distribution-item-row{grid-template-columns:minmax(0,1fr)}.distribution-remove{width:100%}}
</style>

<div class="mx-auto max-w-6xl">
    <div class="card">
        <div class="mb-7">
            <h2 class="text-3xl font-bold">Edit Distribution</h2>
            <p class="mt-2 text-gray-500">{{ $distribution->invoice_number }}. Stock and dashboard totals adjust automatically.</p>
        </div>

        @if($distribution->payment_status === 'Paid')
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-800">This transaction is already paid. Review all products and payment information carefully.</div>
        @endif
        <form action="/distribution/{{ $distribution->id }}" method="POST" class="space-y-5" id="editDistributionForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label><span class="mb-2 block font-semibold">Date</span><input type="date" name="distribution_date" value="{{ old('distribution_date', $distribution->distribution_date) }}" class="w-full" required></label>
                <label><span class="mb-2 block font-semibold">Client</span><input type="text" name="destination" value="{{ old('destination', $distribution->destination) }}" class="w-full" required></label>
                <label><span class="mb-2 block font-semibold">Client Phone</span><input type="tel" name="client_phone" value="{{ old('client_phone', $distribution->client_phone) }}" placeholder="10-13 digit phone number" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13" class="w-full" required></label>
                <label>
                    <span class="mb-2 block font-semibold">Payment Method</span>
                    <select name="payment_method" class="native-select distribution-native-select" required>
                        @foreach(['Transfer', 'Cash', 'Credit', 'Other'] as $method)<option value="{{ $method }}" @selected(old('payment_method', $distribution->payment_method ?: 'Transfer') === $method)>{{ $method }}</option>@endforeach
                    </select>
                </label>
            </div>

            <div>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <div><h3 class="text-lg font-bold">Products</h3><p class="text-sm text-gray-500">Available stock includes the quantity currently assigned to this transaction.</p></div>
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
                                        @php $availableStock = $product->stock + $items->where('product_id', $product->id)->sum('qty_out'); @endphp
                                        <option value="{{ $product->id }}" data-price="{{ round((float) $product->selling_price) }}" @selected((string) ($formItem['product_id'] ?? '') === (string) $product->id)>
                                            {{ $product->sku }} - {{ $product->product_name }} (Available {{ $formatNumber($availableStock) }})
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label><span>Quantity</span><input type="number" name="items[{{ $index }}][qty_out]" class="distribution-qty" min="1" step="1" value="{{ $formItem['qty_out'] ?? 1 }}" required></label>
                            <label><span>Price / Unit</span><input type="text" inputmode="numeric" name="items[{{ $index }}][price]" class="distribution-price currency-input" value="{{ $formatCurrencyInput($formItem['price'] ?? 0) }}" required></label>
                            <button type="button" class="distribution-remove">Remove</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label><span class="mb-2 block font-semibold">Shipping Cost</span><input type="text" inputmode="numeric" name="shipping_cost" value="{{ $formatCurrencyInput(old('shipping_cost', (float) $distribution->shipping_cost)) }}" class="w-full currency-input"></label>
                <label><span class="mb-2 block font-semibold">Tax</span><input type="text" inputmode="numeric" name="tax" value="{{ $formatCurrencyInput(old('tax', (float) $distribution->tax)) }}" class="w-full currency-input"></label>
                <label>
                    <span class="mb-2 block font-semibold">Payment Status</span>
                    <select id="paymentStatus" name="payment_status" class="native-select distribution-native-select w-full" required>@foreach(['Unpaid', 'Partial', 'Paid'] as $status)<option value="{{ $status }}" @selected(old('payment_status', $distribution->payment_status) === $status)>{{ $status }}</option>@endforeach</select>
                </label>
                <label><span class="mb-2 block font-semibold">Paid Amount</span><input id="paidAmount" type="text" inputmode="numeric" name="paid_amount" value="{{ $formatCurrencyInput(old('paid_amount', (float) $distribution->paid_amount)) }}" class="w-full currency-input"><span id="paymentHint" class="mt-2 block text-sm text-gray-500"></span></label>
                <label class="md:col-span-2 xl:col-span-4"><span class="mb-2 block font-semibold">Note</span><input type="text" name="note" value="{{ old('note', $distribution->note) }}" class="w-full"></label>
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="btn-green">Update Distribution</button>
                <a href="/distribution" class="rounded-2xl bg-gray-100 px-6 py-4 font-semibold text-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>

<template id="distributionItemTemplate">
    <div class="distribution-item-row">
        <label><span>Approved Product</span><select name="items[__INDEX__][product_id]" class="native-select distribution-product" required><option value="">Select product</option>@foreach($products as $product)@php $availableStock = $product->stock + $items->where('product_id', $product->id)->sum('qty_out'); @endphp<option value="{{ $product->id }}" data-price="{{ round((float) $product->selling_price) }}">{{ $product->sku }} - {{ $product->product_name }} (Available {{ $formatNumber($availableStock) }})</option>@endforeach</select></label>
        <label><span>Quantity</span><input type="number" name="items[__INDEX__][qty_out]" class="distribution-qty" min="1" step="1" value="1" required></label>
        <label><span>Price / Unit</span><input type="text" inputmode="numeric" name="items[__INDEX__][price]" class="distribution-price currency-input" value="0" required></label>
        <button type="button" class="distribution-remove">Remove</button>
    </div>
</template>

<script>
const distributionItems = document.getElementById('distributionItems');
const itemTemplate = document.getElementById('distributionItemTemplate');
const paymentStatus = document.getElementById('paymentStatus');
const paidAmount = document.getElementById('paidAmount');
let itemIndex = {{ count($formItems) }};

function parseCurrency(value) {
    return Number(String(value).replace(/\D/g, '')) || 0;
}

function formatCurrencyInput(input) {
    input.value = parseCurrency(input.value).toLocaleString('id-ID');
}

function distributionTotal() {
    let subtotal = 0;
    distributionItems.querySelectorAll('.distribution-item-row').forEach(row => {
        subtotal += (Number(row.querySelector('.distribution-qty').value) || 0)
            * parseCurrency(row.querySelector('.distribution-price').value);
    });

    return subtotal
        + parseCurrency(document.querySelector('[name="shipping_cost"]').value)
        + parseCurrency(document.querySelector('[name="tax"]').value);
}

function syncPaidAmount() {
    if (paymentStatus.value === 'Paid') paidAmount.value = Math.round(distributionTotal()).toLocaleString('id-ID');
    if (paymentStatus.value === 'Unpaid') paidAmount.value = '0';
}

function updateRemoveButtons() {
    const buttons = distributionItems.querySelectorAll('.distribution-remove');
    buttons.forEach(button => button.disabled = buttons.length === 1);
}

function updatePaymentInput() {
    const status = paymentStatus.value;
    paidAmount.readOnly = status !== 'Partial';
    document.getElementById('paymentHint').textContent = status === 'Paid' ? 'Paid amount automatically matches the total.' : status === 'Unpaid' ? 'Paid amount automatically becomes Rp0.' : 'Enter an amount greater than Rp0 and lower than the total.';
    syncPaidAmount();
}

document.getElementById('addDistributionItem').addEventListener('click', () => {
    distributionItems.insertAdjacentHTML('beforeend', itemTemplate.innerHTML.replaceAll('__INDEX__', itemIndex++));
    updateRemoveButtons();
    syncPaidAmount();
});

distributionItems.addEventListener('click', event => {
    if (!event.target.classList.contains('distribution-remove')) return;
    event.target.closest('.distribution-item-row').remove();
    updateRemoveButtons();
    syncPaidAmount();
});

distributionItems.addEventListener('change', event => {
    if (!event.target.classList.contains('distribution-product')) return;
    const priceInput = event.target.closest('.distribution-item-row').querySelector('.distribution-price');
    const selectedPrice = event.target.selectedOptions[0]?.dataset.price;
    if (parseCurrency(priceInput.value) === 0 && selectedPrice) {
        priceInput.value = selectedPrice;
        formatCurrencyInput(priceInput);
    }
    syncPaidAmount();
});

document.getElementById('editDistributionForm').addEventListener('input', event => {
    if (event.target.classList.contains('currency-input')) formatCurrencyInput(event.target);
    syncPaidAmount();
});
document.getElementById('editDistributionForm').addEventListener('submit', () => {
    document.querySelectorAll('#editDistributionForm .currency-input').forEach(input => input.value = parseCurrency(input.value));
});
paymentStatus.addEventListener('change', updatePaymentInput);
document.querySelectorAll('#editDistributionForm .currency-input').forEach(formatCurrencyInput);
updateRemoveButtons();
updatePaymentInput();
</script>
@endsection
