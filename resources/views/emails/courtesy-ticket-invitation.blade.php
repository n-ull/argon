@component('mail::message')
# Hola!

Has recibido una invitación para obtener tu ticket de cortesía para el evento **{{ $invitation->event->title }}**.

Para recibir tus tickets, por favor regístrate haciendo clic en el siguiente botón:

@component('mail::button', ['url' => $url])
Aceptar Invitación
@endcomponent

Esta invitación expirará el {{ $invitation->expires_at->format('d/m/Y H:i') }}.

Si no esperabas esta invitación, puedes ignorar este correo.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
