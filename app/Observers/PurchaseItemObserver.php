<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Support\StockLedger;

class PurchaseItemObserver
{
    public function created(PurchaseItem $purchaseItem): void
    {
        $this->adjustStock($purchaseItem, $purchaseItem->quantity);
    }

    public function updated(PurchaseItem $purchaseItem): void
    {
        if (! $purchaseItem->wasChanged('quantity')) {
            return;
        }

        $difference = $purchaseItem->quantity - $purchaseItem->getOriginal('quantity');

        $this->adjustStock($purchaseItem, $difference);
    }

    public function deleted(PurchaseItem $purchaseItem): void
    {
        $this->adjustStock($purchaseItem, -1 * $purchaseItem->quantity);
    }

    protected function adjustStock(PurchaseItem $purchaseItem, int $quantityChange): void
    {
        StockLedger::apply(
            $purchaseItem->product_id,
            $purchaseItem->purchase->warehouse_id,
            $quantityChange,
        );
    }
}
