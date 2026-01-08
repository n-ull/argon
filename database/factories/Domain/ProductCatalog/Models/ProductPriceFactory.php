<?php

namespace Database\Factories\Domain\ProductCatalog\Models;

use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'product_id' => \Domain\ProductCatalog\Models\Product::factory(),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'label' => $this->faker->word,
            'start_sale_date' => null,
            'end_sale_date' => null,
            'stock' => $this->faker->numberBetween(10, 100),
            'quantity_sold' => 0,
            'is_hidden' => false,
            'sort_order' => 0,
        ];
    }
}
