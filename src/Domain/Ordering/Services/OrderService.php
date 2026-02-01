<?php

namespace Domain\Ordering\Services;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\OrderItemData;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Events\OrderCompleted;
use Domain\Ordering\Events\OrderCreated;
use Domain\Ordering\Exceptions\OrderAlreadyCompletedException;
use Domain\Ordering\Exceptions\OrderNotFoundException;
use Domain\Ordering\Models\Order;
use Domain\ProductCatalog\Models\ProductPrice;

class OrderService
{
    public function __construct(
        private OrderValidatorService $orderValidatorService,
        private PriceCalculationService $priceCalculationService,
        private ReferenceIdService $referenceIdService,
        private \Illuminate\Database\DatabaseManager $databaseManager
    ) {
    }

    public function createPendingOrder(CreateOrderData $orderData): Order
    {
        return $this->databaseManager->transaction(function () use ($orderData) {
            // Load event with relationships
            $event = Event::with(['products.product_prices', 'taxesAndFees', 'organizer.settings'])
                ->find($orderData->eventId);

            if (! $event) {
                throw new \DomainException("Event doesn't exist.");
            }

            // Check for existing pending order
            if ($orderData->userId) {
                $pendingOrder = Order::where('user_id', $orderData->userId)
                    ->where('status', OrderStatus::PENDING)
                    ->where('expires_at', '>', now())
                    ->first();

                if ($pendingOrder) {
                    throw new \Domain\Ordering\Exceptions\OrderAlreadyPendingException($pendingOrder->id);
                }
            }

            // Validate order
            $this->orderValidatorService->validateOrder($orderData);

            // Prepare order items with pricing
            $orderItems = $this->prepareOrderItems($orderData->items);

            // Calculate prices with snapshots
            $priceBreakdown = $this->priceCalculationService->calculate(
                $orderItems,
                $event,
                $orderData->gateway ?? null
            );

            $status = OrderStatus::PENDING;

            // Create order with all calculated values
            $order = $event->orders()->create([
                'user_id' => $orderData->userId,
                'subtotal' => $priceBreakdown->subtotal,
                'taxes_total' => $priceBreakdown->taxesTotal,
                'fees_total' => $priceBreakdown->feesTotal,
                'total_before_additions' => $priceBreakdown->totalBeforeAdditions,
                'total_gross' => $priceBreakdown->totalGross,
                'items_snapshot' => $priceBreakdown->itemsSnapshot,
                'taxes_snapshot' => $priceBreakdown->taxesSnapshot,
                'fees_snapshot' => $priceBreakdown->feesSnapshot,
                'reference_id' => $this->referenceIdService->create(),
                'organizer_raise_method_snapshot' => $event->organizer->settings->raise_money_method ?? null,
                'used_payment_gateway_snapshot' => $orderData->gateway ?? null,
                'expires_at' => now()->addMinutes(15),
                'status' => $status,
                'referral_code' => $orderData->referral_code ?? null,
            ]);

            // Create order items and increment quantity_sold
            foreach ($priceBreakdown->itemsSnapshot as $itemSnapshot) {
                $order->orderItems()->create([
                    'product_id' => $itemSnapshot['product_id'],
                    'product_price_id' => $itemSnapshot['product_price_id'],
                    'quantity' => $itemSnapshot['quantity'],
                    'unit_price' => $itemSnapshot['unit_price'],
                ]);

                ProductPrice::where('id', $itemSnapshot['product_price_id'])
                    ->increment('quantity_sold', $itemSnapshot['quantity']);
            }

            event(new OrderCreated($order, $orderData->referral_code));

            if ($status === OrderStatus::COMPLETED) {
                event(new OrderCompleted($order));
            } else {
                \Domain\Ordering\Jobs\ExpireOrder::dispatch($order->id)
                    ->delay(now()->addMinutes(15));
            }

            return $order;
        });
    }

    /**
     * Prepare order items with pricing information
     *
     * @param  array  $items  Array of CreateOrderProductData
     * @return OrderItemData[]
     */
    private function prepareOrderItems(array $items): array
    {
        return array_map(function ($item) {
            $productPrice = ProductPrice::findOrFail($item['productPriceId']);

            return new OrderItemData(
                productId: $item['productId'],
                productPriceId: $item['productPriceId'],
                quantity: $item['quantity'],
                unitPrice: $productPrice->price,
                productName: $productPrice->product->name,
                productPriceLabel: $productPrice->label
            );
        }, $items);
    }

    /**
     * Summary of completePendingOrder
     *
     * @throws OrderNotFoundException
     * @throws OrderAlreadyCompletedException
     * @throws \DomainException
     */
    public function completePendingOrder(?int $orderId = null, ?string $referenceId = null): Order
    {
        if (! $orderId && ! $referenceId) {
            throw new \DomainException('Order ID or Reference ID is required.');
        }

        $order = Order::find($orderId) ?? Order::where('reference_id', $referenceId)->first();

        if (! $order) {
            throw new OrderNotFoundException;
        }

        if ($order->status == OrderStatus::COMPLETED) {
            throw new OrderAlreadyCompletedException($order->reference_id);
        }

        $order->update([
            'status' => OrderStatus::COMPLETED,
        ]);

        event(new OrderCompleted($order));

        return $order;
    }
}
