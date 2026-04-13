<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Papeleta de sitio</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #0f172a; margin: 24px; }
        .marco { border: 2px solid #c6a16a; padding: 20px; border-radius: 4px; }
        h1 { font-size: 14pt; text-align: center; margin: 0 0 8px; color: #0f172a; letter-spacing: 0.05em; }
        .sub { text-align: center; font-size: 9pt; color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px 0; vertical-align: top; }
        .k { width: 32%; font-weight: bold; color: #334155; font-size: 9pt; text-transform: uppercase; }
        .v { border-bottom: 1px solid #e2e8f0; }
        .pie { margin-top: 24px; font-size: 8pt; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="marco">
        @if (!empty($escudoPath) && file_exists($escudoPath))
            <div style="text-align:center;margin-bottom:12px;">
                <img src="{{ $escudoPath }}" alt="" style="max-height:64px;">
            </div>
        @endif
        <h1>{{ $hermandad?->nombre_hermandad ?? $hermandad?->nombre_corto ?? 'Hermandad' }}</h1>
        <p class="sub">Papeleta de sitio — Estación de penitencia {{ $papeleta->ejercicio?->año }}</p>
        <table>
            <tr><td class="k">Hermano</td><td class="v">{{ $papeleta->hermano->nombreCompleto() }}</td></tr>
            <tr><td class="k">N.º de hermano</td><td class="v">{{ $papeleta->hermano->numero_hermano }}</td></tr>
            <tr><td class="k">Puesto</td><td class="v">{{ $papeleta->puesto }}</td></tr>
            <tr><td class="k">Tramo</td><td class="v">{{ $papeleta->tramo ?: '—' }}</td></tr>
            <tr><td class="k">Insignia</td><td class="v">{{ $papeleta->insignia?->nombre ?? '—' }}</td></tr>
            <tr><td class="k">Donativo</td><td class="v">{{ number_format((float) $papeleta->donativo_pagado, 2, ',', '.') }} €</td></tr>
            <tr><td class="k">Estado</td><td class="v">{{ $papeleta->estado }}</td></tr>
            @if ($config?->fecha_salida)
                <tr><td class="k">Fecha de salida</td><td class="v">{{ $config->fecha_salida->format('d/m/Y') }}</td></tr>
            @endif
        </table>
        @if ($papeleta->notas)
            <p style="margin-top:16px;font-size:9pt;color:#475569;"><strong>Notas:</strong> {{ $papeleta->notas }}</p>
        @endif
    </div>
    <p class="pie">Documento generado el {{ now()->format('d/m/Y H:i') }}. Conserve este documento para la salida procesional.</p>
</body>
</html>
