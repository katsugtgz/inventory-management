<?php

namespace App\Reports;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;

class StockQuery
{
    public static function base(): Builder
    {
        return Stock::query()->with(['product', 'warehouse']);
    }

    public static function applyFilters(
        Builder $query,
        ?int $productId = null,
        ?int $warehouseId = null,
        ?string $fromDate = null,
        ?string $toDate = null,
    ): Builder {
        return $query
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($fromDate, fn ($q) => $q->whereDate('updated_at', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('updated_at', '<=', $toDate));
    }
}
