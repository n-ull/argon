<?php

use Domain\Ticketing\Actions\CreateOrderTicket;
use Domain\Ticketing\Models\Ticket;

test('domain/ticketing/actions/createorderticket', function () {
    $product = setupAvailableProduct();

    $action = CreateOrderTicket::run([
        'product_id' => $product->id,
    ]);

    expect($action)->toBeInstanceOf(Ticket::class);
});
