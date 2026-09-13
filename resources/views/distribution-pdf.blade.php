<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Distribution Report</title>
    <style>
        @page { margin: 24px; }
        body {
            color: #17202a;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        h1 {
            color: #16a34a;
            font-size: 22px;
            margin: 0 0 6px;
        }
        .meta {
            color: #566573;
            margin-bottom: 16px;
        }
        .summary {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }
        .summary td {
            background: #f3f6f4;
            border: 1px solid #dfe7e2;
            padding: 9px;
        }
        .summary strong {
            display: block;
            font-size: 13px;
            margin-top: 3px;
        }
        table.report {
            width: 100%;
            border-collapse: collapse;
        }
        .report th,
        .report td {
            border: 1px solid #d5d8dc;
            padding: 6px;
            vertical-align: top;
        }
        .report th {
            background: #16a34a;
            color: #fff;
            font-size: 9px;
        }
        .number {
            text-align: right;
            white-space: nowrap;
        }
        .center {
            text-align: center;
        }
        .empty {
            color: #7f8c8d;
            padding: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $currency = fn ($value) => 'Rp' . number_format((float) $value, 0, ',', '.');
    @endphp

    <h1>Kantong Jamu Distribution Report</h1>
    <div class="meta">
        Client: {{ $filters['client'] ?? 'All Clients' }}
        | Period: {{ $filters['date_from'] ?? 'All' }} to {{ $filters['date_to'] ?? 'All' }}
        | Payment: {{ $filters['payment_status'] ?? 'All Status' }}
    </div>

    <table class="summary">
        <tr>
            <td>Orders<strong>{{ number_format($summary['orders'], 0, ',', '.') }}</strong></td>
            <td>Quantity<strong>{{ number_format($summary['quantity'], 0, ',', '.') }} pcs</strong></td>
            <td>Total Sales<strong>{{ $currency($summary['sales']) }}</strong></td>
            <td>Total Paid<strong>{{ $currency($summary['paid']) }}</strong></td>
            <td>Total Unpaid<strong>{{ $currency($summary['unpaid']) }}</strong></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Client</th>
                <th>Products</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Shipping</th>
                <th>Tax</th>
                <th>Total</th>
                <th>Status</th>
                <th>Paid</th>
                <th>Unpaid</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distributions as $item)
                @php
                    $lines = $item->items->isNotEmpty() ? $item->items : collect([(object) [
                        'product_name' => $item->product_name,
                        'qty_out' => $item->qty_out,
                        'unit' => 'pcs',
                        'price' => $item->price,
                    ]]);
                @endphp
                <tr>
                    <td>{{ $item->invoice_number ?: '#'.$item->id }}</td>
                    <td class="center">{{ $item->distribution_date }}</td>
                    <td>{{ $item->destination }}</td>
                    <td>@foreach($lines as $line)<div>{{ $line->product_name }}</div>@endforeach</td>
                    <td class="number">@foreach($lines as $line)<div>{{ number_format($line->qty_out, 0, ',', '.') }} {{ $line->unit }}</div>@endforeach</td>
                    <td class="number">@foreach($lines as $line)<div>{{ $currency($line->price) }}</div>@endforeach</td>
                    <td class="number">{{ $currency($item->shipping_cost) }}</td>
                    <td class="number">{{ $currency($item->tax) }}</td>
                    <td class="number">{{ $currency($item->total_amount) }}</td>
                    <td class="center">{{ $item->payment_status }}</td>
                    <td class="number">{{ $currency($item->paid_amount) }}</td>
                    <td class="number">{{ $currency(max($item->total_amount - $item->paid_amount, 0)) }}</td>
                    <td>{{ $item->note ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="empty">No distribution data found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
