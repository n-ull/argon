<?php

use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\Ordering\Services\OrderService;
use Domain\Ticketing\Jobs\GenerateTickets;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

test('completing an order dispatches the listener and job', function () {
    Bus::fake([GenerateTickets::class]);

    // Arrange
    $order = Order::factory()->create([
        'status' => OrderStatus::PENDING,
    ]);

    // Mock OrderService dependencies or use the container
    $service = app(OrderService::class);

    // Act
    $service->completePendingOrder(orderId: $order->id);

    // Assert
    Bus::assertDispatched(GenerateTickets::class, function ($job) use ($order) {
        return $job->orderId === $order->id;
    });
});

test('GenerateTickets job creates tickets for each order item', function () {
    // Arrange
    $order = Order::factory()->create([
        'status' => OrderStatus::COMPLETED,
    ]);

    // Create 2 items, one with quantity 2 and another with quantity 1
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => 2,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => 1,
    ]);

    // Execute job
    $job = new GenerateTickets($order->id);
    $job->handle(new Google2FA);

    // Assert
    expect(Ticket::count())->toBe(3);
    expect(Ticket::where('order_id', $order->id)->count())->toBe(3);
});
