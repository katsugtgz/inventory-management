<?php

namespace App\Exports;

use App\Reports\ProfitLossQuery;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProfitLossReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $categoryId = null,
        protected ?string $fromDate = null,
        protected ?string $toDate = null,
    ) {}

    public function collection(): Collection
    {
        return ProfitLossQuery::applyFilters(
            ProfitLossQuery::base(),
            $this->categoryId,
            $this->fromDate,
            $this->toDate,
        )
            ->orderByRaw('profit desc')
            ->get();
    }

    public function headings(): array
    {
        return ['Product', 'SKU', 'Units Sold', 'Revenue', 'Cost', 'Profit', 'Margin %'];
    }

    public function map($row): array
    {
        $margin = $row->revenue > 0 ? round(($row->profit / $row->revenue) * 100, 1) : 0;

        return [
            $row->product_name,
            $row->product_sku,
            $row->units_sold,
            number_format($row->revenue, 2),
            number_format($row->cost, 2),
            number_format($row->profit, 2),
            $margin.'%',
        ];
    }
}
