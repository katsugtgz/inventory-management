<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Master data ----
        $parentCategories = collect(['Electronics', 'Groceries', 'Stationery', 'Home & Kitchen'])
            ->mapWithKeys(fn ($name) => [$name => Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => true,
            ])->id]);

        $childNames = ['Mobile Accessories' => 'Electronics', 'Beverages' => 'Groceries', 'Writing Supplies' => 'Stationery'];
        foreach ($childNames as $child => $parent) {
            Category::create([
                'name' => $child,
                'slug' => Str::slug($child),
                'parent_id' => $parentCategories[$parent],
                'status' => true,
            ]);
        }

        $units = collect([
            ['name' => 'Piece', 'short_code' => 'PCS'],
            ['name' => 'Kilogram', 'short_code' => 'KG'],
            ['name' => 'Box', 'short_code' => 'BOX'],
            ['name' => 'Litre', 'short_code' => 'LTR'],
        ])->mapWithKeys(fn ($u) => [$u['short_code'] => Unit::create($u)->id]);

        $suppliers = collect([
            ['name' => 'ABC Trading Company', 'email' => 'abc@example.com', 'phone' => '+91 98765 43210', 'address' => '12 Industrial Area, Vadodara, Gujarat'],
            ['name' => 'Global Wholesale Mart', 'email' => 'global@example.com', 'phone' => '+91 98111 22334', 'address' => '48 Market Street, Mumbai, Maharashtra'],
            ['name' => 'Shree Distributors', 'email' => 'shree@example.com', 'phone' => '+91 99001 55667', 'address' => '7 GT Road, Delhi'],
        ])->map(fn ($s) => Supplier::create($s + ['status' => true]));

        $customers = collect([
            ['name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '+91 90909 10101', 'address' => '5 Park Lane, Ahmedabad, Gujarat'],
            ['name' => 'Priya Sharma', 'email' => 'priya@example.com', 'phone' => '+91 90909 20202', 'address' => '22 Lake View, Pune, Maharashtra'],
            ['name' => 'Ravi Kumar', 'email' => 'ravi@example.com', 'phone' => '+91 90909 30303', 'address' => '9 Hill Road, Jaipur, Rajasthan'],
            ['name' => 'Meena Traders', 'email' => 'meena@example.com', 'phone' => '+91 90909 40404', 'address' => '31 Bazaar Marg, Surat, Gujarat'],
        ])->map(fn ($c) => Customer::create($c + ['status' => true]));

        $warehouses = collect([
            ['name' => 'Main Warehouse', 'location' => 'Sector 12, Vadodara, Gujarat'],
            ['name' => 'City Store', 'location' => 'Ring Road, Ahmedabad, Gujarat'],
        ])->map(fn ($w) => Warehouse::create($w + ['status' => true]));

        // ---- Products ----
        $productDefs = [
            ['Wireless Mouse', 'Electronics', 'PCS', 450, 799],
            ['USB-C Cable', 'Mobile Accessories', 'PCS', 120, 249],
            ['Bluetooth Speaker', 'Electronics', 'PCS', 1500, 2499],
            ['Phone Cover', 'Mobile Accessories', 'PCS', 90, 199],
            ['Green Tea 250g', 'Beverages', 'BOX', 210, 399],
            ['Instant Coffee 500g', 'Beverages', 'BOX', 480, 899],
            ['Ballpoint Pen Pack', 'Writing Supplies', 'BOX', 65, 129],
            ['Notebook A5', 'Writing Supplies', 'PCS', 40, 89],
            ['Non-stick Pan', 'Home & Kitchen', 'PCS', 890, 1599],
            ['Storage Container Set', 'Home & Kitchen', 'BOX', 350, 649],
        ];

        $products = collect();
        foreach ($productDefs as $i => [$name, $category, $unit, $cost, $price]) {
            $products->push(Product::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'sku' => 'PRD-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'category_id' => $parentCategories[$category] ?? Category::where('slug', Str::slug($category))->value('id'),
                'unit_id' => $units[$unit],
                'cost_price' => $cost,
                'selling_price' => $price,
                'description' => "Sample {$name} used for demo data.",
                'status' => true,
            ]));
        }

        // ---- Purchases (spread over last 30 days) ----
        $purchaseNo = 1;
        foreach ($products->chunk(2) as $index => $chunk) {
            $supplier = $suppliers[$index % $suppliers->count()];
            $warehouse = $warehouses[$index % $warehouses->count()];
            $date = now()->subDays(28 - $index * 2);

            $total = 0;
            $items = $chunk->map(function ($product) use (&$total) {
                $quantity = rand(20, 60);
                $subtotal = $quantity * (float) $product->cost_price;
                $total += $subtotal;

                return ['product' => $product, 'quantity' => $quantity, 'unit_cost' => $product->cost_price, 'subtotal' => $subtotal];
            });

            $paid = round($total * (rand(5, 10) / 10), 2);

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'invoice_no' => 'PUR-'.str_pad((string) $purchaseNo++, 4, '0', STR_PAD_LEFT),
                'purchase_date' => $date,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'due_amount' => round($total - $paid, 2),
                'status' => 'completed',
            ]);

            foreach ($items as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        }

        // ---- Sales (spread over last 30 days, this year for chart) ----
        $saleNo = 1;
        foreach (range(1, 12) as $n) {
            $customer = $customers[$n % $customers->count()];
            $warehouse = $warehouses[$n % $warehouses->count()];
            $date = now()->subDays(max(0, 30 - $n * 2));

            $picked = $products->shuffle()->take(rand(2, 4));
            $total = 0;
            $items = $picked->map(function ($product) use (&$total) {
                $quantity = rand(2, 10);
                $subtotal = $quantity * (float) $product->selling_price;
                $total += $subtotal;

                return ['product' => $product, 'quantity' => $quantity, 'unit_price' => $product->selling_price, 'subtotal' => $subtotal];
            });

            $paid = round($total * (rand(6, 10) / 10), 2);

            $sale = Sale::create([
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouse->id,
                'invoice_no' => 'SAL-'.str_pad((string) $saleNo++, 4, '0', STR_PAD_LEFT),
                'sale_date' => $date,
                'total_amount' => $total,
                'paid_amount' => $paid,
                'due_amount' => round($total - $paid, 2),
                'status' => 'completed',
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        }

        // ---- A couple of manual stock adjustments ----
        StockAdjustment::create([
            'product_id' => $products[0]->id,
            'warehouse_id' => $warehouses[0]->id,
            'type' => 'subtract',
            'quantity' => 2,
            'reason' => 'Damaged during transit',
            'adjusted_by' => 1,
        ]);

        StockAdjustment::create([
            'product_id' => $products[4]->id,
            'warehouse_id' => $warehouses[0]->id,
            'type' => 'add',
            'quantity' => 5,
            'reason' => 'Stock count correction',
            'adjusted_by' => 1,
        ]);
    }
}
