<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Mail\ReclamacionDeudaMailable;
use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use App\Services\Contabilidad\MorosidadHermanosService;
use App\Services\Informes\ExtractoContableHermanoPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AnalisisDeudaController extends Controller
{
    public function __construct(
        private readonly MorosidadHermanosService $morosidadService,
        private readonly ExtractoContableHermanoPdfService $extractoPdf
    ) {}

    public function index(Request $request): View
    {
        $filtro = $request->string('antiguedad')->toString();
        $filtro = in_array($filtro, ['', '1y', '3y'], true) ? ($filtro === '' ? null : $filtro) : null;

        $filas = $this->morosidadService->listado($filtro);

        return view('economia.analisis-deuda.index', [
            'filas' => $filas,
            'filtroAntiguedad' => $filtro,
        ]);
    }

    public function reclamacionMasiva(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hermano_ids' => ['required', 'array', 'min:1', 'max:40'],
            'hermano_ids.*' => ['integer', 'exists:hermanos,id'],
            'antiguedad' => ['nullable', 'string', 'in:1y,3y'],
        ]);

        $hermandad = ConfiguracionHermandad::query()->firstOrFail();
        $enviados = 0;
        $omitidos = 0;

        foreach ($data['hermano_ids'] as $hid) {
            $hermano = Hermano::query()->find((int) $hid);
            if (! $hermano) {
                continue;
            }
            $email = trim((string) $hermano->email);
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $omitidos++;

                continue;
            }
            try {
                $bin = $this->extractoPdf->outputBinary($hermano, $hermandad);
                $nombrePdf = 'extracto_contable_n'.$hermano->numero_hermano.'.pdf';
                Mail::to($email)->send(new ReclamacionDeudaMailable($hermano, $hermandad, $nombrePdf, $bin));
                $enviados++;
            } catch (\Throwable) {
                $omitidos++;
            }
        }

        $ant = isset($data['antiguedad']) ? (string) $data['antiguedad'] : '';
        $q = in_array($ant, ['1y', '3y'], true) ? ['antiguedad' => $ant] : [];

        return redirect()
            ->route('economia.analisis-deuda.index', $q)
            ->with('status', "Reclamaciones enviadas: {$enviados}. Omitidos (sin email o error): {$omitidos}.");
    }
}
