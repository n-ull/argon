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
        \Illuminate\Support\Facades\DB::transaction(function () {
            $order = Order::with('orderItems')->find($this->orderId);

            if (! $order) {
                return;
            }

            if ($order->status === OrderStatus::PENDING && $order->expires_at <= now()) {
                $order->update(['status' => OrderStatus::EXPIRED]);

                foreach ($order->orderItems as $item) {
                    \Domain\ProductCatalog\Models\ProductPrice::where('id', $item->product_price_id)
                        ->decrement('quantity_sold', $item->quantity);
                }
            }
        });
    }
}
