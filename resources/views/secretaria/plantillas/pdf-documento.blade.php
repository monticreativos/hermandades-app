<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 30mm 20mm 25mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #0F172A; font-size: 12pt; line-height: 1.45; }
        .watermark-text { position: fixed; top: 42%; left: 18%; font-size: 54px; opacity: .06; transform: rotate(-22deg); z-index: -2; }
        .watermark-img { position: fixed; top: 26%; left: 22%; width: 56%; opacity: .08; z-index: -3; }
        .header { border-bottom: 2px solid #C6A16A; padding-bottom: 10px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; }
        .header img { width: 54px; height: 54px; object-fit: contain; }
        .footer { position: fixed; bottom: -8mm; left: 0; right: 0; border-top: 1px solid #d1d5db; color: #64748b; font-size: 10px; text-align: center; padding-top: 6px; }
    </style>
</head>
<body>
    @if (!empty($marcaAguaPath))
        <img class="watermark-img" src="{{ $marcaAguaPath }}" alt="marca agua">
    @endif
    <div class="watermark-text">{{ $marcaAgua }}</div>
    <div class="header">
        @if (!empty($escudoUrl))
            <img src="{{ $escudoUrl }}" alt="Escudo">
        @endif
        <div>
            <h1 style="margin:0; font-size:18pt;">{{ $titulo }}</h1>
            <p style="margin:2px 0 0 0;">{{ $nombreHermandad }} · Secretaría</p>
        </div>
    </div>
    <div>{!! $contenido !!}</div>
    <div class="footer">{{ $nombreHermandad }} · Documento oficial · {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
