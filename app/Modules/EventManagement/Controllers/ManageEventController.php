<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManageEventController extends Controller
{
    public function dashboard(Organizer $organizer, $eventId)
    {
        $event = Event::where('id', $eventId)->first()->load('organizer');

        return Inertia::render('organizers/event/Dashboard', [
            'event' => $event,
        ]);
    }
}
