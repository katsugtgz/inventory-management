<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Support\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockLedgerTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
    }

    public function test_apply_creates_stock_row_on_first_movement(): void
    {
        $stock = StockLedger::apply($this->product->id, $this->warehouse->id, 5);

        $this->assertSame(5, $stock->quantity);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 5,
        ]);
    }

    public function test_apply_accumulates_on_existing_row(): void
    {
        StockLedger::apply($this->product->id, $this->warehouse->id, 5);
        $stock = StockLedger::apply($this->product->id, $this->warehouse->id, 3);

        $this->assertSame(8, $stock->quantity);
        $this->assertSame(1, Stock::count());
    }

    public function test_apply_zero_delta_leaves_row_untouched(): void
    {
        StockLedger::apply($this->product->id, $this->warehouse->id, 5);
        StockLedger::apply($this->product->id, $this->warehouse->id, 0);

        $this->assertDatabaseHas('stocks', ['quantity' => 5]);
    }

    public function test_purchase_item_created_increases_stock(): void
    {
        $purchase = Purchase::factory()->create(['warehouse_id' => $this->warehouse->id]);

        PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);
    }

    public function test_purchase_item_quantity_update_adjusts_delta(): void
    {
        $purchase = Purchase::factory()->create(['warehouse_id' => $this->warehouse->id]);
        $item = PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $item->update(['quantity' => 7]);

        $this->assertDatabaseHas('stocks', ['quantity' => 7]);
    }

    public function test_purchase_item_deleted_reverses_stock(): void
    {
        $purchase = Purchase::factory()->create(['warehouse_id' => $this->warehouse->id]);
        $item = PurchaseItem::factory()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $item->delete();

        $this->assertDatabaseHas('stocks', ['quantity' => 0]);
    }

    public function test_sale_item_created_decreases_stock(): void
    {
        StockLedger::apply($this->product->id, $this->warehouse->id, 20);

        $sale = Sale::factory()->create(['warehouse_id' => $this->warehouse->id]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 4,
        ]);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 16,
        ]);
    }

    public function test_sale_item_deleted_returns_stock(): void
    {
        StockLedger::apply($this->product->id, $this->warehouse->id, 20);

        $sale = Sale::factory()->create(['warehouse_id' => $this->warehouse->id]);
        $item = SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'quantity' => 4,
        ]);

        $item->delete();

        $this->assertDatabaseHas('stocks', ['quantity' => 20]);
    }

    public function test_adjustment_add_type_increases_stock(): void
    {
        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'add',
            'quantity' => 6,
        ]);

        $this->assertDatabaseHas('stocks', ['quantity' => 6]);
    }

    public function test_adjustment_remove_type_decreases_stock(): void
    {
        StockLedger::apply($this->product->id, $this->warehouse->id, 9);

        StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'subtract',
            'quantity' => 6,
        ]);

        $this->assertDatabaseHas('stocks', ['quantity' => 3]);
    }

    public function test_adjustment_type_flip_on_update_nets_out(): void
    {
        $adjustment = StockAdjustment::factory()->create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'add',
            'quantity' => 5,
        ]);

        $adjustment->update(['type' => 'subtract']);

        $this->assertDatabaseHas('stocks', ['quantity' => -5]);
    }
}
