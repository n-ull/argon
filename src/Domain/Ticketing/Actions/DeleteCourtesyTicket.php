<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCourtesyTicket
{
    use AsAction;

    public function asController(int $eventId, int $courtesyId, Request $request)
    {
        $isInvitation = $request->boolean('is_invitation');

        if ($isInvitation) {
            $invitation = \Domain\Ticketing\Models\TicketInvitation::where('id', $courtesyId)
                ->where('event_id', $eventId)
                ->firstOrFail();
            
            $invitation->delete();
        } else {
            $ticket = Ticket::where('id', $courtesyId)
                ->where('event_id', $eventId)
                ->where('is_courtesy', true)
                ->firstOrFail();

            \Domain\Ticketing\Jobs\DeleteCourtesyTicket::dispatch($ticket);
        }

        return back()->with('message', flash_success('Eliminado exitosamente', 'El registro ha sido eliminado.'));
    }
}
