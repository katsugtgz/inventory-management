<?php

namespace App\Filament\Pages;

use App\Exports\PurchaseReportExport;
use App\Models\Supplier;
use App\Reports\PurchaseQuery;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class PurchaseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Purchase Report';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.purchase-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseQuery::base()
            )
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Invoice No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->label('Purchase Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('due_amount')
                    ->label('Due')
                    ->money('INR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                    }),
            ])
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::pluck('name', 'id'))
                    ->placeholder('All Suppliers')
                    ->query(fn (Builder $query, array $data): Builder => PurchaseQuery::applyFilters(
                        $query,
                        supplierId: $data['value'] ?? null,
                    )),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => PurchaseQuery::applyFilters(
                        $query,
                        status: $data['value'] ?? null,
                    )),
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from_date')
                            ->label('From Date'),
                        DatePicker::make('to_date')
                            ->label('To Date'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => PurchaseQuery::applyFilters(
                        $query,
                        fromDate: $data['from_date'] ?? null,
                        toDate: $data['to_date'] ?? null,
                    )),
            ])
            ->defaultSort('purchase_date', 'desc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    $filters = $this->getTableFiltersForm()->getState();

                    return Excel::download(
                        new PurchaseReportExport(
                            supplierId: $filters['supplier_id']['value'] ?? null,
                            status: $filters['status']['value'] ?? null,
                            fromDate: $filters['date_range']['from_date'] ?? null,
                            toDate: $filters['date_range']['to_date'] ?? null,
                        ),
                        'purchase-report-'.now()->format('Y-m-d').'.xlsx'
                    );
                }),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $purchases = $this->getFilteredTableQuery()->get();
                    $pdf = Pdf::loadView('pdf.purchase-report', ['purchases' => $purchases]);

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        'purchase-report-'.now()->format('Y-m-d').'.pdf'
                    );
                }),
        ];
    }
}
