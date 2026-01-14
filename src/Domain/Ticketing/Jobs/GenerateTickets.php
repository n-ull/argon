<?php

namespace Domain\Ticketing\Jobs;

use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\Ticketing\Enums\TicketStatus;
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
        $order = Order::findOrFail($this->orderId);

        if (!$order) {
            Log::warning("GenerateTickets job failed: Order {$this->orderId} not found");

            return;
        }

        if (!$order->isPaid) {
            Log::warning("GenerateTickets job failed: Order {$this->orderId} is not paid");

            return;
        }

        \DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $item) {
                $this->generateTicket($item);
            }
        });
    }

    public function generateTicket(OrderItem $orderItem)
    {
        for ($i = 0; $i < $orderItem->quantity; $i++) {
            Ticket::create([
                'token' => \Domain\Ticketing\Facades\TokenGenerator::generate($orderItem->product->ticket_type),
                'event_id' => $orderItem->order->event_id,
                'product_id' => $orderItem->product_id,
                'order_id' => $orderItem->order_id,
                'user_id' => $orderItem->order->user_id,
                'type' => $orderItem->product->ticket_type,
                'status' => TicketStatus::ACTIVE,
                'transfers_left' => 0,
                'is_courtesy' => false,
                'used_at' => null,
                'expired_at' => null,
            ]);
        }
    }
}
