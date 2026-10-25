<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'invoice_no' => 'SAL-'.fake()->unique()->numberBetween(1000, 9999),
            'sale_date' => fake()->date(),
            'total_amount' => 0,
            'paid_amount' => 0,
            'due_amount' => 0,
            'status' => 'pending',
        ];
    }
}
