<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\StorePortalBizumCuotaRequest;
use App\Services\Contabilidad\MovimientoRapidoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PortalBizumCuotaController extends Controller
{
    public function __construct(
        private readonly MovimientoRapidoService $movimientoRapidoService
    ) {}

    public function __invoke(StorePortalBizumCuotaRequest $request): RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        try {
            $this->movimientoRapidoService->registrarCobroCuotaPortal(
                $hermano,
                (float) $request->validated('importe'),
                'portal_bizum'
            );
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('portal.pagos.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable) {
            return redirect()
                ->route('portal.pagos.index')
                ->with('error', 'No se pudo registrar el pago. Inténtelo de nuevo o contacte con secretaría.');
        }

        return redirect()
            ->route('portal.pagos.index')
            ->with('status', 'Pago simulado registrado. Su cuota consta como abonada en contabilidad (Bizum → bancos).');
    }
}
