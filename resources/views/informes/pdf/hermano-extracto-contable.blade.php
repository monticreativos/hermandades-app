<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Extracto contable — Hermano n.º {{ $hermano->numero_hermano }}</title>
    <style>
        @page { margin: 26px 28px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #0f172a;
            margin: 0;
        }
        .orla-outer {
            border: 3px double #0f172a;
            padding: 8px;
            min-height: 720px;
            background: #fffef9;
        }
        .orla-inner {
            border: 1px solid #c6a16a;
            padding: 20px 22px;
            min-height: 700px;
            position: relative;
        }
        .barra-dorada {
            height: 3px;
            background: linear-gradient(90deg, #c6a16a 0%, #0f172a 50%, #c6a16a 100%);
            margin: 0 0 14px;
            border-radius: 1px;
        }
        .cabecera { display: table; width: 100%; margin-bottom: 12px; }
        .cab-escudo { display: table-cell; width: 64px; vertical-align: top; }
        .cab-escudo img { width: 56px; height: auto; display: block; }
        .cab-texto { display: table-cell; vertical-align: middle; padding-left: 10px; }
        h1 {
            margin: 0 0 4px;
            font-size: 13pt;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .sub { font-size: 8.5pt; color: #64748b; margin: 0; }
        .meta { font-size: 8pt; color: #94a3b8; text-align: right; margin-top: 8px; }
        .caja-hermano {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        table.movs { width: 100%; border-collapse: collapse; font-size: 8pt; }
        table.movs th {
            background: #f1f5f9;
            text-align: left;
            padding: 6px 4px;
            border-bottom: 2px solid #c6a16a;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.04em;
        }
        table.movs td { padding: 5px 4px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .sin-cuenta { color: #b45309; font-style: italic; padding: 16px 0; }
        .pie { font-size: 7.5pt; color: #64748b; margin-top: 14px; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="orla-outer">
        <div class="orla-inner">
            <div class="barra-dorada"></div>
            <div class="cabecera">
                @if (!empty($escudoDataUri))
                    <div class="cab-escudo">
                        <img src="{{ $escudoDataUri }}" alt="">
                    </div>
                @else
                    <div class="cab-escudo"></div>
                @endif
                <div class="cab-texto">
                    <h1>{{ $hermandad->nombre_hermandad }}</h1>
                    <p class="sub">Extracto de subcuenta contable (Libro mayor simplificado)</p>
                </div>
            </div>
            <div class="meta">
                Emisión: {{ $fechaEmision->format('d/m/Y H:i') }}
            </div>
            <div class="caja-hermano">
                <strong>Hermano n.º {{ $hermano->numero_hermano }}</strong> — {{ $hermano->nombreCompleto() }}<br>
                @if ($cuenta)
                    Cuenta: <span style="font-family: DejaVu Sans Mono, monospace;">{{ $cuenta->codigo }}</span> — {{ $cuenta->nombre }}
                @else
                    <span class="sin-cuenta">Sin subcuenta auxiliar vinculada.</span>
                @endif
            </div>

            @if ($cuenta && $movimientos->isNotEmpty())
                <table class="movs">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th class="num">Debe</th>
                            <th class="num">Haber</th>
                            <th class="num">Saldo acum.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movimientos as $m)
                            @php
                                $concepto = trim(($m->concepto_detalle ? $m->concepto_detalle.' — ' : '').$m->asiento->glosa);
                                $saldo = array_key_exists('saldo_acumulado', $m->getAttributes()) ? (float) $m->saldo_acumulado : null;
                            @endphp
                            <tr>
                                <td>{{ $m->asiento->fecha->format('d/m/Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($concepto, 85) }}</td>
                                <td class="num">{{ $m->debe > 0 ? number_format($m->debe, 2, ',', '.') : '—' }}</td>
                                <td class="num">{{ $m->haber > 0 ? number_format($m->haber, 2, ',', '.') : '—' }}</td>
                                <td class="num">{{ $saldo !== null ? number_format($saldo, 2, ',', '.') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif ($cuenta)
                <p class="sin-cuenta">No constan movimientos en esta subcuenta.</p>
            @endif

            <p class="pie">
                Documento generado desde GestaHerSevilla. El saldo acumulado se calcula en orden cronológico (Debe − Haber).
                Para uso interno y remisión al cofrade; el registro oficial es el libro diario.
            </p>
        </div>
    </div>
</body>
</html>
