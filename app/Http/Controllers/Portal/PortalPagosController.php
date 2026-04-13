<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalPagosController extends Controller
{
    public function __invoke(): View
    {
        $cuenta = Auth::guard('portal')->user();
        $cuenta->load(['hermano.cuotaPendienteEjercicio']);

        $hermano = $cuenta->hermano;
        $deudaLoteria = $hermano->deudaLoteria();

        $historialRecibos = $hermano->remesaRecibos()
            ->with(['remesa'])
            ->orderByDesc('id')
            ->limit(40)
            ->get();

        return view('portal.pagos.index', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
            'deudaLoteria' => $deudaLoteria,
            'historialRecibos' => $historialRecibos,
        ]);
    }
}
