<?php

namespace Domain\Ordering\Jobs;

use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            return;
        }

        if ($order->status === OrderStatus::PENDING && $order->expires_at <= now()) {
            $order->update(['status' => OrderStatus::EXPIRED]);
        }
    }
}
