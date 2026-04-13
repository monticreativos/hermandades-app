<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\InformeHistorial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InformeHistorialEconomiaController extends Controller
{
    public function index(): View
    {
        $items = InformeHistorial::query()
            ->with('usuario')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('economia.informes.historial', compact('items'));
    }

    public function descargar(InformeHistorial $informeHistorial): StreamedResponse|RedirectResponse
    {
        if (! Storage::disk('local')->exists($informeHistorial->archivo_path)) {
            return redirect()
                ->route('economia.informes.historial')
                ->with('error', 'El archivo ya no está disponible en el servidor.');
        }

        return Storage::disk('local')->download(
            $informeHistorial->archivo_path,
            'informe_'.$informeHistorial->id.'.pdf'
        );
    }
}
