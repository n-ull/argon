<?php

namespace Database\Factories\Domain\ProductCatalog\Models;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'max_per_order' => 10,
            'min_per_order' => 1,
            'product_type' => ProductType::TICKET,
            'product_price_type' => 'fixed',
            'hide_before_sale_start_date' => false,
            'hide_after_sale_end_date' => false,
            'hide_when_sold_out' => false,
            'show_stock' => true,
            'start_sale_date' => null,
            'is_hidden' => false,
            'end_sale_date' => null,
            'event_id' => Event::factory(),
        ];
    }
}
