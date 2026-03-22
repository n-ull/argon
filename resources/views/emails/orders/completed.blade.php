@component('mail::message')
# ¡Gracias por tu compra!

Hola **{{ $order->client->name }}**,

Tu pedido para el evento **{{ $order->event->title }}** ha sido procesado exitosamente.

@foreach ($order->tickets as $ticket)
@if($ticket->type == \Domain\Ticketing\Enums\TicketType::STATIC)
**{{ $ticket->product->name }}**

<div style="text-align: center;">
<img src="{{ $message->embedData(file_get_contents('https://api.qrserver.com/v1/create-qr-code/?size=150x150&data='.urlencode('st-'.$ticket->token)), 'ticket-qr-'.$ticket->token.'.png', 'image/png') }}" alt="QR Code" width="150" height="150" style="display: inline-block; margin: 10px auto;">
</div>

{{ $ticket->product->description }}
@endif
@endforeach


@component('mail::button', ['url' => config('app.frontend_url').'/orders/'.$order->id])
Ver mi Pedido
@endcomponent

Si tienes alguna pregunta, no dudes en contactarnos.

Gracias,<br>
{{ config('app.name') }}
@endcomponent