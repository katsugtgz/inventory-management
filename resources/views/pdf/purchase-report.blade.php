<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Report</title>
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
    <h1>Purchase Report</h1>
    <p class="meta">Generated on {{ now()->format('d M Y, h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Invoice No</th>
                <th>Supplier</th>
                <th>Warehouse</th>
                <th>Purchase Date</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->invoice_no }}</td>
                    <td>{{ $purchase->supplier?->name ?? 'Unknown' }}</td>
                    <td>{{ $purchase->warehouse?->name ?? 'Unknown' }}</td>
                    <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                    <td>₹{{ number_format($purchase->total_amount, 2) }}</td>
                    <td>₹{{ number_format($purchase->paid_amount, 2) }}</td>
                    <td>₹{{ number_format($purchase->due_amount, 2) }}</td>
                    <td>{{ ucfirst($purchase->status) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4">Grand Total</td>
                <td>₹{{ number_format($purchases->sum('total_amount'), 2) }}</td>
                <td>₹{{ number_format($purchases->sum('paid_amount'), 2) }}</td>
                <td>₹{{ number_format($purchases->sum('due_amount'), 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
