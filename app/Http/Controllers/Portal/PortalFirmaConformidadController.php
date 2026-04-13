<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\FirmaConformidadSolicitud;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalFirmaConformidadController extends Controller
{
    public function show(FirmaConformidadSolicitud $solicitud): View
    {
        $hermano = Auth::guard('portal')->user()->hermano;
        abort_unless($solicitud->hermano_id === $hermano->id, 403);

        $solicitud->load('documentoArchivo');

        return view('portal.firmas.show', [
            'solicitud' => $solicitud,
            'hermano' => $hermano,
        ]);
    }

    public function firmar(Request $request, FirmaConformidadSolicitud $solicitud): RedirectResponse
    {
        $hermano = Auth::guard('portal')->user()->hermano;
        abort_unless($solicitud->hermano_id === $hermano->id, 403);

        if ($solicitud->estado !== FirmaConformidadSolicitud::ESTADO_PENDIENTE) {
            throw ValidationException::withMessages([
                'acepto' => ['Este documento ya consta como firmado.'],
            ]);
        }

        $request->validate([
            'acepto' => ['accepted'],
        ]);

        $solicitud->forceFill([
            'estado' => FirmaConformidadSolicitud::ESTADO_FIRMADO,
            'firmado_en' => now(),
            'firmado_ip' => $request->ip(),
        ])->save();

        RegistroActividad::registrar(
            'firma_conformidad_portal',
            'Hermano n.º '.$hermano->numero_hermano.' firmó conformidad: «'.$solicitud->titulo.'» (solicitud '.$solicitud->id.', IP '.$request->ip().').'
        );

        return redirect()
            ->route('portal.documentos.index')
            ->with('status', 'Su firma de conformidad ha quedado registrada.');
    }
}
