<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretaria\StoreDocumentoArchivoRequest;
use App\Models\DocumentoArchivoJustificante;
use App\Models\DocumentoArchivo;
use App\Services\IA\OpenRouterResumenDocumentoService;
use App\Support\RegistroActividad;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoDigitalController extends Controller
{
    public function index(Request $request): View
    {
        $q = DocumentoArchivo::query()->with('subidoPor')->orderByDesc('created_at');

        if ($request->filled('categoria')) {
            $q->where('categoria', $request->string('categoria'));
        }
        if ($request->filled('nivel')) {
            $q->where('nivel_acceso', $request->string('nivel'));
        }
        if ($request->filled('q')) {
            $s = '%'.$request->string('q').'%';
            $q->where(function ($w) use ($s): void {
                $w->where('titulo', 'like', $s)
                    ->orWhere('descripcion', 'like', $s);
            });
        }

        $documentos = $q->paginate(20)->withQueryString();

        return view('secretaria.archivo-digital.index', [
            'documentos' => $documentos,
            'categorias' => DocumentoArchivo::etiquetasCategoria(),
            'niveles' => DocumentoArchivo::etiquetasNivel(),
            'documentosAdjuntables' => DocumentoArchivo::query()->orderByDesc('created_at')->limit(100)->get(['id', 'titulo']),
        ]);
    }

    public function create(): View
    {
        return view('secretaria.archivo-digital.create', [
            'categorias' => DocumentoArchivo::etiquetasCategoria(),
            'niveles' => DocumentoArchivo::etiquetasNivel(),
        ]);
    }

    public function store(StoreDocumentoArchivoRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $file = $request->file('archivo');
        $path = $file->store('archivo-digital', 'local');

        $doc = DocumentoArchivo::query()->create([
            'titulo' => $request->validated('titulo'),
            'categoria' => $request->validated('categoria'),
            'nivel_acceso' => $request->validated('nivel_acceso'),
            'descripcion' => $request->validated('descripcion'),
            'archivo_path' => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'tamano_bytes' => $file->getSize(),
            'subido_por_user_id' => $user->id,
        ]);

        RegistroActividad::registrar(
            'documento_archivo_subido',
            'Documento de archivo «'.$doc->titulo.'» ('.$doc->categoria.', '.$doc->nivel_acceso.'), ID '.$doc->id.'.'
        );

        return redirect()
            ->route('secretaria.archivo-digital.index')
            ->with('status', 'Documento incorporado al archivo digital.');
    }

    public function destroy(DocumentoArchivo $documento): RedirectResponse
    {
        Storage::disk('local')->delete($documento->archivo_path);
        $documento->delete();

        return redirect()
            ->route('secretaria.archivo-digital.index')
            ->with('status', 'Documento eliminado.');
    }

    /**
     * @throws FileNotFoundException
     */
    public function descargar(DocumentoArchivo $documento): StreamedResponse
    {
        if (! Storage::disk('local')->exists($documento->archivo_path)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $documento->archivo_path,
            $documento->nombre_original ?: basename($documento->archivo_path),
            ['Content-Type' => $documento->mime ?: 'application/octet-stream']
        );
    }

    public function vincularJustificante(Request $request, DocumentoArchivo $documento): RedirectResponse
    {
        $data = $request->validate([
            'documento_hijo_id' => ['required', 'integer', 'exists:documentos_archivo,id'],
        ]);

        if ((int) $data['documento_hijo_id'] === (int) $documento->id) {
            return back()->withErrors(['documento_hijo_id' => 'No puedes adjuntar el mismo documento como justificante.']);
        }

        DocumentoArchivoJustificante::query()->firstOrCreate([
            'documento_padre_id' => $documento->id,
            'documento_hijo_id' => (int) $data['documento_hijo_id'],
        ]);

        return back()->with('status', 'Justificante vinculado al documento.');
    }

    public function resumir(DocumentoArchivo $documento, OpenRouterResumenDocumentoService $resumidor): RedirectResponse
    {
        try {
            $resumen = $resumidor->resumirDesdeArchivo($documento->archivo_path, $documento->mime);
            $documento->update(['resumen_ia' => $resumen]);
        } catch (\Throwable $e) {
            return back()->withErrors(['resumen_ia' => $e->getMessage()]);
        }

        return back()->with('status', 'Resumen IA generado y guardado.');
    }
}
