<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event as EventModel;
use Domain\ProductCatalog\Scopes\AvailableProductsScope;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventModel $event)
    {
        $products = $event->products()->withGlobalScope('available', new AvailableProductsScope())->get();

        return Inertia::render('events/Details', [
            'event' => $event,
            'products' => $products
        ]);
    }
}
