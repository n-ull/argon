<?php

namespace App\Modules\OrganizerManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $organizationIds = $user->organizers()->pluck('organizer_id');

        $organizers = $user->organizers()->get();

        // Build the query with eager loading
        $query = Event::with('organizer')->whereIn('organizer_id', $organizationIds);

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        // Apply organization filter
        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->organizer_id);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // By default, exclude archived events
            $query->whereNot('status', 'archived');
        }

        // Get per_page value with default of 10
        $perPage = $request->input('per_page', 10);

        // Validate per_page is one of the allowed values
        if (! in_array($perPage, [10, 20, 30, 50])) {
            $perPage = 10;
        }

        // Get paginated events
        $events = $query
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Dashboard', [
            'events' => $events,
            'organizers' => $organizers,
            'filters' => $request->only(['search', 'organizer_id', 'status']),
        ]);
    }
}
