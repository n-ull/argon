<?php

namespace Database\Factories\Domain\Ordering\Models;

use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_price_id' => ProductPrice::factory(),
            'product_id' => function (array $attributes) {
                return ProductPrice::find($attributes['product_price_id'])->product_id;
            },
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
