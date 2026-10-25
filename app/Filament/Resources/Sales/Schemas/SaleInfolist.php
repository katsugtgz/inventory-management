<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale Information')
                    ->schema([
                        TextEntry::make('invoice_no')
                            ->label('Invoice Number')
                            ->weight('bold'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                            }),
                        TextEntry::make('customer.name')
                            ->label('Customer'),
                        TextEntry::make('warehouse.name')
                            ->label('Warehouse'),
                        TextEntry::make('sale_date')
                            ->label('Sale Date')
                            ->date('d M Y'),
                        TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('INR'),
                        TextEntry::make('paid_amount')
                            ->label('Paid Amount')
                            ->money('INR'),
                        TextEntry::make('due_amount')
                            ->label('Due Amount')
                            ->money('INR')
                            ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                    ])
                    ->columns(3),
                Section::make('Sale Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Product')
                                    ->weight('bold')
                                    ->columnSpan(2),
                                TextEntry::make('quantity')
                                    ->label('Quantity'),
                                TextEntry::make('unit_price')
                                    ->label('Unit Price')
                                    ->money('INR'),
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('INR')
                                    ->weight('bold'),
                            ])
                            ->columns(5)
                            ->contained(false),
                    ])
                    ->columns(1),
            ]);
    }
}
