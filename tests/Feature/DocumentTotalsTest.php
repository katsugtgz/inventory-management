<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_recalculate_totals_sums_item_subtotals(): void
    {
        $sale = Sale::factory()->create(['total_amount' => 0]);
        SaleItem::factory()->for($sale)->create(['quantity' => 2, 'unit_price' => 50.50, 'subtotal' => 101.00]);
        SaleItem::factory()->for($sale)->create(['quantity' => 1, 'unit_price' => 30.00, 'subtotal' => 30.00]);

        $sale->recalculateTotals();

        $this->assertSame('131.00', (string) $sale->refresh()->total_amount);
    }

    public function test_sale_recalculate_totals_derives_due_amount(): void
    {
        $sale = Sale::factory()->create(['paid_amount' => 60.00]);
        SaleItem::factory()->for($sale)->create(['quantity' => 2, 'unit_price' => 50.00, 'subtotal' => 100.00]);

        $sale->recalculateTotals();

        $this->assertSame('40.00', (string) $sale->refresh()->due_amount);
    }

    public function test_sale_recalculate_totals_zeroes_totals_when_no_items(): void
    {
        $sale = Sale::factory()->create(['total_amount' => 500.00, 'due_amount' => 200.00]);

        $sale->recalculateTotals();

        $this->assertSame('0.00', (string) $sale->refresh()->total_amount);
        $this->assertSame('0.00', (string) $sale->refresh()->due_amount);
    }

    public function test_purchase_recalculate_totals_sums_item_subtotals(): void
    {
        $purchase = Purchase::factory()->create(['total_amount' => 0]);
        PurchaseItem::factory()->for($purchase)->create(['quantity' => 3, 'unit_cost' => 10.00, 'subtotal' => 30.00]);
        PurchaseItem::factory()->for($purchase)->create(['quantity' => 1, 'unit_cost' => 25.25, 'subtotal' => 25.25]);

        $purchase->recalculateTotals();

        $this->assertSame('55.25', (string) $purchase->refresh()->total_amount);
    }

    public function test_sale_observations_recalculate_totals(): void
    {
        $sale = Sale::factory()->create(['paid_amount' => 0]);
        SaleItem::factory()->for($sale)->create(['quantity' => 2, 'unit_price' => 50.00, 'subtotal' => 100.00]);

        // Observer fires on item create: total becomes 100.00, then adding a
        // second item recalculates again over both rows.
        SaleItem::factory()->for($sale)->create(['quantity' => 1, 'unit_price' => 40.00, 'subtotal' => 40.00]);

        $this->assertSame('140.00', (string) $sale->refresh()->total_amount);
        $this->assertSame('140.00', (string) $sale->refresh()->due_amount);
    }

    public function test_item_subtotal_is_derived_on_save(): void
    {
        $sale = Sale::factory()->create();
        $item = SaleItem::factory()->for($sale)->create(['quantity' => 2, 'unit_price' => 50.00, 'subtotal' => 0]);

        $this->assertSame('100.00', (string) $item->refresh()->subtotal);
    }

    public function test_item_quantity_update_recomputes_subtotal_and_totals(): void
    {
        $sale = Sale::factory()->create();
        $item = SaleItem::factory()->for($sale)->create(['quantity' => 2, 'unit_price' => 50.00, 'subtotal' => 100.00]);

        $item->update(['quantity' => 3]);

        $this->assertSame('150.00', (string) $item->refresh()->subtotal);
        $this->assertSame('150.00', (string) $sale->refresh()->total_amount);
    }

    public function test_purchase_item_subtotal_is_derived_on_save(): void
    {
        $purchase = Purchase::factory()->create();
        $item = PurchaseItem::factory()->for($purchase)->create(['quantity' => 4, 'unit_cost' => 12.50, 'subtotal' => 0]);

        $this->assertSame('50.00', (string) $item->refresh()->subtotal);
        $this->assertSame('50.00', (string) $purchase->refresh()->total_amount);
    }
}
