<?php

namespace Domain\Ticketing\Listeners;

use Domain\Ordering\Events\OrderCompleted;
use Domain\Ticketing\Jobs\GenerateTickets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateTicketsForOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $timeout = 30;

    public function handle(OrderCompleted $event): void
    {
        \Log::info('Generating tickets for completed order', [
            'order_id' => $event->order->id,
            'listener' => self::class,
        ]);

        GenerateTickets::dispatch($event->order->id);
    }
}
