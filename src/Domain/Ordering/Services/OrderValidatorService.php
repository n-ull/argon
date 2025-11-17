<?php

namespace Domain\Ordering\Services;

use Domain\EventManagment\Enums\EventStatus;
use Domain\EventManagment\Models\Event;
use Domain\Ordering\Data\CreateOrderData;

class OrderValidatorService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function validateOrder(CreateOrderData $orderData)
    {
        $event = Event::findOrFail($orderData->eventId);

        if ($event->status !== EventStatus::PUBLISHED) {
            throw new \DomainException('Event is not published.');
        }

        foreach ($orderData->products as $product) {
            $eventProduct = $event->products->firstWhere('product_id', $product->productId);
            $selectedPrice = $eventProduct->product_prices->firstWhere('id', $product->selectedPriceId);

            // check sales datetime
            if ($eventProduct->start_sale_date->gt(now()) || $eventProduct->end_sale_date->lt(now())) {
                throw new \DomainException('Product sales are not available.');
            }

            if ($selectedPrice->start_sale_date->gt(now()) || $selectedPrice->end_sale_date->lt(now())) {
                throw new \DomainException('Product with this price sales are not available');
            }

            // check stock
            if ($selectedPrice->stock < $product->quantity) {
                throw new \DomainException('Product is not available');
            }
        }
    }
}
