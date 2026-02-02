<?php

namespace Domain\Ordering\Listeners;

use App\Mail\OrderCompleted;
use Domain\Ordering\Events\OrderCompleted as OrderCompletedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderCompletedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCompletedEvent $event): void
    {
        Mail::to($event->order->client)->send(new OrderCompleted($event->order));
    }
}
