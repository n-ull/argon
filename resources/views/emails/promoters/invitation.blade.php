@component('mail::message')
# ¡Has sido invitado a ser un promotor!

Has sido invitado a promocionar los eventos de **{{ $companyName }}**.

@component('mail::button', ['url' => route('promoters.invitations.show', $invitation->token)])
Aceptar Invitación
@endcomponent

Si no esperabas esta invitación, puedes ignorar este correo.

Gracias,<br>
{{ config('app.name') }}
@endcomponent