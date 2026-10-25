<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Models\Supplier;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Supplier Name')
                            ->placeholder('e.g. ABC Trading Company')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->placeholder('supplier@example.com')
                            ->email()
                            ->maxLength(255)
                            ->unique(Supplier::class, 'email', ignoreRecord: true),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->placeholder('e.g. +91 98765 43210')
                            ->tel()
                            ->maxLength(20),
                        Textarea::make('address')
                            ->label('Address')
                            ->placeholder('Enter full address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('status')
                            ->label('Active Status')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
