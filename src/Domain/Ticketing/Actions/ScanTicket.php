<?php

namespace Domain\Ticketing\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Events\TicketScanned;
use Domain\Ticketing\Resources\ScannedTicketResource;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use PragmaRX\Google2FA\Google2FA;

class ScanTicket
{
    use AsAction;

    public function handle(string $type, string $token, Event $event, ?string $totp)
    {
        $ticket = $event->tickets->where('token', $token)->first();

        if (! $ticket) {
            return response()->json([
                'message' => __('tickets.ticket_not_available')
            ]);
        }

        if ($type === 'static') {
            $response = ScannedTicketResource::make($ticket)->resolve();

            $ticket->markAsUsed();

            event(new TicketScanned($ticket, auth()->user()));

            return response()->json($response);
        }

        if ($type === 'dynamic') {
            $google2FA = app(Google2FA::class);

            $tokenValidation = $google2FA->verifyKey($token, $totp);

            if (! $tokenValidation) {
                return response()->json([
                    'message' => __('tickets.invalid_totp')
                ]);
            }

            $response = ScannedTicketResource::make($ticket)->resolve();

            $ticket->markAsUsed();

            event(new TicketScanned($ticket, auth()->user()));

            return response()->json($response);
        }
    }

    public function asController(Request $request, Event $event)
    {
        $request->validate([
            'type' => ['in:static,dynamic', 'required', 'string'],
            'token' => ['exists:tickets,token', 'required'],
            'totp' => ['nullable', 'string']
        ]);

        $userIsDoormen = $event->doormen()->where('user_id', auth()->user()->id)->where('is_active', true)->exists();

        if (! $userIsDoormen) {
            return response()->json([
                'message' => __('tickets.doormen_not_registered')
            ]);
        }


        return $this->handle($request['type'], $request['token'], $event, $request['totp']);
    }
}
