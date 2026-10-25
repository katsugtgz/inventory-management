<?php

namespace App\Exports;

use App\Reports\PurchaseQuery;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchaseReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $supplierId = null,
        protected ?string $status = null,
        protected ?string $fromDate = null,
        protected ?string $toDate = null,
    ) {}

    public function collection(): Collection
    {
        return PurchaseQuery::applyFilters(
            PurchaseQuery::base(),
            supplierId: $this->supplierId,
            status: $this->status,
            fromDate: $this->fromDate,
            toDate: $this->toDate,
        )
            ->get();
    }

    public function headings(): array
    {
        return ['Invoice No', 'Supplier', 'Warehouse', 'Purchase Date', 'Total Amount', 'Paid Amount', 'Due Amount', 'Status'];
    }

    public function map($purchase): array
    {
        return [
            $purchase->invoice_no,
            $purchase->supplier?->name ?? 'Unknown',
            $purchase->warehouse?->name ?? 'Unknown',
            $purchase->purchase_date->format('d M Y'),
            number_format($purchase->total_amount, 2),
            number_format($purchase->paid_amount, 2),
            number_format($purchase->due_amount, 2),
            ucfirst($purchase->status),
        ];
    }
}
