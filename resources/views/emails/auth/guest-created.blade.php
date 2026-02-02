@component('mail::message')
# ¡Bienvenido a {{ config('app.name') }}!

Tu cuenta ha sido creada exitosamente. Para acceder, utiliza las siguientes credenciales:

**Email:** {{ $user->email }}
<br>
**Contraseña:** {{ $password }}

@component('mail::button', ['url' => config('app.frontend_url').'/login'])
Iniciar Sesión
@endcomponent

Si tienes alguna pregunta, no dudes en contactarnos.

Gracias,<br>
{{ config('app.name') }}
@endcomponent