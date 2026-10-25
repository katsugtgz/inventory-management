<?php

namespace App\Reports;

use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;

class ProfitLossQuery
{
    public static function base(): Builder
    {
        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->selectRaw('
                MIN(sale_items.id) as id,
                products.id as product_id,
                products.name as product_name,
                products.sku as product_sku,
                SUM(sale_items.quantity) as units_sold,
                SUM(sale_items.subtotal) as revenue,
                SUM(sale_items.quantity * products.cost_price) as cost,
                SUM(sale_items.subtotal) - SUM(sale_items.quantity * products.cost_price) as profit
            ')
            ->groupBy('products.id', 'products.name', 'products.sku');
    }

    public static function applyFilters(
        Builder $query,
        ?int $categoryId = null,
        ?string $fromDate = null,
        ?string $toDate = null,
    ): Builder {
        return $query
            ->when($categoryId, fn ($q) => $q->where('products.category_id', $categoryId))
            ->when($fromDate, fn ($q) => $q->whereDate('sales.sale_date', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('sales.sale_date', '<=', $toDate));
    }
}
