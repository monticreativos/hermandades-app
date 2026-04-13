<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\DocumentoArchivo;
use App\Models\FirmaConformidadSolicitud;
use App\Models\PapeletaSitio;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalDocumentosController extends Controller
{
    public function __invoke(): View
    {
        $cuenta = Auth::guard('portal')->user();
        $hermano = $cuenta->hermano;

        $papeletasHistoricas = $hermano->papeletas()
            ->where('estado', PapeletaSitio::ESTADO_EMITIDA)
            ->with(['ejercicio', 'insignia'])
            ->get()
            ->sortByDesc(fn (PapeletaSitio $p) => $p->ejercicio?->año ?? 0)
            ->values();

        $boletines = DocumentoArchivo::query()
            ->where('categoria', DocumentoArchivo::CATEGORIA_BOLETIN)
            ->where('nivel_acceso', DocumentoArchivo::NIVEL_PUBLICO_HERMANOS)
            ->orderByDesc('created_at')
            ->get();

        $añosCertificado = range((int) now()->year, max((int) now()->year - 5, 2000));

        $firmasPendientes = $hermano->solicitudesFirmaConformidad()
            ->where('estado', FirmaConformidadSolicitud::ESTADO_PENDIENTE)
            ->with('documentoArchivo')
            ->orderByDesc('created_at')
            ->get();

        return view('portal.documentos.index', [
            'cuenta' => $cuenta,
            'hermano' => $hermano,
            'papeletasHistoricas' => $papeletasHistoricas,
            'boletines' => $boletines,
            'añosCertificado' => $añosCertificado,
            'firmasPendientes' => $firmasPendientes,
        ]);
    }
}
