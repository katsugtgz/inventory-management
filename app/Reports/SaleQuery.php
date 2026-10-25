<?php

namespace App\Reports;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;

class SaleQuery
{
    public static function base(): Builder
    {
        return Sale::query()->with(['customer', 'warehouse']);
    }

    public static function applyFilters(
        Builder $query,
        ?int $customerId = null,
        ?string $status = null,
        ?string $fromDate = null,
        ?string $toDate = null,
    ): Builder {
        return $query
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($fromDate, fn ($q) => $q->whereDate('sale_date', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('sale_date', '<=', $toDate));
    }
}
