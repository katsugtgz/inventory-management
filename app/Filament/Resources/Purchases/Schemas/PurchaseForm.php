<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Purchase Information')
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(Supplier::pluck('name', 'id'))
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
                            ->placeholder('e.g. PUR-0001')
                            ->required()
                            ->maxLength(255)
                            ->unique(Purchase::class, 'invoice_no', ignoreRecord: true),
                        DatePicker::make('purchase_date')
                            ->label('Purchase Date')
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
                Section::make('Purchase Items')
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
                                        $unitCost = (float) $get('unit_cost');
                                        $set('subtotal', $quantity * $unitCost);
                                    }),
                                TextInput::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $quantity = (float) $get('quantity');
                                        $unitCost = (float) $get('unit_cost');
                                        $set('subtotal', $quantity * $unitCost);
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
