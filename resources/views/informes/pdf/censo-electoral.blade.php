<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Censo electoral de votantes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #0f172a; margin: 22px; }
        h1 { font-size: 14pt; text-align: center; margin: 0 0 4px; color: #0f172a; letter-spacing: 0.04em; }
        .sub { text-align: center; font-size: 8.5pt; color: #64748b; margin-bottom: 6px; }
        .bar { height: 3px; background: linear-gradient(90deg, #c6a16a 0%, #0f172a 100%); margin: 12px 0 16px; border-radius: 1px; }
        .meta { font-size: 8pt; color: #475569; margin-bottom: 14px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0f172a; color: #fff; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.06em; padding: 7px 5px; text-align: left; }
        th.dni-col { width: 22%; }
        td { border-bottom: 1px solid #e2e8f0; padding: 5px 5px; vertical-align: top; font-size: 8.5pt; }
        tr:nth-child(even) td { background: #f8fafc; }
        .num { font-family: DejaVu Sans Mono, monospace; font-weight: bold; width: 8%; }
        .dni-mask {
            font-family: DejaVu Sans Mono, monospace;
            letter-spacing: 0.05em;
            background: #cbd5e1;
            color: #1e293b;
            padding: 2px 6px;
            border-radius: 2px;
        }
        .pie { margin-top: 18px; font-size: 7pt; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .aviso { font-size: 7.5pt; color: #64748b; font-style: italic; margin-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ $hermandad?->nombre_hermandad ?? $hermandad?->nombre_corto ?? 'Hermandad' }}</h1>
    <p class="sub">Censo oficial de votantes — Elecciones y órganos de gobierno</p>
    <div class="bar"></div>

    <div class="meta">
        <strong>Fecha del informe:</strong> {{ $fechaInforme->format('d/m/Y') }}<br>
        <strong>Criterios:</strong> Estado Alta; mayor de 18 años a la fecha del informe; antigüedad mínima {{ $antiguedadAnos }} año(s) en la hermandad (fecha de alta).<br>
        @if ($excluirMorosos)
            <strong>Filtro económico:</strong> excluidos hermanos con participaciones de lotería/rifa pendientes de cobro o cuota ordinaria marcada como pendiente en secretaría.<br>
        @endif
        <strong>Total de votantes:</strong> {{ $total }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="num">N.º</th>
                <th>Apellidos, nombre</th>
                <th class="dni-col">Documento (protegido RGPD)</th>
                <th>Localidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($votantes as $h)
                <tr>
                    <td class="num">{{ $h->numero_hermano }}</td>
                    <td>{{ $h->apellidos }}, {{ $h->nombre }}</td>
                    <td><span class="dni-mask">{{ $enmascararDni($h->dni) }}</span></td>
                    <td>{{ $h->localidad ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="aviso">El identificador se muestra enmascarado conforme a minimización de datos (RGPD). Documento interno de la hermandad.</p>
    <p class="pie">Generado el {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
