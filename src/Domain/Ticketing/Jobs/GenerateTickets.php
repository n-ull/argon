<?php

namespace Domain\Ticketing\Jobs;

use Domain\Ordering\Models\Order;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use PragmaRX\Google2FA\Google2FA;

class GenerateTickets implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Google2FA $google2fa,
        public int $orderId
    ) {
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

        foreach ($order->orderItems as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $ticket = Ticket::create([
                    'token' => $this->google2fa->generateSecretKey(),
                    'event_id' => $order->event_id,
                    'product_id' => $item->product_id,
                    'type' => TicketType::DYNAMIC,
                    'status' => TicketStatus::ACTIVE,
                    'transfers_left' => 0,
                    'is_courtesy' => false,
                    'used_at' => null,
                    'expired_at' => null,
                ]);
            }
        }
    }
}
