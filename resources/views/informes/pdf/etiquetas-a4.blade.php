<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Etiquetas postales</title>
    <style>
        @page { margin: 8mm 7mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; color: #0f172a; font-size: 9pt; }
        .page { page-break-after: always; width: 100%; }
        .page:last-child { page-break-after: auto; }
        table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grid td {
            width: 33.33%;
            height: 38mm;
            vertical-align: middle;
            border: 0.4pt dashed #cbd5e1;
            padding: 4mm 3mm;
        }
        .label-inner {
            border-left: 2pt solid #c6a16a;
            padding-left: 3mm;
            min-height: 28mm;
        }
        .nombre { font-weight: bold; font-size: 10pt; margin-bottom: 2mm; color: #0f172a; }
        .dir { font-size: 9pt; line-height: 1.35; color: #334155; }
        .pie-pag { font-size: 6pt; color: #94a3b8; text-align: center; margin-top: 2mm; }
    </style>
</head>
<body>
    @php
        $chunks = $hermanos->chunk(21);
    @endphp
    @forelse ($chunks as $pagina => $bloque)
        <div class="page">
            <table class="grid">
                @foreach ($bloque->chunk(3) as $fila)
                    <tr>
                        @foreach ($fila as $h)
                            <td>
                                <div class="label-inner">
                                    <div class="nombre">{{ $h->nombre }} {{ $h->apellidos }}</div>
                                    <div class="dir">
                                        {{ $h->direccion ?: '—' }}<br>
                                        {{ trim(($h->codigo_postal ?: '').' '.($h->localidad ?: '')) }}<br>
                                        {{ $h->provincia ?: '' }}
                                    </div>
                                </div>
                            </td>
                        @endforeach
                        @for ($i = $fila->count(); $i < 3; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
            <p class="pie-pag">{{ $hermandad?->nombre_corto ?? 'Hermandad' }} · Etiquetas {{ $modo === 'cabezas' ? 'cabezas de familia' : 'todos' }} · Pág. {{ $pagina + 1 }} / {{ $chunks->count() }}</p>
        </div>
    @empty
        <p style="padding:20mm;text-align:center;color:#64748b;">No hay hermanos que cumplan los filtros seleccionados.</p>
    @endforelse
</body>
</html>
