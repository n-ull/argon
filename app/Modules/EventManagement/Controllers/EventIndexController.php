<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event as EventModel;
use Inertia\Inertia;

class EventIndexController extends Controller
{
    public function index()
    {
        $events = EventModel::orderBy('start_date', 'desc')
            ->where('start_date', '>=', now())
            ->where('status', EventStatus::PUBLISHED)
            ->where('is_featured', true)
            ->paginate(10);

        return Inertia::render('events/Index', [
            'events' => Inertia::scroll($events),
        ]);
    }
}
