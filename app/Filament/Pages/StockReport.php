<?php

namespace App\Filament\Pages;

use App\Exports\StockReportExport;
use App\Models\Product;
use App\Models\Warehouse;
use App\Reports\StockQuery;
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

class StockReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Stock Report';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.stock-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                StockQuery::base()
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(Product::pluck('name', 'id'))
                    ->placeholder('All Products')
                    ->query(fn (Builder $query, array $data): Builder => StockQuery::applyFilters(
                        $query,
                        productId: $data['value'] ?? null,
                    )),
                SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::pluck('name', 'id'))
                    ->placeholder('All Warehouses')
                    ->query(fn (Builder $query, array $data): Builder => StockQuery::applyFilters(
                        $query,
                        warehouseId: $data['value'] ?? null,
                    )),
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from_date')
                            ->label('From Date'),
                        DatePicker::make('to_date')
                            ->label('To Date'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => StockQuery::applyFilters(
                        $query,
                        fromDate: $data['from_date'] ?? null,
                        toDate: $data['to_date'] ?? null,
                    )),
            ])
            ->defaultSort('updated_at', 'desc');
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
                        new StockReportExport(
                            productId: $filters['product_id']['value'] ?? null,
                            warehouseId: $filters['warehouse_id']['value'] ?? null,
                            fromDate: $filters['date_range']['from_date'] ?? null,
                            toDate: $filters['date_range']['to_date'] ?? null,
                        ),
                        'stock-report-'.now()->format('Y-m-d').'.xlsx'
                    );
                }),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $stocks = $this->getFilteredTableQuery()->get();
                    $pdf = Pdf::loadView('pdf.stock-report', ['stocks' => $stocks]);

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        'stock-report-'.now()->format('Y-m-d').'.pdf'
                    );
                }),
        ];
    }
}
