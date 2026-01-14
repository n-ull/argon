<?php

namespace Domain\Ticketing\Actions;

use Domain\Ticketing\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Pluralizer;
use Lorisleiva\Actions\Concerns\AsAction;

class BulkDeleteCourtesyTickets
{
    use AsAction;

    public function handle(array $ticketIds)
    {
        Ticket::whereIn('id', $ticketIds)->delete();
    }

    public function asController(int $eventId, Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:tickets,id',
        ]);

        // Security check: ensure all tickets belong to this event and are courtesies
        $count = Ticket::whereIn('id', $validated['ids'])
            ->where('event_id', $eventId)
            ->where('is_courtesy', true)
            ->count();

        if ($count !== count($validated['ids'])) {
            abort(403, 'Unauthorized deletion attempt.');
        }

        $this->handle($validated['ids']);

        return back()->with('message', flash_success('Tickets deleted successfully', count($validated['ids']) . ' courtesy tickets deleted.'));
    }
}
