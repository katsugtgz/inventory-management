<?php

namespace App\Exports;

use App\Reports\SaleQuery;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SaleReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $customerId = null,
        protected ?string $status = null,
        protected ?string $fromDate = null,
        protected ?string $toDate = null,
    ) {}

    public function collection(): Collection
    {
        return SaleQuery::applyFilters(
            SaleQuery::base(),
            customerId: $this->customerId,
            status: $this->status,
            fromDate: $this->fromDate,
            toDate: $this->toDate,
        )
            ->get();
    }

    public function headings(): array
    {
        return ['Invoice No', 'Customer', 'Warehouse', 'Sale Date', 'Total Amount', 'Paid Amount', 'Due Amount', 'Status'];
    }

    public function map($sale): array
    {
        return [
            $sale->invoice_no,
            $sale->customer?->name ?? 'Unknown',
            $sale->warehouse?->name ?? 'Unknown',
            $sale->sale_date->format('d M Y'),
            number_format($sale->total_amount, 2),
            number_format($sale->paid_amount, 2),
            number_format($sale->due_amount, 2),
            ucfirst($sale->status),
        ];
    }
}
