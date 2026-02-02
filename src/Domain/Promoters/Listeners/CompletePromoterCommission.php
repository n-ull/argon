<?php

namespace Domain\Promoters\Listeners;

use Domain\Ordering\Events\OrderCompleted;
use Domain\Promoters\Events\CommissionCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompletePromoterCommission implements ShouldQueue
{
    public function handle(OrderCompleted $event): void
    {
        $commission = $event->order->commission;

        if ($commission) {
            CommissionCompleted::dispatch($commission);
        }
    }
}
