<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManageEventController extends Controller
{
    public function dashboard(int $eventId)
    {
        $event = Event::find($eventId)->load('organizer');
        $event->loadCount('products');
        $event->append('widget_stats');

        return Inertia::render('organizers/event/Dashboard', [
            'event' => $event,
        ]);
    }

    public function analytics(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Analytics', [
            'event' => $event,
        ]);
    }

    public function products(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Products', [
            'event' => $event,
            'products' => $event->products()->with('product_prices')->get(),
        ]);
    }

    public function orders(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Orders', [
            'event' => $event,
            'orders' => $event->orders()->with('client:id,name,email')->withCount('orderItems')->latest()->paginate(10),
        ]);
    }

    public function promoters(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Promoters', [
            'event' => $event,
        ]);
    }

    public function settings(int $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Settings', [
            'event' => $event,
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
}
