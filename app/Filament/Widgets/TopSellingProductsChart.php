<?php

namespace App\Filament\Widgets;

use App\Models\SaleItem;
use Filament\Widgets\ChartWidget;

class TopSellingProductsChart extends ChartWidget
{
    protected ?string $heading = 'Top 5 Selling Products';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $topProducts = SaleItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('product')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Units Sold',
                    'data' => $topProducts->pluck('total_quantity')->toArray(),
                    'backgroundColor' => [
                        '#1D9E75',
                        '#FAC775',
                        '#85B7EB',
                        '#ED93B1',
                        '#9C9A92',
                    ],
                ],
            ],
            'labels' => $topProducts->map(fn ($item) => $item->product?->name ?? 'Unknown')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
