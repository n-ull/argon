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

        $latestEvents = Event::whereIn('organizer_id', $organizationIds)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'latestEvents' => $latestEvents,
            'organizers' => $organizers,
        ]);
    }
}
