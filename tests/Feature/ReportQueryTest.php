<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Reports\PurchaseQuery;
use App\Reports\SaleQuery;
use App\Reports\StockQuery;
use App\Support\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_query_matches_export_query_filters(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();

        $inScope = Sale::factory()->create([
            'customer_id' => $customerA->id,
            'status' => 'completed',
            'sale_date' => '2026-10-10',
        ]);
        Sale::factory()->create(['customer_id' => $customerB->id, 'status' => 'completed', 'sale_date' => '2026-10-10']);
        Sale::factory()->create(['customer_id' => $customerA->id, 'status' => 'pending', 'sale_date' => '2026-10-10']);
        Sale::factory()->create(['customer_id' => $customerA->id, 'status' => 'completed', 'sale_date' => '2026-11-01']);

        $rows = SaleQuery::applyFilters(
            SaleQuery::base(),
            customerId: $customerA->id,
            status: 'completed',
            fromDate: '2026-10-01',
            toDate: '2026-10-31',
        )->get();

        $this->assertSame([$inScope->id], $rows->pluck('id')->all());
    }

    public function test_sale_query_base_eager_loads_relations(): void
    {
        Sale::factory()->create();

        $query = SaleQuery::base();

        $this->assertArrayHasKey('customer', $query->getEagerLoads());
        $this->assertArrayHasKey('warehouse', $query->getEagerLoads());
    }

    public function test_purchase_query_matches_export_query_filters(): void
    {
        $supplierA = Supplier::factory()->create();
        $supplierB = Supplier::factory()->create();

        $inScope = Purchase::factory()->create([
            'supplier_id' => $supplierA->id,
            'status' => 'completed',
            'purchase_date' => '2026-10-10',
        ]);
        Purchase::factory()->create(['supplier_id' => $supplierB->id, 'status' => 'completed', 'purchase_date' => '2026-10-10']);
        Purchase::factory()->create(['supplier_id' => $supplierA->id, 'status' => 'pending', 'purchase_date' => '2026-10-10']);

        $rows = PurchaseQuery::applyFilters(
            PurchaseQuery::base(),
            supplierId: $supplierA->id,
            status: 'completed',
            fromDate: '2026-10-01',
            toDate: '2026-10-31',
        )->get();

        $this->assertSame([$inScope->id], $rows->pluck('id')->all());
    }

    public function test_stock_query_matches_export_query_filters(): void
    {
        $productA = Product::factory()->create();
        $warehouseA = Warehouse::factory()->create();
        $warehouseB = Warehouse::factory()->create();

        $stockA = StockLedger::apply($productA->id, $warehouseA->id, 5);
        $stockB = StockLedger::apply($productA->id, $warehouseB->id, 3);
        $stockA->forceFill(['created_at' => '2026-10-10 08:00:00', 'updated_at' => '2026-10-10 08:00:00'])->saveQuietly();
        $stockB->forceFill(['created_at' => '2026-10-11 08:00:00', 'updated_at' => '2026-10-11 08:00:00'])->saveQuietly();

        $rows = StockQuery::applyFilters(
            StockQuery::base(),
            productId: $productA->id,
            warehouseId: $warehouseA->id,
            fromDate: '2026-10-10',
            toDate: '2026-10-10',
        )->get();

        $this->assertSame(1, $rows->count());
        $this->assertSame($warehouseA->id, $rows->first()->warehouse_id);
    }
}
