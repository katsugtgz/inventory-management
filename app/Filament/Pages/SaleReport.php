<?php

namespace App\Filament\Pages;

use App\Exports\SaleReportExport;
use App\Models\Customer;
use App\Reports\SaleQuery;
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

class SaleReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Sale Report';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.sale-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SaleQuery::base()
            )
            ->columns([
                TextColumn::make('invoice_no')
                    ->label('Invoice No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sale_date')
                    ->label('Sale Date')
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
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::pluck('name', 'id'))
                    ->placeholder('All Customers')
                    ->query(fn (Builder $query, array $data): Builder => SaleQuery::applyFilters(
                        $query,
                        customerId: $data['value'] ?? null,
                    )),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => SaleQuery::applyFilters(
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
                    ->query(fn (Builder $query, array $data): Builder => SaleQuery::applyFilters(
                        $query,
                        fromDate: $data['from_date'] ?? null,
                        toDate: $data['to_date'] ?? null,
                    )),
            ])
            ->defaultSort('sale_date', 'desc');
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
                        new SaleReportExport(
                            customerId: $filters['customer_id']['value'] ?? null,
                            status: $filters['status']['value'] ?? null,
                            fromDate: $filters['date_range']['from_date'] ?? null,
                            toDate: $filters['date_range']['to_date'] ?? null,
                        ),
                        'sale-report-'.now()->format('Y-m-d').'.xlsx'
                    );
                }),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $sales = $this->getFilteredTableQuery()->get();
                    $pdf = Pdf::loadView('pdf.sale-report', ['sales' => $sales]);

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        'sale-report-'.now()->format('Y-m-d').'.pdf'
                    );
                }),
        ];
    }
}
