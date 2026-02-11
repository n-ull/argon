<?php

namespace Domain\Ticketing\Jobs;

use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;


class GenerateTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['orderItems'])->findOrFail($this->orderId);

        if (! $order) {
            Log::warning("GenerateTickets job failed: Order {$this->orderId} not found");

            return;
        }

        if (! $order->isPaid) {
            Log::warning("GenerateTickets job failed: Order {$this->orderId} is not paid");

            return;
        }

        \DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $orderItem) {
                if ($orderItem->product_id !== null && $orderItem->combo_id === null) {
                    if ($orderItem->product->product_type === ProductType::TICKET) {
                        $this->generateTicket($orderItem);
                    }
                }

                if ($orderItem->combo_id !== null) {
                    foreach ($orderItem->combo->items as $comboItem) {
                        $this->generateTicketFromCombo($orderItem, $comboItem);
                    }
                }
            }
        });
    }

    public function generateTicket(OrderItem $orderItem)
    {
        if (! $orderItem->product) {
            Log::warning("GenerateTickets: OrderItem {$orderItem->id} has no product. Skipper ticket generation.");
            return;
        }

        for ($i = 0; $i < $orderItem->quantity; $i++) {
            Ticket::create([
                'token' => \Domain\Ticketing\Facades\TokenGenerator::generate($orderItem->product->ticket_type ?? TicketType::STATIC),
                'event_id' => $orderItem->order->event_id,
                'product_id' => $orderItem->product_id,
                'order_id' => $orderItem->order_id,
                'user_id' => $orderItem->order->user_id,
                'type' => $orderItem->product->ticket_type ?? TicketType::STATIC ,
                'status' => TicketStatus::ACTIVE,
                'transfers_left' => $orderItem->product->transfers_left ?? 0,
                'is_courtesy' => false,
                'used_at' => null,
                'expired_at' => null,
            ]);
        }
    }

    public function generateTicketFromCombo(OrderItem $orderItem, \Domain\ProductCatalog\Models\ComboItem $comboItem)
    {
        if (! $comboItem->productPrice || ! $comboItem->productPrice->product) {
            Log::warning("GenerateTickets: ComboItem {$comboItem->id} has no product. Skipper ticket generation.");

            return;
        }

        $totalQuantity = $orderItem->quantity * $comboItem->quantity;
        $product = $comboItem->productPrice->product;

        for ($i = 0; $i < $totalQuantity; $i++) {
            Ticket::create([
                'token' => \Domain\Ticketing\Facades\TokenGenerator::generate($product->ticket_type ?? TicketType::STATIC),
                'event_id' => $orderItem->order->event_id,
                'product_id' => $product->id,
                'order_id' => $orderItem->order_id,
                'user_id' => $orderItem->order->user_id,
                'type' => $product->ticket_type ?? TicketType::STATIC ,
                'status' => TicketStatus::ACTIVE,
                'transfers_left' => $product->transfers_left ?? 0,
                'is_courtesy' => false,
                'used_at' => null,
                'expired_at' => null,
            ]);
        }
    }
}
