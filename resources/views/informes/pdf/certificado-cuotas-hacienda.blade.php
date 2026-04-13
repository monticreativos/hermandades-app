<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado de cuotas y participaciones — {{ $año }}</title>
    <style>
        @page { margin: 28px 32px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Serif, DejaVu Sans, serif;
            font-size: 10pt;
            color: #0f172a;
            margin: 0;
            position: relative;
        }
        .orla-outer {
            border: 4px double #0f172a;
            padding: 10px;
            min-height: 680px;
            position: relative;
            background: #fffef9;
        }
        .orla-inner {
            border: 1px solid #c6a16a;
            padding: 28px 32px 24px;
            min-height: 660px;
            position: relative;
        }
        .watermark {
            position: absolute;
            left: 50%;
            top: 40%;
            transform: translate(-50%, -50%);
            width: 200px;
            opacity: 0.06;
            z-index: 0;
        }
        .watermark img { width: 200px; height: auto; display: block; margin: 0 auto; }
        .content { position: relative; z-index: 1; }
        h1 {
            text-align: center;
            font-size: 15pt;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0 0 6px;
            color: #0f172a;
        }
        .subtitulo {
            text-align: center;
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 8px;
        }
        .barra-dorada {
            height: 3px;
            background: linear-gradient(90deg, #c6a16a 0%, #0f172a 55%, #c6a16a 100%);
            margin: 12px auto 18px;
            max-width: 88%;
            border-radius: 1px;
        }
        .cuerpo { line-height: 1.55; text-align: justify; margin-bottom: 12px; font-size: 9.5pt; }
        .destacado { font-weight: bold; }
        table.detalle { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin: 14px 0; }
        table.detalle th {
            background: #0f172a;
            color: #fff;
            padding: 7px 5px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 7.5pt;
        }
        table.detalle td { border-bottom: 1px solid #e2e8f0; padding: 6px 5px; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .total-fila { font-weight: bold; background: #fefce8 !important; }
        .aviso {
            font-size: 8pt;
            color: #64748b;
            font-style: italic;
            border-left: 3px solid #c6a16a;
            padding: 8px 10px;
            background: #f8fafc;
            margin: 14px 0;
        }
        .firmas { margin-top: 28px; width: 100%; border-collapse: collapse; }
        .firmas td { width: 50%; text-align: center; vertical-align: bottom; padding: 8px 12px 4px; }
        .firma-img { max-height: 48px; max-width: 170px; margin: 0 auto 4px; display: block; }
        .linea-firma { border-top: 1px solid #0f172a; margin: 0 auto 6px; width: 78%; }
        .cargo { font-size: 8pt; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; }
        .sello-pos { position: absolute; right: 36px; bottom: 88px; width: 68px; opacity: 0.9; z-index: 2; }
        .sello-pos img { width: 68px; height: auto; }
        .meta-emision { font-size: 7.5pt; color: #94a3b8; text-align: right; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="orla-outer">
        <div class="orla-inner">
            @if (!empty($escudoDataUri))
                <div class="watermark">
                    <img src="{{ $escudoDataUri }}" alt="">
                </div>
            @endif
            @if (!empty($selloDataUri))
                <div class="sello-pos">
                    <img src="{{ $selloDataUri }}" alt="Sello">
                </div>
            @endif

            <div class="content">
                <h1>Certificado resumen de aportaciones</h1>
                <p class="subtitulo">{{ $hermandad?->nombre_hermandad ?? $hermandad?->nombre_corto ?? 'Hermandad' }} — Ejercicio {{ $año }}</p>
                <div class="barra-dorada"></div>

                <p class="cuerpo">
                    La Hermandad certifica, a los efectos oportunos ante la Agencia Tributaria y demás administraciones,
                    que <span class="destacado">{{ $hermano->nombre }} {{ $hermano->apellidos }}</span> (DNI/NIE <span class="destacado">{{ $hermano->dni }}</span>),
                    hermano/a n.º <span class="destacado">{{ $hermano->numero_hermano }}</span>,
                    consta en nuestros registros con los cobros de participaciones de lotería / rifa asociadas al ejercicio natural <span class="destacado">{{ $año }}</span> que se detallan a continuación.
                </p>

                <div class="aviso">
                    Nota: Este resumen refleja únicamente los cobros registrados en el módulo de lotería de la aplicación.
                    Las cuotas ordinarias tratadas contablemente de forma agregada deberán acreditarse, en su caso, mediante extracto bancario o certificación contable adicional.
                </div>

                @if (!empty($cuotaOrdinariaPendienteEsteAño))
                    <div class="aviso" style="border-left-color:#ea580c;background:#fff7ed;font-style:normal;color:#9a3412;">
                        <strong>Situación registral:</strong> consta <strong>cuota ordinaria pendiente de cobro</strong> para el ejercicio {{ $año }}, según el control interno de secretaría vinculado al plan contable (430/431). El importe exacto debe contrastarse con el libro diario.
                    </div>
                @endif

                @if ($lineasCuotas->isEmpty())
                    <p class="cuerpo"><strong>No constan cobros registrados para el ejercicio {{ $año }}.</strong></p>
                @else
                    <table class="detalle">
                        <thead>
                            <tr>
                                <th>Concepto / campaña</th>
                                <th>Fecha cobro</th>
                                <th style="text-align:right">Importe (€)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lineasCuotas as $l)
                                <tr>
                                    <td>
                                        {{ trim(($l->loteria?->sorteo ?? '').' '.($l->loteria?->numero ?? '')) ?: 'Participación lotería/rifa' }}
                                        @if($l->referencia_taco) — Ref. {{ $l->referencia_taco }} @endif
                                    </td>
                                    <td>{{ optional($l->fecha_cobro)->format('d/m/Y') ?? '—' }}</td>
                                    <td style="text-align:right">{{ number_format((float) $l->importe_a_cobrar, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-fila">
                                <td colspan="2"><strong>Total ejercicio {{ $año }}</strong></td>
                                <td style="text-align:right"><strong>{{ number_format($totalCuotas, 2, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                <table class="firmas">
                    <tr>
                        <td>
                            @if (!empty($firmaSecretarioDataUri))
                                <img src="{{ $firmaSecretarioDataUri }}" class="firma-img" alt="">
                            @else
                                <div style="height:48px"></div>
                            @endif
                            <div class="linea-firma"></div>
                            <p class="cargo">El Secretario</p>
                        </td>
                        <td>
                            @if (!empty($firmaMayordomoDataUri))
                                <img src="{{ $firmaMayordomoDataUri }}" class="firma-img" alt="">
                            @else
                                <div style="height:48px"></div>
                            @endif
                            <div class="linea-firma"></div>
                            <p class="cargo">El Mayordomo</p>
                        </td>
                    </tr>
                </table>

                <p class="meta-emision">
                    {{ $hermandad?->localidad ? $hermandad->localidad.', ' : '' }}{{ $fechaEmision->format('d/m/Y') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
