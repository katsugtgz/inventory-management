<?php

namespace App\Observers;

use App\Models\SaleItem;

class SaleItemTotalsObserver
{
    public function created(SaleItem $saleItem): void
    {
        $this->refreshSubtotal($saleItem);
        $saleItem->sale->recalculateTotals();
    }

    public function updated(SaleItem $saleItem): void
    {
        if (! $saleItem->wasChanged(['quantity', 'unit_price'])) {
            return;
        }

        $this->refreshSubtotal($saleItem);
        $saleItem->sale->recalculateTotals();
    }

    public function deleted(SaleItem $saleItem): void
    {
        $saleItem->sale->recalculateTotals();
    }

    protected function refreshSubtotal(SaleItem $saleItem): void
    {
        $subtotal = (float) $saleItem->quantity * (float) $saleItem->unit_price;

        if ((float) $saleItem->subtotal === $subtotal) {
            return;
        }

        $saleItem->forceFill(['subtotal' => $subtotal]);
        $saleItem->saveQuietly();
    }
}
