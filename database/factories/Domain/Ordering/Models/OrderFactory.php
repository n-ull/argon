<?php

namespace Database\Factories\Domain\Ordering\Models;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'subtotal' => $this->faker->randomFloat(2, 10, 100),
            'taxes_total' => 0,
            'fees_total' => 0,
            'items_snapshot' => [],
            'taxes_snapshot' => [],
            'fees_snapshot' => [],
            'status' => OrderStatus::PENDING,
            'reference_id' => $this->faker->uuid,
            'expires_at' => now()->addMinutes(15),
        ];
    }
}
