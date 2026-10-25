<?php

namespace App\Observers;

use App\Models\SaleItem;
use App\Support\StockLedger;

class SaleItemObserver
{
    public function created(SaleItem $saleItem): void
    {
        $this->adjustStock($saleItem, -1 * $saleItem->quantity);
    }

    public function updated(SaleItem $saleItem): void
    {
        if (! $saleItem->wasChanged('quantity')) {
            return;
        }

        $difference = $saleItem->quantity - $saleItem->getOriginal('quantity');

        $this->adjustStock($saleItem, -1 * $difference);
    }

    public function deleted(SaleItem $saleItem): void
    {
        $this->adjustStock($saleItem, $saleItem->quantity);
    }

    protected function adjustStock(SaleItem $saleItem, int $quantityChange): void
    {
        StockLedger::apply(
            $saleItem->product_id,
            $saleItem->sale->warehouse_id,
            $quantityChange,
        );
    }
}
