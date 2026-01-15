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
        foreach ($orderData->items as $product) {
            $productId = is_array($product) ? $product['productId'] : $product->productId;
            $productPriceId = is_array($product) ? $product['productPriceId'] : $product->productPriceId;
            $quantity = is_array($product) ? $product['quantity'] : $product->quantity;

            $eventProduct = $event->products->firstWhere('id', $productId);

            if ($eventProduct == null) {
                throw new \DomainException('Event product doesn\'t exist');
            }

            $selectedPrice = $eventProduct->product_prices->firstWhere('id', $productPriceId);

            if ($selectedPrice == null) {
                throw new \DomainException('Selected price doesn\'t exist');
            }

            // check sales date
            $productStartSaleDate = $eventProduct->start_sale_date ?? $event->start_date;
            $productEndSaleDate = $eventProduct->end_sale_date ?? $event->end_date;

            // Product start sale date check
            if ($eventProduct->hide_before_sale_start_date && $productStartSaleDate && $productStartSaleDate->gt(now())) {
                throw new \DomainException('Product sales are not available.');
            }

            // Product end sale date check
            if ($eventProduct->hide_after_sale_end_date && $productEndSaleDate && $productEndSaleDate->lt(now())) {
                throw new \DomainException('Product sales are not available.');
            }

            $priceStartSaleDate = $selectedPrice->start_sale_date ?? $productStartSaleDate;
            $priceEndSaleDate = $selectedPrice->end_sale_date ?? $productEndSaleDate;

            // Price start sale date check (Price doesn't have its own hide flag, it uses product's)
            if ($eventProduct->hide_before_sale_start_date && $priceStartSaleDate && $priceStartSaleDate->gt(now())) {
                throw new \DomainException('Product with this price sales are not available');
            }

            // Price end sale date check
            if ($eventProduct->hide_after_sale_end_date && $priceEndSaleDate && $priceEndSaleDate->lt(now())) {
                throw new \DomainException('Product with this price sales are not available');
            }

            // check stock
            if ($selectedPrice->stock !== null && ($selectedPrice->stock - $selectedPrice->quantity_sold) < $quantity) {
                throw new \DomainException('Product is not available');
            }
        }
    }
}
