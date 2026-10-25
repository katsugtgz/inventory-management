<?php

namespace App\Observers;

use App\Models\PurchaseItem;

class PurchaseItemTotalsObserver
{
    public function created(PurchaseItem $purchaseItem): void
    {
        $this->refreshSubtotal($purchaseItem);
        $purchaseItem->purchase->recalculateTotals();
    }

    public function updated(PurchaseItem $purchaseItem): void
    {
        if (! $purchaseItem->wasChanged(['quantity', 'unit_cost'])) {
            return;
        }

        $this->refreshSubtotal($purchaseItem);
        $purchaseItem->purchase->recalculateTotals();
    }

    public function deleted(PurchaseItem $purchaseItem): void
    {
        $purchaseItem->purchase->recalculateTotals();
    }

    protected function refreshSubtotal(PurchaseItem $purchaseItem): void
    {
        $subtotal = (float) $purchaseItem->quantity * (float) $purchaseItem->unit_cost;

        if ((float) $purchaseItem->subtotal === $subtotal) {
            return;
        }

        $purchaseItem->forceFill(['subtotal' => $subtotal]);
        $purchaseItem->saveQuietly();
    }
}
