<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Inertia\Inertia;
use Lorisleiva\Actions\Concerns\AsAction;

class ManageCourtesies
{
    use AsAction;

    public function handle()
    {
        //
    }

    public function asController(int $eventId)
    {
        $event = Event::findOrFail($eventId)->load('organizer');
        
        $tickets = $event->courtesies()->with(['user', 'product', 'givenBy'])->get()->map(function ($ticket) {
            $ticket->is_invitation = false;
            return $ticket;
        });

        $invitations = $event->ticketInvitations()->with(['product', 'givenBy'])
            ->whereNull('accepted_at')
            ->get()->map(function ($invitation) {
                $invitation->is_invitation = true;
                // Mock user object for the table
                $invitation->user = (object) [
                    'name' => explode('@', $invitation->email)[0],
                    'email' => $invitation->email
                ];
                return $invitation;
            });

        $merged = $tickets->concat($invitations)->sortByDesc('created_at')->values();

        $page = request()->get('page', 1);
        $perPage = 10;
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return Inertia::render('organizers/event/Courtesies', [
            'event' => $event,
            'courtesies' => $paginated,
            'products' => $event->products()->where('product_type', \Domain\ProductCatalog\Enums\ProductType::TICKET)->get()
        ]);
    }
}
