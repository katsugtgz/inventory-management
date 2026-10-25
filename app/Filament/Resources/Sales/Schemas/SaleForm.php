<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale Information')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(Warehouse::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('invoice_no')
                            ->label('Invoice Number')
                            ->placeholder('e.g. SAL-0001')
                            ->required()
                            ->maxLength(255)
                            ->unique(Sale::class, 'invoice_no', ignoreRecord: true),
                        DatePicker::make('sale_date')
                            ->label('Sale Date')
                            ->required()
                            ->default(now()),
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0)
                            ->readOnly()
                            ->dehydrated(false),
                        TextInput::make('paid_amount')
                            ->label('Paid Amount')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0)
                            ->required(),
                        TextInput::make('due_amount')
                            ->label('Due Amount')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0)
                            ->readOnly()
                            ->dehydrated(false),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Sale Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->label('Products')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $quantity = (float) $get('quantity');
                                        $unitPrice = (float) $get('unit_price');
                                        $set('subtotal', $quantity * $unitPrice);
                                    }),
                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $quantity = (float) $get('quantity');
                                        $unitPrice = (float) $get('unit_price');
                                        $set('subtotal', $quantity * $unitPrice);
                                    }),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
                                    ->readOnly(),
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->addActionLabel('Add Product')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }
}
