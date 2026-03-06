@component('mail::message')
# Recibiste un ticket de cortesía

Hola **{{ $ticket->user->name }}**,

Te han enviado un ticket de cortesía para el evento **{{ $ticket->event->name }}**.

@if($ticket->type == \Domain\Ticketing\Enums\TicketType::STATIC)
**{{ $ticket->product->name }}**

<div style="text-align: center;">
<img src="{{ $message->embedData(file_get_contents('https://api.qrserver.com/v1/create-qr-code/?size=150x150&data='.urlencode('st-'.$ticket->token)), 'ticket-qr-'.$ticket->token.'.png', 'image/png') }}" alt="QR Code" width="150" height="150" style="display: inline-block; margin: 10px auto;">
</div>
@endif

@component('mail::button', ['url' => config('app.frontend_url').'/tickets/'.$ticket->id])
Ver mi Ticket
@endcomponent

Atentamente,<br>
{{ config('app.name') }}
@endcomponent