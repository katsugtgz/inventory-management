<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Support\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StockWriteGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_stock_create_is_rejected(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->expectException(RuntimeException::class);

        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 5,
        ]);
    }

    public function test_direct_stock_update_is_rejected(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        StockLedger::apply($product->id, $warehouse->id, 5);

        $stock = Stock::query()->firstOrFail();
        $stock->quantity = 999;

        $this->expectException(RuntimeException::class);

        $stock->save();
    }

    public function test_direct_stock_delete_is_rejected(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        StockLedger::apply($product->id, $warehouse->id, 5);

        $stock = Stock::query()->firstOrFail();

        $this->expectException(RuntimeException::class);

        $stock->delete();
    }

    public function test_ledger_can_still_write_through_bypass(): void
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $stock = StockLedger::apply($product->id, $warehouse->id, 7);

        $this->assertSame(7, $stock->quantity);
        $this->assertDatabaseHas('stocks', ['quantity' => 7]);
    }
}
