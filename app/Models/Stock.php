<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class Stock extends Model
{
    /**
     * Set by the stock ledger (the module that owns all stock writes);
     * every other writer is rejected.
     */
    public static bool $writesEnabled = false;

    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];

    protected static function booted(): void
    {
        static::creating(function (Stock $stock): void {
            if (! static::$writesEnabled) {
                throw new RuntimeException('Stock rows are derived state; write via App\Support\StockLedger::apply().');
            }
        });

        static::updating(function (Stock $stock): void {
            if (! static::$writesEnabled) {
                throw new RuntimeException('Stock rows are derived state; write via App\Support\StockLedger::apply().');
            }
        });

        static::deleting(function (Stock $stock): void {
            if (! static::$writesEnabled) {
                throw new RuntimeException('Stock rows are derived state; write via App\Support\StockLedger::apply().');
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
