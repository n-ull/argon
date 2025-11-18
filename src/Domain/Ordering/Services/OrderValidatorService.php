<?php

namespace Domain\Ordering\Services;

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
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

    public function validateOrder(CreateOrderData $orderData): void
    {
        $event = Event::findOrFail($orderData->eventId);

        if ($event->status !== EventStatus::PUBLISHED) {
            throw new \DomainException('Event is not published.');
        }

        // todo: this could be separated into different validation processes
        foreach ($orderData->products as $product) {
            $eventProduct = $event->products->firstWhere('id', $product->productId);

            if ($eventProduct == null) {
                throw new \DomainException('Event product doesn\'t exist');
            }

            $selectedPrice = $eventProduct->product_prices->firstWhere('id', $product->selectedPriceId);

            if ($selectedPrice == null) {
                throw new \DomainException('Selected price doesn\'t exist');
            }

            // check sales date

            $productStartSaleDate = $eventProduct->start_sale_date ?? $event->start_date;
            $productEndSaleDate = $eventProduct->end_sale_date ?? $event->end_date;

            if ($productStartSaleDate->gt(now()) || ($productEndSaleDate && $productEndSaleDate->lt(now()))) {
                throw new \DomainException('Product sales are not available.');
            }

            $priceStartSaleDate = $selectedPrice->start_sale_date ?? $event->start_date;
            $priceEndSaleDate = $selectedPrice->end_sale_date ?? $event->end_date;

            if ($priceStartSaleDate->gt(now()) || ($priceEndSaleDate && $priceEndSaleDate->lt(now()))) {
                throw new \DomainException('Product with this price sales are not available');
            }

            // check stock
            if ($selectedPrice->stock < $product->quantity) {
                throw new \DomainException('Product is not available');
            }
        }
    }
}
