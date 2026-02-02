<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event as EventModel;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EventIndexController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', EventModel::class);

        // Filter and search
        $query = EventModel::query();

        // Search title
        if (request()->has('search')) {
            $query->where('title', 'like', '%'.request('search').'%');
        }

        $events = $query->orderBy('start_date', 'desc')
            ->where(function ($query) {
                $query->where('end_date', '>=', now())
                    ->orWhere('end_date', null);
            })
            ->where('status', EventStatus::PUBLISHED)
            // ->where('is_featured', true)
            ->paginate(10);

        return Inertia::render('events/Index', [
            'events' => Inertia::scroll($events),
        ]);
    }
}
