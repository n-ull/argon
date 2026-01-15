<?php

use Domain\Ordering\Events\OrderCompleted;
use Domain\Ordering\Models\Order;
use Domain\Ticketing\Jobs\GenerateTickets;
use Domain\Ticketing\Listeners\GenerateTicketsForOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

test('it dispatches GenerateTickets job when order is completed', function () {
    Bus::fake();
    Log::shouldReceive('info')->once();

    $order = Order::factory()->create(['id' => 123]);
    $event = new OrderCompleted($order);
    $listener = new GenerateTicketsForOrder;

    $listener->handle($event);

    Bus::assertDispatched(GenerateTickets::class, function ($job) use ($order) {
        return $job->orderId === $order->id;
    });
});
