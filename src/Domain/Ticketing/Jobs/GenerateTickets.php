<?php

namespace Domain\Ticketing\Jobs;

use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use PragmaRX\Google2FA\Google2FA;

class GenerateTickets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $google2fa;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $orderId,
    ) {
        $this->google2fa = app(Google2FA::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::find($this->orderId);

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

    public function generateToken(TicketType $type)
    {
        if ($type === TicketType::STATIC) {
            return fake()->unique()->numerify('T-######');
        }

        return $this->google2fa->generateSecretKey(16);
    }

    public function generateTicket(OrderItem $orderItem)
    {
        for ($i = 0; $i < $orderItem->quantity; $i++) {
            Ticket::create([
                'token' => $this->generateToken($orderItem->product->ticket_type),
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
