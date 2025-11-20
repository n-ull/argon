<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event as EventModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventModel $event)
    {
        $event->load(["products", "products.product_prices"]);

        return Inertia::render('events/Details', [
            'event' => $event
        ]);
    }
}
