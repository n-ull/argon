<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event as EventModel;
use Domain\EventManagement\Resources\EventResource;
use Domain\ProductCatalog\Resources\ProductResource;
use Domain\ProductCatalog\Scopes\AvailableProductsScope;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EventDetailsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EventModel $event)
    {
        Gate::authorize('view', $event);

        $event->load(['organizer', 'taxesAndFees']);

        $products = $event->products()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->with([
                'product_prices' => function ($query) {
                    $query->withGlobalScope('available_prices', new \Domain\ProductCatalog\Scopes\AvailableProductPricesScope)
                        ->orderBy('product_prices.sort_order');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('sort_order')
            ->get()
            ->each(function ($product) use ($event) {
                // Manually set the event relation to share the already loaded taxesAndFees
                $product->setRelation('event', $event);

                // Ensure prices have access to their parent product (which has the event)
                $product->product_prices->each(function ($price) use ($product) {
                    $price->setRelation('product', $product);
                });
            });

        // Update unique visitors statistics plus one
        $ipAddress = request()->ip();
        $cacheKey = 'event_visitor_'.$event->id.'_'.md5($ipAddress);

        if (! cache()->has($cacheKey)) {
            $event->statistics()->update([
                'unique_visitors' => $event->statistics->unique_visitors + 1,
            ]);
            cache()->put($cacheKey, true, now()->addMinutes(1));
        }

        if (request()->has('referr')) {
            $code = request()->input('referr');
            $promoter = \Domain\Promoters\Models\Promoter::where('referral_code', $code)->first();

            if ($promoter && $promoter->events()
                ->where('event_id', $event->id)
                ->where('promoter_events.enabled', true)
                ->exists()) {
                session(['referral_code_'.$event->id => $code]);
            }
        }

        return Inertia::render('events/Details', [
            'event' => EventResource::make($event)->resolve(),
            'products' => ProductResource::collection($products)->resolve(),
            'userIsOrganizer' => auth()->check() && $event->organizer->users->contains(auth()->user()->id),
            'referralCode' => session('referral_code_'.$event->id),
        ]);
    }
}
