<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class MonthlySalesChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Sales Trend';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $monthlyTotals = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyTotals[] = Sale::whereYear('sale_date', now()->year)
                ->whereMonth('sale_date', $month)
                ->sum('total_amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sales (₹)',
                    'data' => $monthlyTotals,
                    'borderColor' => '#1D9E75',
                    'backgroundColor' => 'rgba(29, 158, 117, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
