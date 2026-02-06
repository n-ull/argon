<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Enums\TicketStatus;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class ScanTicket
{
    use AsAction;

    public function handle(string $type, string $token, Event $event)
    {
        if ($type === 'static') {
            $ticket = $event->tickets->where('token', $token)->first();

            if (! $ticket) {
                return response()->json([
                    'message' => __('tickets.ticket_not_available')
                ]);
            }

            $usedAt = now();
            $response = [
                'status' => $ticket->status,
                'used_at' => $usedAt->toIso8601String()
            ];

            $ticket->status = TicketStatus::USED;
            $ticket->used_at = $usedAt;

            $ticket->save();

            return response()->json($response);
        }

        if ($type === 'dynamic') {

        }
    }

    public function asController(Request $request, int $eventId)
    {
        $request->validate([
            'type' => ['in:static,dynamic', 'required', 'string'],
            'token' => ['exists:tickets,token', 'required']
        ]);

        $event = Event::findOrFail($eventId);
        $userIsDoormen = $event->doormen()->where('user_id', auth()->user()->id)->where('is_active', true)->exists();

        if (! $userIsDoormen) {
            return response()->json([
                'message' => __('tickets.doormen_not_registered')
            ]);
        }


        return $this->handle($request['type'], $request['token'], $event);
    }
}
