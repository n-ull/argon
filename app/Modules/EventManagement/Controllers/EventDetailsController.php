<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event as EventModel;
use Domain\EventManagement\Resources\EventResource;
use Domain\ProductCatalog\Resources\ProductResource;
use Domain\ProductCatalog\Scopes\AvailableProductsScope;
use Inertia\Inertia;

class EventDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventModel $event)
    {
        $event->load(['organizer']);

        $products = $event->products()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->with([
                'product_prices' => function ($query) {
                    $query->withGlobalScope('available_prices', new \Domain\ProductCatalog\Scopes\AvailableProductPricesScope);
                },
            ])
            ->get();

        return Inertia::render('events/Details', [
            'event' => EventResource::make($event)->resolve(),
            'products' => ProductResource::collection($products)->resolve(),
        ]);
    }
}
