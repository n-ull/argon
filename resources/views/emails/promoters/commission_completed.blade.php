@component('mail::message')
# ¡Has realizado una nueva venta!

Hola **{{ $commission->promoter->name }}**,

Se ha completado una nueva venta para el evento **{{ $commission->event->name }}**.

**Detalles de la comisión:**
- **Monto ganado:** ${{ number_format($commission->amount, 2) }}
- **Pedido:** #{{ $commission->order->reference_id }}

@component('mail::button', ['url' => route('promoters.dashboard')])
Ir a mi Panel
@endcomponent

Sigue compartiendo tu enlace para ganar más.

Gracias,<br>
{{ config('app.name') }}
@endcomponent