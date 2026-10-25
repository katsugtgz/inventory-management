<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use App\Models\Product;
use App\Models\Warehouse;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Stock Adjustment')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(Warehouse::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->label('Adjustment Type')
                            ->options([
                                'add' => 'Add Stock',
                                'subtract' => 'Subtract Stock',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Reason')
                            ->placeholder('e.g. Damaged during transit, Stock count correction, Expired items')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
