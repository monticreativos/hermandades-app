<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretaria\StoreFirmaConformidadSolicitudRequest;
use App\Models\DocumentoArchivo;
use App\Models\FirmaConformidadSolicitud;
use App\Models\Hermano;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FirmaConformidadSolicitudController extends Controller
{
    public function index(): View
    {
        $solicitudes = FirmaConformidadSolicitud::query()
            ->with(['hermano', 'documentoArchivo', 'creadoPor'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('secretaria.firmas-conformidad.index', [
            'solicitudes' => $solicitudes,
        ]);
    }

    public function create(): View
    {
        $hermanos = Hermano::query()
            ->where('estado', 'Alta')
            ->orderBy('numero_hermano')
            ->get(['id', 'numero_hermano', 'nombre', 'apellidos']);

        $documentos = DocumentoArchivo::query()
            ->where('nivel_acceso', DocumentoArchivo::NIVEL_PUBLICO_HERMANOS)
            ->orderBy('titulo')
            ->get();

        return view('secretaria.firmas-conformidad.create', [
            'hermanos' => $hermanos,
            'documentos' => $documentos,
        ]);
    }

    public function store(StoreFirmaConformidadSolicitudRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $s = FirmaConformidadSolicitud::query()->create([
            'hermano_id' => $request->validated('hermano_id'),
            'titulo' => $request->validated('titulo'),
            'descripcion' => $request->validated('descripcion'),
            'documento_archivo_id' => $request->validated('documento_archivo_id'),
            'estado' => FirmaConformidadSolicitud::ESTADO_PENDIENTE,
            'creado_por_user_id' => $user->id,
        ]);

        RegistroActividad::registrar(
            'firma_conformidad_creada',
            'Solicitud de firma «'.$s->titulo.'» para hermano ID '.$s->hermano_id.' (solicitud '.$s->id.').'
        );

        return redirect()
            ->route('secretaria.firmas-conformidad.index')
            ->with('status', 'Solicitud registrada. El hermano la verá en su portal.');
    }

    public function show(FirmaConformidadSolicitud $solicitud): View
    {
        $solicitud->load(['hermano', 'documentoArchivo', 'creadoPor']);

        return view('secretaria.firmas-conformidad.show', [
            'solicitud' => $solicitud,
        ]);
    }
}
