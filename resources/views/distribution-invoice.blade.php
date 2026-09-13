<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $distribution->invoice_number }}</title>
    <style>
        @page{margin:28px 30px}
        *{box-sizing:border-box}
        body{font-family:Arial,sans-serif;color:#111827;font-size:11px;margin:0}
        .header{display:table;width:100%;margin-bottom:18px}
        .header-logo,.header-info{display:table-cell;vertical-align:top}
        .header-logo{width:68px}.header-logo img{width:54px;max-height:54px;object-fit:contain}
        .company-name{font-size:22px;font-weight:700;margin-bottom:4px}.company-detail{font-size:11px;line-height:1.35}
        h1{text-align:center;font-size:21px;margin:18px 0 22px;letter-spacing:.4px}
        .metadata{display:table;width:100%;margin-bottom:18px}.meta-column{display:table-cell;width:50%;vertical-align:top;padding-right:20px}
        .meta-row{display:table;width:100%;margin-bottom:5px}.meta-label,.meta-value{display:table-cell;vertical-align:top}.meta-label{width:112px}.meta-value{font-weight:700}
        table.items{width:100%;border-collapse:collapse;margin-top:10px}table.items th{background:#35b88f;color:#fff;text-align:left;padding:9px;border:1px solid #2da780}table.items td{padding:8px;border:1px solid #d1d5db}.right{text-align:right}.center{text-align:center}
        .summary-area{display:table;width:100%;margin-top:24px}.words,.totals{display:table-cell;width:50%;vertical-align:top}.words{padding-right:28px}.section-title{font-weight:700;font-size:12px;margin-bottom:6px}.amount-words{font-size:12px;line-height:1.5;margin-bottom:18px;text-transform:capitalize}
        .bank-row{margin:4px 0}.bank-label{display:inline-block;width:105px}.totals{padding-left:18px}.total-row{display:table;width:100%;padding:5px 0;border-bottom:1px solid #e5e7eb}.total-label,.total-value{display:table-cell}.total-value{text-align:right;font-weight:700}.grand{font-size:16px;font-weight:700;border-top:2px solid #9ca3af;border-bottom:2px solid #9ca3af;margin-top:8px;padding:10px 0}.grand .total-label{width:62%}
        .note{margin-top:18px;color:#4b5563;font-style:italic}
    </style>
</head>
<body>
    @php
        $currency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $company = config('app.company');
    @endphp

    <div class="header">
        <div class="header-logo">@if($logoData)<img src="{{ $logoData }}" alt="Kantong Jamu">@endif</div>
        <div class="header-info">
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-detail">{{ $company['phone'] }} &bull; {{ $company['address'] }}</div>
        </div>
    </div>

    <h1>DETAIL PENJUALAN</h1>

    <div class="metadata">
        <div class="meta-column">
            <div class="meta-row"><div class="meta-label">Pihak</div><div class="meta-value">{{ $distribution->destination }}</div></div>
            <div class="meta-row"><div class="meta-label">No. Telepon</div><div class="meta-value">{{ $distribution->client_phone ?: '-' }}</div></div>
        </div>
        <div class="meta-column">
            <div class="meta-row"><div class="meta-label">No. Faktur</div><div class="meta-value">{{ $distribution->invoice_number }}</div></div>
            <div class="meta-row"><div class="meta-label">Tanggal Faktur</div><div class="meta-value">{{ \Carbon\Carbon::parse($distribution->distribution_date)->format('d M Y') }}</div></div>
            <div class="meta-row"><div class="meta-label">Metode Pembayaran</div><div class="meta-value">{{ $distribution->payment_method }} / {{ $distribution->payment_status }}</div></div>
        </div>
    </div>

    <table class="items">
        <thead><tr><th style="width:42px">No.</th><th>Nama</th><th style="width:95px">Jumlah</th><th style="width:105px">Tarif</th><th style="width:115px">Subtotal</th></tr></thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr><td class="center">{{ $index + 1 }}</td><td>{{ $item->product_name }}</td><td class="right">{{ number_format($item->qty_out, 0, ',', '.') }} {{ $item->unit }}</td><td class="right">{{ $currency($item->price) }}</td><td class="right">{{ $currency($item->subtotal) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-area">
        <div class="words">
            <div class="section-title">Terbilang</div><div class="amount-words">{{ $amountInWords }}</div>
            <div class="section-title">Bank Rincian</div>
            <div class="bank-row"><span class="bank-label">Nama Bank</span>: {{ $company['bank_name'] }}</div>
            <div class="bank-row"><span class="bank-label">Atas Nama</span>: {{ $company['bank_account_name'] }}</div>
            <div class="bank-row"><span class="bank-label">No. Akun</span>: {{ $company['bank_account_number'] }}</div>
        </div>
        <div class="totals">
            <div class="total-row"><div class="total-label">Subtotal</div><div class="total-value">{{ $currency($subtotal) }}</div></div>
            <div class="total-row"><div class="total-label">Ongkir</div><div class="total-value">{{ $currency($distribution->shipping_cost) }}</div></div>
            <div class="total-row"><div class="total-label">Pajak</div><div class="total-value">{{ $currency($distribution->tax) }}</div></div>
            <div class="total-row"><div class="total-label">Total</div><div class="total-value">{{ $currency($distribution->total_amount) }}</div></div>
            <div class="total-row"><div class="total-label">Jumlah Diterima</div><div class="total-value">{{ $currency($distribution->paid_amount) }}</div></div>
            <div class="total-row grand"><div class="total-label">Jumlah yang Harus Dibayar</div><div class="total-value">{{ $currency($outstanding) }}</div></div>
        </div>
    </div>

    @if($distribution->note)<div class="note">Catatan: {{ $distribution->note }}</div>@endif
</body>
</html>
