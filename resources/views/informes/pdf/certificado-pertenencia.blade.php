<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado de pertenencia cofrade</title>
    <style>
        @page { margin: 28px 32px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Serif, DejaVu Sans, serif;
            font-size: 10.5pt;
            color: #0f172a;
            margin: 0;
            position: relative;
            min-height: 100vh;
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
            top: 42%;
            transform: translate(-50%, -50%);
            width: 220px;
            opacity: 0.06;
            z-index: 0;
        }
        .watermark img { width: 220px; height: auto; display: block; margin: 0 auto; }
        .content { position: relative; z-index: 1; }
        h1 {
            text-align: center;
            font-size: 17pt;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin: 0 0 6px;
            color: #0f172a;
        }
        .subtitulo {
            text-align: center;
            font-size: 9.5pt;
            color: #64748b;
            margin-bottom: 10px;
        }
        .barra-dorada {
            height: 3px;
            background: linear-gradient(90deg, #c6a16a 0%, #0f172a 55%, #c6a16a 100%);
            margin: 14px auto 22px;
            max-width: 88%;
            border-radius: 1px;
        }
        .cuerpo {
            line-height: 1.65;
            text-align: justify;
            margin-bottom: 18px;
        }
        .destacado {
            font-weight: bold;
            color: #0f172a;
        }
        .caja-datos {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 14px 16px;
            margin: 18px 0;
            border-radius: 2px;
        }
        .caja-datos table { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
        .caja-datos td { padding: 4px 6px; vertical-align: top; }
        .caja-datos td.label { width: 32%; color: #64748b; font-weight: bold; }
        .pie-legal {
            font-size: 8pt;
            color: #64748b;
            font-style: italic;
            margin-top: 16px;
            line-height: 1.45;
        }
        .firmas {
            margin-top: 36px;
            width: 100%;
            border-collapse: collapse;
        }
        .firmas td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 8px 12px 4px;
        }
        .firma-img { max-height: 52px; max-width: 180px; margin: 0 auto 4px; display: block; }
        .linea-firma {
            border-top: 1px solid #0f172a;
            margin: 0 auto 6px;
            width: 78%;
        }
        .cargo { font-size: 8.5pt; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; }
        .sello-pos {
            position: absolute;
            right: 36px;
            bottom: 100px;
            width: 72px;
            opacity: 0.92;
            z-index: 2;
        }
        .sello-pos img { width: 72px; height: auto; }
        .meta-emision {
            font-size: 8pt;
            color: #94a3b8;
            text-align: right;
            margin-top: 8px;
        }
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
                <h1>Certificado de pertenencia</h1>
                <p class="subtitulo">{{ $hermandad?->nombre_hermandad ?? $hermandad?->nombre_corto ?? 'Hermandad' }}</p>
                <div class="barra-dorada"></div>

                <p class="cuerpo">
                    El Secretario y el Mayordomo de la citada Hermandad <span class="destacado">CERTIFICAN</span>
                    que <span class="destacado">{{ $hermano->nombre }} {{ $hermano->apellidos }}</span>,
                    con documento identificativo <span class="destacado">{{ $hermano->dni }}</span>,
                    figura inscrito/a como hermano/a con el <span class="destacado">número {{ $hermano->numero_hermano }}</span>,
                    con fecha de alta en el libro de registro de <span class="destacado">{{ optional($hermano->fecha_alta)->format('d/m/Y') ?? '—' }}</span>.
                    La antigüedad cofrade acreditada a la fecha de emisión es de <span class="destacado">{{ $antiguedadTexto }}</span>
                </p>

                <p class="cuerpo">
                    Situación registral actual: <span class="destacado">{{ $hermano->estado }}</span>.
                    @if ($hermano->fecha_baja)
                        Fecha de baja o causación: {{ $hermano->fecha_baja->format('d/m/Y') }}.
                    @endif
                </p>

                <div class="caja-datos">
                    <table>
                        <tr>
                            <td class="label">Domicilio</td>
                            <td>{{ $hermano->direccion ?: '—' }} @if($hermano->codigo_postal) — {{ $hermano->codigo_postal }} @endif {{ $hermano->localidad ? ' · '.$hermano->localidad : '' }}</td>
                        </tr>
                        @if ($hermandad?->cif)
                            <tr>
                                <td class="label">CIF Hermandad</td>
                                <td>{{ $hermandad->cif }}</td>
                            </tr>
                        @endif
                    </table>
                </div>

                <p class="pie-legal">
                    Documento expedido a efectos de traslados, solicitudes ante autoridades eclesiásticas o civiles y demás usos protocolarios.
                    La validez del presente certificado queda supeditada a los datos vigentes en el archivo de la Hermandad.
                </p>

                <table class="firmas">
                    <tr>
                        <td>
                            @if (!empty($firmaSecretarioDataUri))
                                <img src="{{ $firmaSecretarioDataUri }}" class="firma-img" alt="Firma Secretario">
                            @else
                                <div style="height:52px"></div>
                            @endif
                            <div class="linea-firma"></div>
                            <p class="cargo">El Secretario</p>
                        </td>
                        <td>
                            @if (!empty($firmaMayordomoDataUri))
                                <img src="{{ $firmaMayordomoDataUri }}" class="firma-img" alt="Firma Mayordomo">
                            @else
                                <div style="height:52px"></div>
                            @endif
                            <div class="linea-firma"></div>
                            <p class="cargo">El Mayordomo</p>
                        </td>
                    </tr>
                </table>

                <p class="meta-emision">
                    {{ $hermandad?->localidad ? $hermandad->localidad.', ' : '' }}{{ $fechaEmision->format('d/m/Y') }}
                    — Documento generado electrónicamente (espacio reservado para firma digital cualificada si procede).
                </p>
            </div>
        </div>
    </div>
</body>
</html>
