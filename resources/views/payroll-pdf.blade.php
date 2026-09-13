<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Kantong Jamu Payroll Report</title>
    <style>
        @page{margin:26px 30px}*{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#111827;font-size:10px;margin:0}
        .header{display:table;width:100%;margin-bottom:14px}.logo,.company{display:table-cell;vertical-align:middle}.logo{width:72px}.logo img{width:58px;max-height:58px;object-fit:contain}
        .company-name{font-size:22px;font-weight:700}.company-detail{margin-top:4px;color:#4b5563;line-height:1.4}h1{text-align:center;font-size:20px;margin:18px 0 16px}
        .filters{background:#f3f4f6;border-left:4px solid #20a765;padding:10px 12px;margin-bottom:16px}.filters span{display:inline-block;margin-right:24px;line-height:1.7}.filters b{color:#166534}
        table{width:100%;border-collapse:collapse}th{background:#20a765;color:#fff;text-align:left;padding:8px;border:1px solid #198754}td{padding:7px 8px;border:1px solid #d1d5db}tbody tr:nth-child(even){background:#f9fafb}.right{text-align:right}.center{text-align:center}
        .summary{display:table;width:100%;margin-top:16px}.summary-note,.summary-total{display:table-cell;vertical-align:top}.summary-note{color:#6b7280}.summary-total{width:290px;background:#ecfdf5;border:1px solid #a7f3d0;padding:12px}.total-label,.total-value{display:inline-block;font-size:14px;font-weight:700}.total-label{width:125px}.total-value{width:125px;text-align:right;color:#15803d}
        .footer{margin-top:18px;text-align:right;color:#6b7280;font-size:9px}
    </style>
</head>
<body>
    @php
        $currency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y') : 'All dates';
        $company = config('app.company');
    @endphp

    <div class="header">
        <div class="logo">@if($logoData)<img src="{{ $logoData }}" alt="Kantong Jamu">@endif</div>
        <div class="company"><div class="company-name">{{ $company['name'] }}</div><div class="company-detail">{{ $company['phone'] }} &bull; {{ $company['address'] }}</div></div>
    </div>

    <h1>PAYROLL REPORT</h1>

    <div class="filters">
        <span><b>Period:</b> {{ $date($filters['date_from'] ?? null) }} - {{ $date($filters['date_to'] ?? null) }}</span>
        <span><b>Worker:</b> {{ $filters['worker'] ?? 'All workers' }}</span>
        <span><b>Activity:</b> {{ $filters['activity'] ?? 'All activities' }}</span>
    </div>

    <table>
        <thead><tr><th style="width:34px">No.</th><th style="width:82px">Date</th><th>Worker</th><th>Activity</th><th>Product</th><th style="width:58px">Quantity</th><th style="width:48px">Unit</th><th style="width:90px">Fee / Unit</th><th style="width:105px">Total</th></tr></thead>
        <tbody>
            @forelse($payrolls as $index => $payroll)
                <tr><td class="center">{{ $index + 1 }}</td><td>{{ $payroll->production?->production_date ? \Carbon\Carbon::parse($payroll->production->production_date)->format('d M Y') : '-' }}</td><td>{{ $payroll->worker_name }}</td><td>{{ $payroll->activity_name ?: '-' }}</td><td>{{ $payroll->product_name }}</td><td class="right">{{ number_format($payroll->qty, 0, ',', '.') }}</td><td class="center">{{ $payroll->unit ?: 'pcs' }}</td><td class="right">{{ $currency($payroll->fee) }}</td><td class="right"><b>{{ $currency($payroll->total_salary) }}</b></td></tr>
            @empty
                <tr><td colspan="9" class="center">No payroll data was found for the selected filter.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary"><div class="summary-note">Generated from the Kantong Jamu Internal Production Management System.</div><div class="summary-total"><span class="total-label">Grand Total</span><span class="total-value">{{ $currency($grandTotal) }}</span></div></div>
    <div class="footer">Generated at {{ now()->format('d M Y H:i') }} WIB</div>
</body>
</html>
