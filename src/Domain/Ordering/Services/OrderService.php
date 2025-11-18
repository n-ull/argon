<?php

namespace Domain\Ordering\Services;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Events\OrderCreated;
use Domain\Ordering\Models\Order;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private OrderValidatorService $orderValidatorService
    ) {
        //
    }

    public function createPendingOrder(CreateOrderData $orderData): Order
    {
        // check event existence
        $event = Event::find($orderData->eventId);

        if (! $event) {
            throw new \DomainException("Event doesn't exist.");
        }

        // validate order
        $this->orderValidatorService->validateOrder($orderData);

        // create order
        $order = $event->orders()->create([
            'total_gross',
            'total_before_additions',
            'reference_id' => ReferenceIdService::create($order->id ?? null),
        ]);

        event(new OrderCreated($order));

        return $order;
    }
}
