<?php

namespace App\Modules\OrganizerManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Models\Order;
use Domain\OrganizerManagement\Models\Organizer;
use Inertia\Inertia;

class ManageOrganizations extends Controller
{
    public function show(Organizer $organizer)
    {
        $last_orders = Order::whereHas('event', function ($query) use ($organizer) {
            $query->where('organizer_id', $organizer->id);
        })->orderBy('updated_at', 'desc')->limit(5)->with('event:id,title')->get();
        $last_events = Event::where('organizer_id', $organizer->id)->orderBy('created_at', 'desc')->limit(5)->get();

        return Inertia::render('organizers/Dashboard', [
            'organizer' => $organizer,
            'orders_count' => Order::whereHas('event', function ($query) use ($organizer) {
                $query->where('organizer_id', $organizer->id);
            })->count(),
            'events_count' => $organizer->events()->count(),
            'last_orders' => $last_orders,
            'last_events' => $last_events,
        ]);
    }

    public function settings(Organizer $organizer)
    {
        return Inertia::render('organizers/Settings', [
            'organizer' => $organizer,
        ]);
    }

    public function events(Organizer $organizer)
    {
        return Inertia::render('organizers/Events', [
            'organizer' => $organizer,
            'events' => $organizer->events()->paginate(10),
        ]);
    }

    public function cooperators(Organizer $organizer)
    {
        return Inertia::render('organizers/Cooperators', [
            'organizer' => $organizer,
        ]);
    }
}
