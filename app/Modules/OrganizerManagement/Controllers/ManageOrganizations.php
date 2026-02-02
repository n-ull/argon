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
            'organizer' => $organizer->load(['settings', 'taxesAndFees', 'owner.mercadoPagoAccount']),
        ]);
    }

    public function events(\Illuminate\Http\Request $request, Organizer $organizer)
    {
        $query = $organizer->events();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', '!=', \Domain\EventManagement\Enums\EventStatus::ARCHIVED);
        }

        // Sorting logic
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $sortColumn = match ($sortBy) {
            'title' => 'title',
            'start_date' => 'start_date',
            'created_at' => 'created_at',
            default => 'created_at'
        };

        $query->orderBy($sortColumn, $sortDirection);

        return Inertia::render('organizers/Events', [
            'organizer' => $organizer,
            'events' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_direction']),
        ]);
    }

    public function cooperators(Organizer $organizer)
    {
        $cooperators = $organizer->users()->get()->except($organizer->owner_id);

        return Inertia::render('organizers/Cooperators', [
            'organizer' => $organizer,
            'cooperators' => $cooperators,
            'userIsOwner' => $organizer->owner_id === auth()->id(),
        ]);
    }
}
