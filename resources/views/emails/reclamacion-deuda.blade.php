<x-mail::message>
# Recordatorio de deuda

Estimado cofrade,

Le enviamos un extracto de su subcuenta contable (adjunto en PDF) con el detalle de movimientos y saldo acumulado a fecha de emisión.

Rogamos regularice su situación con tesorería o a través del portal del hermano (sección «Pagos»), salvo que ya hubiera abonado el importe y esté pendiente de conciliación.

@if ($hermandad->email_contacto)
Para cualquier aclaración: {{ $hermandad->email_contacto }}
@endif

Saludos cordiales,<br>
{{ $hermandad->nombre_corto ?: $hermandad->nombre_hermandad }}

<x-mail::subcopy>
Hermano n.º {{ $hermano->numero_hermano }} — {{ $hermano->nombreCompleto() }}
</x-mail::subcopy>
</x-mail::message>
