<?php

use Domain\Ticketing\Actions\CreateOrderTicket;
use Domain\Ticketing\Models\Ticket;

test('domain/ticketing/actions/createorderticket', function () {
    $product = setupAvailableProduct();

    $action = CreateOrderTicket::run($product);

    expect($action)->toBeInstanceOf(Ticket::class);
    expect($action->token)->not()->toBeEmpty();
    expect($action->event_id)->toBe($product->event_id);
    expect($action->product_id)->toBe($product->id);
    expect($action->order_id)->toBe($product->order_id);

    $this->assertDatabaseHas('tickets', [
        'token' => $action->token,
        'event_id' => $product->event_id,
        'product_id' => $product->id,
        'order_id' => $product->order_id,
    ]);
});
