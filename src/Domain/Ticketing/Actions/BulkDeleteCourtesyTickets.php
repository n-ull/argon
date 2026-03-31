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
        \Domain\Ticketing\Jobs\DeleteCourtesyTickets::dispatch($ticketIds);
    }

    public function asController(int $eventId, Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|string',
        ]);

        $ticketIds = [];
        $invitationIds = [];

        foreach ($validated['ids'] as $key) {
            if (str_starts_with($key, 'i-')) {
                $invitationIds[] = (int) substr($key, 2);
            } elseif (str_starts_with($key, 't-')) {
                $ticketIds[] = (int) substr($key, 2);
            } else {
                // Backward compatibility just in case or assume it's a ticket
                $ticketIds[] = (int) $key;
            }
        }

        // Security check for real tickets
        if (!empty($ticketIds)) {
            $ticketCount = Ticket::whereIn('id', $ticketIds)
                ->where('event_id', $eventId)
                ->where('is_courtesy', true)
                ->count();

            if ($ticketCount !== count($ticketIds)) {
                abort(403, 'Intento de eliminación no autorizado (tickets)');
            }
        }

        // Security check for invitations
        if (!empty($invitationIds)) {
            $invitationCount = \Domain\Ticketing\Models\TicketInvitation::whereIn('id', $invitationIds)
                ->where('event_id', $eventId)
                ->count();

            if ($invitationCount !== count($invitationIds)) {
                abort(403, 'Intento de eliminación no autorizado (invitaciones)');
            }
        }

        // Perform deletions
        if (!empty($ticketIds)) {
            \Domain\Ticketing\Jobs\DeleteCourtesyTickets::dispatch($ticketIds);
        }

        if (!empty($invitationIds)) {
            \Domain\Ticketing\Models\TicketInvitation::whereIn('id', $invitationIds)->delete();
        }

        return back()->with('message', flash_success('Eliminado exitosamente', 'Los registros seleccionados han sido eliminados.'));
    }
}
