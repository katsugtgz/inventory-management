<?php

namespace App\Reports;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Builder;

class PurchaseQuery
{
    public static function base(): Builder
    {
        return Purchase::query()->with(['supplier', 'warehouse']);
    }

    public static function applyFilters(
        Builder $query,
        ?int $supplierId = null,
        ?string $status = null,
        ?string $fromDate = null,
        ?string $toDate = null,
    ): Builder {
        return $query
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($fromDate, fn ($q) => $q->whereDate('purchase_date', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('purchase_date', '<=', $toDate));
    }
}
