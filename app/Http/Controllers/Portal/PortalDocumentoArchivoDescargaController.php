<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\DocumentoArchivo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PortalDocumentoArchivoDescargaController extends Controller
{
    public function __invoke(DocumentoArchivo $documento): Response
    {
        abort_unless(
            $documento->nivel_acceso === DocumentoArchivo::NIVEL_PUBLICO_HERMANOS,
            403
        );

        if (! Storage::disk('local')->exists($documento->archivo_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $documento->archivo_path,
            $documento->nombre_original ?: 'documento',
            ['Content-Type' => $documento->mime ?: 'application/octet-stream']
        );
    }
}
