<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; }
        h1 { margin: 0 0 6px; font-size: 18px; }
        .muted { color: #64748b; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; }
        th { background: #f8fafc; text-align: left; }
    </style>
</head>
<body>
    <h1>Cuadrante de Relevos — {{ $cuadrilla->nombre }}</h1>
    <p class="muted">Paso {{ strtoupper($cuadrilla->paso) }} · Salida {{ $relevo->fecha_salida?->format('d/m/Y') }}</p>
    <table>
        <thead><tr><th>Punto</th><th>Hora desde</th><th>Hora hasta</th><th>Costalero</th><th>Turno</th><th>Notas</th></tr></thead>
        <tbody>
            @foreach($relevo->detalles as $d)
                <tr>
                    <td>{{ $d->punto }}</td>
                    <td>{{ $d->hora_desde }}</td>
                    <td>{{ $d->hora_hasta }}</td>
                    <td>{{ $d->hermano?->nombreCompleto() ?? '—' }}</td>
                    <td>{{ $d->turno ?? '—' }}</td>
                    <td>{{ $d->notas ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
