<?php

use Domain\Ticketing\Actions\CreateOrderTicket;
use Domain\Ticketing\Models\Ticket;

describe('CreateOrderTicket', function () {
    it('should create a ticket for a product', function () {
        $product = setupAvailableProduct();

        $action = CreateOrderTicket::run($product);

        expect($action)->toBeInstanceOf(Ticket::class);
        expect($action->token)->not()->toBeEmpty();
        expect($action->event_id)->toBe($product->event_id);
        expect($action->product_id)->toBe($product->id);

        $this->assertDatabaseHas('tickets', [
            'token' => $action->token,
            'event_id' => $product->event_id,
            'product_id' => $product->id,
        ]);
    });
});
