<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Arqueo tesorería — {{ $etiquetaMes }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #0f172a; margin: 0; }
        .barra-dorada {
            height: 3px;
            background: linear-gradient(90deg, #c6a16a 0%, #0f172a 50%, #c6a16a 100%);
            margin: 0 0 16px;
        }
        h1 { font-size: 15pt; margin: 0 0 6px; letter-spacing: 0.06em; text-transform: uppercase; }
        .sub { color: #64748b; font-size: 9pt; margin: 0 0 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background: #f1f5f9; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.05em; }
        .num { text-align: right; font-family: DejaVu Sans Mono, monospace; }
        .total-row { font-weight: bold; background: #f8fafc; }
        .meta { font-size: 8pt; color: #94a3b8; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="barra-dorada"></div>
    <h1>{{ $hermandad->nombre_hermandad }}</h1>
    <p class="sub">Arqueo de caja y bancos — {{ $etiquetaMes }} (570 / 572)</p>
    <p style="font-size:9pt;">Periodo contable: <strong>{{ \Carbon\Carbon::parse($mesInicio)->format('d/m/Y') }}</strong> al <strong>{{ \Carbon\Carbon::parse($mesFin)->format('d/m/Y') }}</strong></p>

    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th>Nombre</th>
                <th class="num">Saldo inicial</th>
                <th class="num">Gastos (Debe)</th>
                <th class="num">Ingresos (Haber)</th>
                <th class="num">Saldo final</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cuentas as $fila)
                <tr>
                    <td style="font-family: DejaVu Sans Mono, monospace;">{{ $fila['cuenta']->codigo }}</td>
                    <td>{{ $fila['cuenta']->nombre }}</td>
                    <td class="num">{{ number_format($fila['saldo_inicial'], 2, ',', '.') }} €</td>
                    <td class="num">{{ number_format($fila['gastos'], 2, ',', '.') }} €</td>
                    <td class="num">{{ number_format($fila['ingresos'], 2, ',', '.') }} €</td>
                    <td class="num">{{ number_format($fila['saldo_final'], 2, ',', '.') }} €</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">Totales tesorería</td>
                <td class="num">{{ number_format($totales['saldo_inicial'], 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($totales['gastos'], 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($totales['ingresos'], 2, ',', '.') }} €</td>
                <td class="num">{{ number_format($totales['saldo_final'], 2, ',', '.') }} €</td>
            </tr>
        </tbody>
    </table>

    <p class="meta">
        Generado el {{ now()->format('d/m/Y H:i') }} — Cierre mensual para revisión del Fiscal / Censor.
        Los importes proceden del libro diario (subcuentas 570 y 572).
    </p>
</body>
</html>
