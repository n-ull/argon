<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Actions\SaveEventQuestions;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EventQuestionsController extends Controller
{
    public function index(int $eventId)
    {
        $event = Event::findOrFail($eventId)->load('organizer');
        Gate::authorize('update', $event);

        $questions = $event->questions()->with('product')->get();
        $products = $event->products()->with('product_prices')->get();

        return Inertia::render('organizers/event/Questions', [
            'event' => $event,
            'questions' => $questions,
            'products' => $products,
        ]);
    }

    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        Gate::authorize('update', $event);

        $validated = $request->validate([
            'applies_to' => ['required', 'in:order,product'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'is_active' => ['required', 'boolean'],
            'fields' => ['required', 'array'],
            'fields.*.id' => ['required', 'string'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'in:text,number,select,checkbox,radio'],
            'fields.*.required' => ['required', 'boolean'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.*' => ['string', 'max:255'],
        ]);

        SaveEventQuestions::run($event, $validated);

        return back()->with('flash', [
            'message' => [
                'summary' => 'Questions saved',
                'detail' => 'The question form has been saved successfully.',
                'type' => 'success',
            ],
        ]);
    }
}
