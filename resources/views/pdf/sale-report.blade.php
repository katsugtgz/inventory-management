<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.meta { color: #666; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4f6f5; font-weight: bold; }
        tr:nth-child(even) { background: #fafafa; }
        .total-row td { font-weight: bold; background: #eef7f2; }
    </style>
</head>
<body>
    <h1>Sale Report</h1>
    <p class="meta">Generated on {{ now()->format('d M Y, h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Invoice No</th>
                <th>Customer</th>
                <th>Warehouse</th>
                <th>Sale Date</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->customer?->name ?? 'Unknown' }}</td>
                    <td>{{ $sale->warehouse?->name ?? 'Unknown' }}</td>
                    <td>{{ $sale->sale_date->format('d M Y') }}</td>
                    <td>₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td>₹{{ number_format($sale->paid_amount, 2) }}</td>
                    <td>₹{{ number_format($sale->due_amount, 2) }}</td>
                    <td>{{ ucfirst($sale->status) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4">Grand Total</td>
                <td>₹{{ number_format($sales->sum('total_amount'), 2) }}</td>
                <td>₹{{ number_format($sales->sum('paid_amount'), 2) }}</td>
                <td>₹{{ number_format($sales->sum('due_amount'), 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
