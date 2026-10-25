<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'sku' => fake()->unique()->ean13(),
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'cost_price' => fake()->randomFloat(2, 10, 500),
            'selling_price' => fake()->randomFloat(2, 20, 800),
            'status' => true,
        ];
    }
}
