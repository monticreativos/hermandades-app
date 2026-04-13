<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretaria\RechazarSolicitudCambioDatosRequest;
use App\Models\SolicitudCambioDatos;
use App\Models\User;
use App\Services\Portal\ProcesarSolicitudCambioDatosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SolicitudCambioDatosController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->string('estado')->toString();

        $q = SolicitudCambioDatos::query()
            ->with(['hermano', 'portalCuenta', 'procesadoPor'])
            ->orderByDesc('created_at');

        if ($estado !== '' && in_array($estado, [
            SolicitudCambioDatos::ESTADO_PENDIENTE,
            SolicitudCambioDatos::ESTADO_APROBADA,
            SolicitudCambioDatos::ESTADO_RECHAZADA,
        ], true)) {
            $q->where('estado', $estado);
        } else {
            $q->where('estado', SolicitudCambioDatos::ESTADO_PENDIENTE);
            $estado = SolicitudCambioDatos::ESTADO_PENDIENTE;
        }

        $solicitudes = $q->paginate(20)->withQueryString();

        $pendientesCount = SolicitudCambioDatos::query()
            ->where('estado', SolicitudCambioDatos::ESTADO_PENDIENTE)
            ->count();

        return view('secretaria.solicitudes-cambio.index', [
            'solicitudes' => $solicitudes,
            'filtroEstado' => $estado,
            'pendientesCount' => $pendientesCount,
        ]);
    }

    public function show(SolicitudCambioDatos $solicitud): View
    {
        $solicitud->load(['hermano.banco', 'portalCuenta', 'procesadoPor']);

        return view('secretaria.solicitudes-cambio.show', [
            'solicitud' => $solicitud,
        ]);
    }

    public function aprobar(
        SolicitudCambioDatos $solicitud,
        ProcesarSolicitudCambioDatosService $service
    ): RedirectResponse {
        try {
            $service->aprobar($solicitud, $this->getUser());
        } catch (ValidationException $e) {
            return redirect()
                ->route('secretaria.solicitudes-cambio.show', $solicitud)
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('secretaria.solicitudes-cambio.index')
            ->with('status', 'Solicitud aprobada y datos del hermano actualizados.');
    }

    public function rechazar(
        RechazarSolicitudCambioDatosRequest $request,
        SolicitudCambioDatos $solicitud,
        ProcesarSolicitudCambioDatosService $service
    ): RedirectResponse {
        try {
            $service->rechazar($solicitud, $this->getUser(), $request->validated('motivo_rechazo'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('secretaria.solicitudes-cambio.show', $solicitud)
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('secretaria.solicitudes-cambio.index')
            ->with('status', 'Solicitud rechazada.');
    }

    private function getUser(): User
    {
        $u = auth()->user();
        abort_if(! $u, 403);

        return $u;
    }
}
