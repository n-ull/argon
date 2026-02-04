<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Domain\Promoters\Models\Promoter;

class ManageEventController extends Controller
{
    public function dashboard(int $eventId)
    {
        $event = Event::findOrFail($eventId)->load('organizer');
        Gate::authorize('update', $event);

        $event->loadCount('products');
        $event->append('widget_stats');

        return Inertia::render('organizers/event/Dashboard', [
            'event' => $event,
        ]);
    }

    public function analytics(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');
        Gate::authorize('update', $event);

        return Inertia::render('organizers/event/Analytics', [
            'event' => $event,
        ]);
    }

    public function analyticsSales(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        Gate::authorize('update', $event);

        $period = $request->input('period', 'day');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = \Domain\Ordering\Actions\GetEventSalesAnalytics::run($event, $period, $startDate, $endDate);

        return response()->json($data);
    }

    public function analyticsPromoters(int $eventId)
    {
        $event = Event::findOrFail($eventId);
        Gate::authorize('update', $event);

        $data = \Domain\Promoters\Actions\GetPromoterSalesAnalytics::run($event);

        return response()->json($data);
    }

    public function products(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');
        Gate::authorize('update', $event);

        return Inertia::render('organizers/event/Products', [
            'event' => $event,
            'products' => $event->products()->with('product_prices')->orderBy('sort_order')->get(),
            'combos' => $event->combos()->with('items.productPrice.product')->get(),
        ]);
    }

    public function orders(Request $request, int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        $query = $event->orders()->with(['client:id,name,email', 'promoter'])->withCount('orderItems');

        // Search by client name, email, or reference ID
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('used_payment_gateway_snapshot', $request->input('payment_method'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Map frontend sort field to actual column
        $sortColumn = match ($sortBy) {
            'created_at' => 'created_at',
            'total' => 'subtotal', // or you might want to sort by a computed total
            'order_items_count' => 'order_items_count',
            default => 'created_at'
        };

        $query->orderBy($sortColumn, $sortDirection);

        return Inertia::render('organizers/event/Orders', [
            'event' => $event,
            'orders' => $query->paginate(10),
        ]);
    }

    // public function promoters(int $eventId)
    // {
    //     $event = Event::where('id', $eventId)->first()->load(['organizer', 'promoters' => function ($query) use ($eventId) {
    //         $query->withCount(['commissions' => function ($query) use ($eventId) {
    //             $query->where('event_id', $eventId)->completed();
    //         }]);
    //     }, 'promoterInvitations']);

    //     return Inertia::render('organizers/event/Promoters', [
    //         'event' => $event,
    //         'promoters' => \Domain\Promoters\Resources\PromoterResource::collection($event->promoters)->resolve(),
    //         'invitations' => $event->promoterInvitations,
    //     ]);
    // }

    public function settings(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');
        $event->load('taxesAndFees');

        // Get all taxes available for this organizer
        $availableTaxes = $event->organizer->taxesAndFees()->where('is_active', true)->get();

        return Inertia::render('organizers/event/Settings', [
            'event' => $event,
            'availableTaxes' => $availableTaxes,
        ]);
    }

    public function attendees(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Attendees', [
            'event' => $event,
        ]);
    }

    public function doormen(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Doormen', [
            'event' => $event,
        ]);
    }

    public function vouchers(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Vouchers', [
            'event' => $event,
        ]);
    }

    public function promoterStats(Request $request, Event $event, Promoter $promoter)
    {
        // Verify promoter belongs to the event's organizer
        if (! $promoter->organizers()->where('organizer_id', $event->organizer_id)->exists()) {
            abort(404);
        }

        $stats = $event->orders()
            ->where('referral_code', $promoter->referral_code)
            ->where('status', \Domain\Ordering\Enums\OrderStatus::COMPLETED)
            ->with(['orderItems.product'])
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems;
            })
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $items->sum('quantity'),
                    'total_sales' => $items->sum('total_price'),
                ];
            })
            ->values();

        return response()->json($stats);
    }
}
