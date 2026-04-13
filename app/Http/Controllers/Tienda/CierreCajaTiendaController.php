<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\StoreCierreCajaTiendaRequest;
use App\Models\AperturaCajaTienda;
use App\Models\CierreCajaTienda;
use App\Models\VentaTienda;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CierreCajaTiendaController extends Controller
{
    public function create(): View
    {
        $fecha = request('fecha', now()->toDateString());
        $carbon = Carbon::parse($fecha);
        $totales = $this->teoricos($carbon);
        $apertura = AperturaCajaTienda::query()->whereDate('fecha', $carbon->toDateString())->first();
        $saldoInicial = $apertura ? (float) $apertura->saldo_inicial_efectivo : 0.0;
        $teoricoEfectivo = $totales[VentaTienda::METODO_EFECTIVO] ?? 0;
        $efectivoEsperado = round($saldoInicial + $teoricoEfectivo, 2);

        return view('tienda.cierre-caja.create', [
            'fecha' => $fecha,
            'totales' => $totales,
            'apertura' => $apertura,
            'saldoInicialEfectivo' => $saldoInicial,
            'efectivoEsperadoCierre' => $efectivoEsperado,
            'cierreExistente' => CierreCajaTienda::query()->whereDate('fecha', $carbon->toDateString())->first(),
        ]);
    }

    public function store(StoreCierreCajaTiendaRequest $request): RedirectResponse
    {
        $fecha = Carbon::parse($request->validated('fecha'))->startOfDay();
        $totales = $this->teoricos($fecha);

        $teoricoEfectivo = $totales[VentaTienda::METODO_EFECTIVO] ?? 0;
        $teoricoTarjeta = $totales[VentaTienda::METODO_TARJETA] ?? 0;
        $teoricoBizum = $totales[VentaTienda::METODO_BIZUM] ?? 0;

        $fisico = round((float) $request->validated('conteo_efectivo_fisico'), 2);
        $apertura = AperturaCajaTienda::query()->whereDate('fecha', $fecha->toDateString())->first();
        $saldoInicial = $apertura ? (float) $apertura->saldo_inicial_efectivo : 0.0;
        $efectivoEsperado = round($saldoInicial + $teoricoEfectivo, 2);
        $dif = round($fisico - $efectivoEsperado, 2);

        CierreCajaTienda::query()->updateOrCreate(
            ['fecha' => $fecha->toDateString()],
            [
                'user_id' => Auth::id(),
                'teorico_efectivo' => $teoricoEfectivo,
                'teorico_tarjeta' => $teoricoTarjeta,
                'teorico_bizum' => $teoricoBizum,
                'saldo_inicial_efectivo' => round($saldoInicial, 2),
                'efectivo_esperado_cierre' => $efectivoEsperado,
                'conteo_efectivo_fisico' => $fisico,
                'diferencia_efectivo' => $dif,
                'notas' => $request->validated('notas'),
            ]
        );

        return redirect()
            ->route('tienda.cierre-caja.create', ['fecha' => $fecha->toDateString()])
            ->with('status', 'Cierre registrado. Descuadre en caja (físico − esperado): '.number_format($dif, 2, ',', '.').' €.');
    }

    /**
     * @return array<string, float>
     */
    private function teoricos(Carbon $fecha): array
    {
        return VentaTienda::query()
            ->whereDate('created_at', $fecha)
            ->selectRaw('metodo_pago, SUM(importe_total) as t')
            ->groupBy('metodo_pago')
            ->pluck('t', 'metodo_pago')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }
}
