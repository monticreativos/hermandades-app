<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 3mm 2mm 4mm;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5pt;
            color: #0f172a;
            width: 100%;
        }
        .center { text-align: center; }
        .muted { color: #64748b; font-size: 7.5pt; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #94a3b8; margin: 3mm 0; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 2mm; }
        table.lines td { padding: 1mm 0; vertical-align: top; font-size: 8pt; }
        table.lines td.qty { width: 10%; text-align: right; white-space: nowrap; }
        table.lines td.precio { width: 22%; text-align: right; white-space: nowrap; }
        .escudo { max-height: 18mm; max-width: 18mm; margin: 0 auto 2mm; display: block; }
        .total { font-size: 11pt; font-weight: bold; margin-top: 2mm; }
        .dorado { color: #8b6914; }
    </style>
</head>
<body>
    @if (!empty($escudoDataUri))
        <img src="{{ $escudoDataUri }}" alt="" class="escudo" />
    @endif
    <div class="center bold" style="font-size: 9.5pt;">{{ $hermandad?->nombre_corto ?: $hermandad?->nombre_hermandad ?: 'Hermandad' }}</div>
    <div class="center muted">Ticket de venta (TPV)</div>
    <div class="line"></div>
    <div class="muted">{{ $venta->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div>
    <div><span class="muted">Folio:</span> <span class="bold">{{ $venta->folio }}</span></div>
    @if ($venta->venta_anonima)
        <div><span class="muted">Cliente:</span> Público general</div>
    @elseif ($venta->hermano)
        <div><span class="muted">Hermano:</span> n.º {{ $venta->hermano->numero_hermano }}</div>
        <div class="muted" style="font-size: 7.5pt;">{{ $venta->hermano->nombreCompleto() }}</div>
        @if ($venta->hermano->cuentaContable)
            <div class="muted">Cta. {{ $venta->hermano->cuentaContable->codigo }}</div>
        @endif
    @endif
    @if ($venta->user)
        <div class="muted">Cajero: {{ $venta->user->name }}</div>
    @endif
    <div class="line"></div>
    <table class="lines">
        @foreach ($venta->lineas as $l)
            @php
                $nombre = $l->producto?->nombre ?? 'Artículo';
            @endphp
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($nombre, 28) }}</td>
                <td class="qty">{{ $l->cantidad }}×</td>
                <td class="precio">{{ number_format((float) $l->total_linea, 2, ',', '.') }} €</td>
            </tr>
        @endforeach
    </table>
    <div class="line"></div>
    <div class="muted">Forma de pago:
        @switch($venta->metodo_pago)
            @case('efectivo') Efectivo @break
            @case('tarjeta') Tarjeta @break
            @case('bizum') Bizum @break
            @default {{ $venta->metodo_pago }}
        @endswitch
    </div>
    @if ((float) $venta->total_iva > 0)
        <div class="muted">Base {{ number_format((float) $venta->total_base, 2, ',', '.') }} € · IVA {{ number_format((float) $venta->total_iva, 2, ',', '.') }} €</div>
    @endif
    <div class="total center dorado">TOTAL {{ number_format((float) $venta->importe_total, 2, ',', '.') }} €</div>
    <div class="line"></div>
    <div class="center muted" style="font-size: 7pt;">Gracias por su colaboración</div>
</body>
</html>
