<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\DocumentoGasto;
use App\Models\Proveedor;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacturasController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->string('estado')->toString();
        $desde = $request->input('fecha_desde');
        $hasta = $request->input('fecha_hasta');
        $proveedorId = $request->filled('proveedor_id') ? $request->integer('proveedor_id') : null;

        $documentos = DocumentoGasto::query()
            ->with(['asiento.ejercicio', 'proveedorRegistrado'])
            ->when($proveedorId !== null, fn ($q) => $q->where('proveedor_id', $proveedorId))
            ->when(in_array($estado, [DocumentoGasto::ESTADO_PENDIENTE, DocumentoGasto::ESTADO_PAGADA], true), fn ($q) => $q->where('estado', $estado))
            ->when($desde, fn ($q) => $q->whereDate('fecha_documento', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_documento', '<=', $hasta))
            ->orderByDesc('fecha_documento')
            ->orderBy('proveedor')
            ->orderBy('estado')
            ->paginate(20)
            ->withQueryString();

        $proveedorSeleccionado = $proveedorId
            ? Proveedor::query()->find($proveedorId)
            : null;

        $pageConfig = [
            'proveedorBaseUrl' => url('/economia/proveedores'),
            'buscarUrl' => route('economia.proveedores.buscar'),
            'storeUrl' => route('economia.proveedores.store'),
        ];

        return view('economia.facturas.index', [
            'documentos' => $documentos,
            'proveedorSeleccionado' => $proveedorSeleccionado,
            'pageConfig' => $pageConfig,
        ]);
    }

    public function galeria(Request $request): View
    {
        $desde = $request->date('fecha_desde')?->format('Y-m-d');
        $hasta = $request->date('fecha_hasta')?->format('Y-m-d');

        $documentos = DocumentoGasto::query()
            ->with(['asiento.ejercicio'])
            ->whereNotNull('archivo_path')
            ->when($desde, fn ($q) => $q->whereDate('fecha_documento', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_documento', '<=', $hasta))
            ->orderByDesc('fecha_documento')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('economia.facturas.galeria', [
            'documentos' => $documentos,
        ]);
    }

    public function descargar(DocumentoGasto $documento): StreamedResponse|RedirectResponse
    {
        try {
            return Storage::disk('local')->download(
                $documento->archivo_path,
                $documento->nombre_original
            );
        } catch (FileNotFoundException) {
            return redirect()->route('economia.facturas.index')->with('error', 'No se encuentra el archivo.');
        }
    }

    public function ver(DocumentoGasto $documento): StreamedResponse|RedirectResponse
    {
        try {
            return Storage::disk('local')->response(
                $documento->archivo_path,
                $documento->nombre_original,
                ['Content-Disposition' => 'inline; filename="'.$documento->nombre_original.'"']
            );
        } catch (FileNotFoundException) {
            return redirect()->route('economia.facturas.index')->with('error', 'No se encuentra el archivo.');
        }
    }

    public function actualizarEstado(Request $request, DocumentoGasto $documento): RedirectResponse
    {
        $data = $request->validate([
            'estado' => ['required', 'string', 'in:Pendiente,Pagada'],
        ]);
        $documento->update(['estado' => $data['estado']]);

        return back()->with('status', 'Estado del documento actualizado.');
    }
}
