<?php

namespace App\Support;

use App\Models\Stock;

class StockLedger
{
    /**
     * Apply a signed quantity change for one product in one warehouse,
     * creating the stock row on first movement. Zero deltas are no-ops.
     */
    public static function apply(int $productId, int $warehouseId, int $quantityChange): Stock
    {
        Stock::$writesEnabled = true;

        try {
            $stock = Stock::firstOrNew([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ]);

            if ($quantityChange === 0) {
                return $stock;
            }

            $stock->quantity = ($stock->quantity ?? 0) + $quantityChange;
            $stock->save();

            return $stock;
        } finally {
            Stock::$writesEnabled = false;
        }
    }
}
