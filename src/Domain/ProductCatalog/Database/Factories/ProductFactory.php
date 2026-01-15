<?php

namespace Domain\ProductCatalog\Database\Factories;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Enums\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->words(4, true),
            'max_per_order' => $this->faker->numberBetween(1, 10),
            'min_per_order' => 1,
            'product_type' => $this->faker->randomElement([ProductType::GENERAL, ProductType::TICKET]),
            'ticket_type' => $this->faker->randomElement([TicketType::STATIC , TicketType::DYNAMIC]),
            'hide_before_sale_start_date' => false,
            'hide_after_sale_end_date' => false,
            'hide_when_sold_out' => $this->faker->boolean(30),
            'show_stock' => $this->faker->boolean(70),
            'start_sale_date' => now(),
            'end_sale_date' => now()->addDays(30),
            'event_id' => Event::factory(),
        ];
    }
}
