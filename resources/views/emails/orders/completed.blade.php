@component('mail::message')
# ¡Gracias por tu compra!

Hola **{{ $order->client->name }}**,

Tu pedido para el evento **{{ $order->event->name }}** ha sido procesado exitosamente.

@component('mail::button', ['url' => config('app.frontend_url').'/orders/'.$order->id])
Ver mi Pedido
@endcomponent

Si tienes alguna pregunta, no dudes en contactarnos.

Gracias,<br>
{{ config('app.name') }}
@endcomponent