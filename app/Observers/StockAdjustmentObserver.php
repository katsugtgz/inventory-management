<?php

namespace App\Observers;

use App\Models\StockAdjustment;
use App\Support\StockLedger;

class StockAdjustmentObserver
{
    public function created(StockAdjustment $stockAdjustment): void
    {
        $this->adjustStock($stockAdjustment, $this->signedEffect($stockAdjustment));
    }

    public function updated(StockAdjustment $stockAdjustment): void
    {
        if (! $stockAdjustment->wasChanged(['type', 'quantity'])) {
            return;
        }

        $originalEffect = $this->signedQuantity(
            $stockAdjustment->getOriginal('type'),
            (int) $stockAdjustment->getOriginal('quantity'),
        );
        $newEffect = $this->signedEffect($stockAdjustment);

        $this->adjustStock($stockAdjustment, $newEffect - $originalEffect);
    }

    public function deleted(StockAdjustment $stockAdjustment): void
    {
        $this->adjustStock($stockAdjustment, -1 * $this->signedEffect($stockAdjustment));
    }

    protected function signedEffect(StockAdjustment $stockAdjustment): int
    {
        return $this->signedQuantity($stockAdjustment->type, $stockAdjustment->quantity);
    }

    protected function signedQuantity(string $type, int $quantity): int
    {
        return $type === 'add' ? $quantity : -1 * $quantity;
    }

    protected function adjustStock(StockAdjustment $stockAdjustment, int $quantityChange): void
    {
        StockLedger::apply(
            $stockAdjustment->product_id,
            $stockAdjustment->warehouse_id,
            $quantityChange,
        );
    }
}
