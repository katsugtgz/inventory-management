<?php

namespace App\Filament\Pages;

use App\Exports\ProfitLossReportExport;
use App\Models\Category;
use App\Reports\ProfitLossQuery;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
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

class ProfitLossReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-rupee';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Profit & Loss';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.profit-loss-report';

    protected function aliasSort(string $alias): Closure
    {
        return fn (Builder $query, string $direction): Builder => $query->orderByRaw("{$alias} {$direction}");
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ProfitLossQuery::base())
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('products.name', 'like', "%{$search}%"))
                    ->sortable(query: $this->aliasSort('product_name')),
                TextColumn::make('product_sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('units_sold')
                    ->label('Units Sold')
                    ->sortable(query: $this->aliasSort('units_sold')),
                TextColumn::make('revenue')
                    ->label('Revenue')
                    ->money('INR')
                    ->sortable(query: $this->aliasSort('revenue')),
                TextColumn::make('cost')
                    ->label('Cost')
                    ->money('INR')
                    ->sortable(query: $this->aliasSort('cost')),
                TextColumn::make('profit')
                    ->label('Profit')
                    ->money('INR')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(query: $this->aliasSort('profit')),
                TextColumn::make('margin')
                    ->label('Margin')
                    ->getStateUsing(fn ($record) => $record->revenue > 0
                        ? round(($record->profit / $record->revenue) * 100, 1)
                        : 0)
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => $state >= 25 ? 'success' : ($state >= 10 ? 'warning' : 'danger')),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::pluck('name', 'id'))
                    ->placeholder('All Categories')
                    ->query(fn (Builder $query, array $data): Builder => ProfitLossQuery::applyFilters(
                        $query,
                        categoryId: $data['value'] ?? null,
                    )),
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from_date')
                            ->label('From Date'),
                        DatePicker::make('to_date')
                            ->label('To Date'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => ProfitLossQuery::applyFilters(
                        $query,
                        fromDate: $data['from_date'] ?? null,
                        toDate: $data['to_date'] ?? null,
                    )),
            ])
            ->defaultSort('profit', 'desc')
            ->defaultKeySort(false);
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
                        new ProfitLossReportExport(
                            categoryId: $filters['category_id']['value'] ?? null,
                            fromDate: $filters['date_range']['from_date'] ?? null,
                            toDate: $filters['date_range']['to_date'] ?? null,
                        ),
                        'profit-loss-report-'.now()->format('Y-m-d').'.xlsx'
                    );
                }),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->action(function () {
                    $rows = $this->getFilteredTableQuery()
                        ->reorder()
                        ->orderByRaw('profit desc')
                        ->get();
                    $pdf = Pdf::loadView('pdf.profit-loss-report', ['rows' => $rows]);

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        'profit-loss-report-'.now()->format('Y-m-d').'.pdf'
                    );
                }),
        ];
    }
}
