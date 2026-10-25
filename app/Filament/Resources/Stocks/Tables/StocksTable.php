<?php

namespace App\Filament\Resources\Stocks\Tables;

use App\Models\Product;
use App\Models\Warehouse;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StocksTable
{
    protected static int $lowStockThreshold = 10;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Current Stock')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= self::$lowStockThreshold => 'warning',
                        default => 'success',
                    })
                    ->icon(fn (int $state): string => match (true) {
                        $state <= 0 => 'heroicon-o-x-circle',
                        $state <= self::$lowStockThreshold => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-check-circle',
                    })
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->getStateUsing(fn ($record) => $record->product?->unit?->short_code ?? '—')
                    ->badge()
                    ->color('info'),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(Product::pluck('name', 'id'))
                    ->placeholder('All Products'),
                SelectFilter::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(Warehouse::pluck('name', 'id'))
                    ->placeholder('All Warehouses'),
                TernaryFilter::make('low_stock')
                    ->label('Low Stock Only')
                    ->queries(
                        true: fn ($query) => $query->where('quantity', '<=', self::$lowStockThreshold),
                        false: fn ($query) => $query,
                        blank: fn ($query) => $query,
                    ),
            ])
            ->defaultSort('quantity', 'asc');
    }
}
