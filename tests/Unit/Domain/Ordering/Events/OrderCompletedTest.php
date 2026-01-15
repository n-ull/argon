<?php

use Domain\Ordering\Events\OrderCompleted;
use Domain\Ordering\Models\Order;

test('it can be instantiated with an order', function () {
    $order = Order::factory()->make();
    $event = new OrderCompleted($order);

    expect($event->order)->toBe($order);
});
