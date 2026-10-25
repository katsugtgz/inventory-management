<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Filament\Resources\Purchases\Schemas\PurchaseInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewPurchase extends ViewRecord
{
    protected static string $resource = PurchaseResource::class;

    public function infolist(Schema $schema): Schema
    {
        return PurchaseInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
