<?php

namespace App\Providers;

use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Observers\PurchaseItemObserver;
use App\Observers\PurchaseItemTotalsObserver;
use App\Observers\SaleItemObserver;
use App\Observers\SaleItemTotalsObserver;
use App\Observers\StockAdjustmentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PurchaseItem::observe(PurchaseItemObserver::class);
        PurchaseItem::observe(PurchaseItemTotalsObserver::class);
        SaleItem::observe(SaleItemObserver::class);
        SaleItem::observe(SaleItemTotalsObserver::class);
        StockAdjustment::observe(StockAdjustmentObserver::class);
    }
}
