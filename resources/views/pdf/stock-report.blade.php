<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.meta { color: #666; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4f6f5; font-weight: bold; }
        tr:nth-child(even) { background: #fafafa; }
    </style>
</head>
<body>
    <h1>Stock Report</h1>
    <p class="meta">Generated on {{ now()->format('d M Y, h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Warehouse</th>
                <th>Quantity</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stocks as $stock)
                <tr>
                    <td>{{ $stock->product?->name ?? 'Unknown' }}</td>
                    <td>{{ $stock->product?->sku ?? '—' }}</td>
                    <td>{{ $stock->warehouse?->name ?? 'Unknown' }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>{{ $stock->updated_at->format('d M Y, h:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
