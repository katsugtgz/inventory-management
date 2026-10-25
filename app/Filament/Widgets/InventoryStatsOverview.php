<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Stock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InventoryStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalSales = Sale::sum('total_amount');
        $totalPurchases = Purchase::sum('total_amount');
        $totalCustomers = Customer::count();
        $stockValue = Stock::query()
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->sum(DB::raw('stocks.quantity * products.cost_price'));

        return [
            Stat::make('Total Sales', '₹'.number_format($totalSales, 2))
                ->description('Revenue from all sales')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Purchases', '₹'.number_format($totalPurchases, 2))
                ->description('Spent on all purchases')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),
            Stat::make('Stock Value', '₹'.number_format($stockValue, 2))
                ->description('Current inventory worth')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
            Stat::make('Total Customers', $totalCustomers)
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
        ];
    }
}
