<?php

namespace App\Exports;

use App\Reports\StockQuery;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $productId = null,
        protected ?int $warehouseId = null,
        protected ?string $fromDate = null,
        protected ?string $toDate = null,
    ) {}

    public function collection(): Collection
    {
        return StockQuery::applyFilters(
            StockQuery::base(),
            productId: $this->productId,
            warehouseId: $this->warehouseId,
            fromDate: $this->fromDate,
            toDate: $this->toDate,
        )
            ->get();
    }

    public function headings(): array
    {
        return ['Product', 'SKU', 'Warehouse', 'Quantity', 'Last Updated'];
    }

    public function map($stock): array
    {
        return [
            $stock->product?->name ?? 'Unknown',
            $stock->product?->sku ?? '—',
            $stock->warehouse?->name ?? 'Unknown',
            $stock->quantity,
            $stock->updated_at->format('d M Y, h:i A'),
        ];
    }
}
