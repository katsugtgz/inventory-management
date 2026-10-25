<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit &amp; Loss Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.meta { color: #666; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4f6f5; font-weight: bold; }
        tr:nth-child(even) { background: #fafafa; }
        .total-row td { font-weight: bold; background: #eef7f2; }
        .loss { color: #c0392b; }
        .gain { color: #1d7a5a; }
    </style>
</head>
<body>
    <h1>Profit &amp; Loss Report</h1>
    <p class="meta">Generated on {{ now()->format('d M Y, h:i A') }}</p>
    @php
        $totalRevenue = $rows->sum('revenue');
        $totalCost = $rows->sum('cost');
        $totalProfit = $rows->sum('profit');
        $overallMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Units Sold</th>
                <th>Revenue</th>
                <th>Cost</th>
                <th>Profit</th>
                <th>Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php
                    $margin = $row->revenue > 0 ? round(($row->profit / $row->revenue) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ $row->product_name }}</td>
                    <td>{{ $row->product_sku }}</td>
                    <td>{{ $row->units_sold }}</td>
                    <td>₹{{ number_format($row->revenue, 2) }}</td>
                    <td>₹{{ number_format($row->cost, 2) }}</td>
                    <td class="{{ $row->profit >= 0 ? 'gain' : 'loss' }}">₹{{ number_format($row->profit, 2) }}</td>
                    <td>{{ $margin }}%</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">Grand Total</td>
                <td>{{ $rows->sum('units_sold') }}</td>
                <td>₹{{ number_format($totalRevenue, 2) }}</td>
                <td>₹{{ number_format($totalCost, 2) }}</td>
                <td>₹{{ number_format($totalProfit, 2) }}</td>
                <td>{{ $overallMargin }}%</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
