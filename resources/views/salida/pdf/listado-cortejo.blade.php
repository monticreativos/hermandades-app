<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Listado de cofradía</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #0f172a; margin: 20px; }
        h1 { font-size: 13pt; margin: 0 0 4px; color: #0f172a; }
        .meta { font-size: 8pt; color: #64748b; margin-bottom: 16px; }
        h2 { font-size: 10pt; background: #0f172a; color: #fff; padding: 6px 8px; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 4px 6px; text-align: left; }
        th { font-size: 7pt; text-transform: uppercase; color: #64748b; background: #f8fafc; }
        .n { font-family: DejaVu Sans Mono, monospace; font-weight: bold; width: 12%; }
        .pie { margin-top: 20px; font-size: 7pt; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $hermandad?->nombre_hermandad ?? $hermandad?->nombre_corto ?? 'Hermandad' }}</h1>
    <p class="meta">Listado de cofradía — Ejercicio {{ $ejercicio->año }} · {{ $total }} cofrades</p>

    @foreach ($tramosAgrupados as $tramo => $lista)
        <h2>Tramo {{ $tramo ?: 'Sin tramo' }} ({{ $lista->count() }})</h2>
        <table>
            <thead>
                <tr>
                    <th class="n">N.º</th>
                    <th>Apellidos, nombre</th>
                    <th>Puesto</th>
                    <th>Insignia</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lista as $pap)
                    <tr>
                        <td class="n">{{ $pap->hermano->numero_hermano }}</td>
                        <td>{{ $pap->hermano->nombreCompleto() }}</td>
                        <td>{{ $pap->puesto }}</td>
                        <td>{{ $pap->insignia?->nombre ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <p class="pie">Generado el {{ now()->format('d/m/Y H:i') }} — Uso interno / celadores</p>
</body>
</html>
