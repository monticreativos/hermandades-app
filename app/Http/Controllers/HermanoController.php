<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHermanoRequest;
use App\Http\Requests\UpdateHermanoRequest;
use App\Models\Actividad;
use App\Models\Apunte;
use App\Models\Banco;
use App\Models\ComunicadoMasivoDestinatario;
use App\Models\Hermano;
use App\Models\LoteriaAsignacion;
use App\Models\PapeletaSitio;
use App\Models\Tunica;
use App\Services\Contabilidad\CuentaAuxiliarContableService;
use App\Services\Contabilidad\CuotaHermanoEstadoService;
use App\Services\Contabilidad\CuotaPeriodicidadService;
use App\Support\RegistroActividad;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HermanoController extends Controller
{
    public function __construct(
        private readonly CuentaAuxiliarContableService $cuentaAuxiliarContableService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $query = Hermano::query();

        if (request('estado')) {
            $query->where('estado', request('estado'));
        }

        if (request('q')) {
            $search = trim((string) request('q'));
            $query->where(function ($q) use ($search): void {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        $hermanos = $query->orderBy('numero_hermano')->paginate(15)->withQueryString();
        $bancos = Banco::query()->orderBy('nombre')->get();

        return view('hermanos.index', [
            'hermanos' => $hermanos,
            'bancos' => $bancos,
            'hermanosJson' => $hermanos->getCollection()->map(fn (Hermano $h): array => [
                'id' => $h->id,
                'numero_hermano' => $h->numero_hermano,
                'nombre' => $h->nombre,
                'apellidos' => $h->apellidos,
                'dni' => $h->dni,
                'fecha_nacimiento' => optional($h->fecha_nacimiento)->format('Y-m-d'),
                'sexo' => $h->sexo,
                'direccion' => $h->direccion,
                'localidad' => $h->localidad,
                'provincia' => $h->provincia,
                'codigo_postal' => $h->codigo_postal,
                'telefono' => $h->telefono,
                'email' => $h->email,
                'banco_id' => $h->banco_id,
                'sucursal' => $h->sucursal,
                'iban' => $h->iban,
                'titular_cuenta' => $h->titular_cuenta,
                'titular_cuenta_menor' => $h->titular_cuenta_menor,
                'fecha_alta' => optional($h->fecha_alta)->format('Y-m-d'),
                'fecha_baja' => optional($h->fecha_baja)->format('Y-m-d'),
                'fecha_bautismo' => optional($h->fecha_bautismo)->format('Y-m-d'),
                'parroquia_bautismo' => $h->parroquia_bautismo,
                'estado' => $h->estado,
                'observaciones' => $h->observaciones,
                'periodicidad_pago' => $h->periodicidad_pago,
                'importe_cuota_anual_referencia' => $h->importe_cuota_anual_referencia !== null ? (string) $h->importe_cuota_anual_referencia : '',
            ])->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHermanoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (array_key_exists('importe_cuota_anual_referencia', $data) && ($data['importe_cuota_anual_referencia'] === '' || $data['importe_cuota_anual_referencia'] === null)) {
            $data['importe_cuota_anual_referencia'] = null;
        }
        if (empty($data['periodicidad_pago'])) {
            $data['periodicidad_pago'] = null;
        }

        if (empty($data['numero_hermano'])) {
            $data['numero_hermano'] = (int) Hermano::max('numero_hermano') + 1;
        }

        if (($data['estado'] ?? null) === 'Alta') {
            $data['fecha_baja'] = null;
        } elseif (empty($data['fecha_baja'])) {
            $data['fecha_baja'] = now()->toDateString();
        }

        $hermano = Hermano::create($data);

        if ($request->hasFile('partida_bautismo')) {
            $hermano->partida_bautismo_path = $request->file('partida_bautismo')
                ->store("hermanos/{$hermano->id}/partida_bautismo", 'local');
        }

        if ($request->hasFile('dni_escaneado')) {
            $hermano->dni_escaneado_path = $request->file('dni_escaneado')
                ->store("hermanos/{$hermano->id}/dni_escaneado", 'local');
        }

        $hermano->save();

        $this->cuentaAuxiliarContableService->obtenerOCrearParaHermano($hermano->fresh());

        RegistroActividad::registrar(
            Actividad::ACCION_ALTA_HERMANO,
            'Alta de hermano n.º '.$hermano->numero_hermano.': '.$hermano->nombreCompleto()
        );

        return redirect()
            ->route('hermanos.index')
            ->with('status', 'Hermano creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hermano $hermano): View
    {
        $hermano->load(['banco', 'cuotaPendienteEjercicio', 'portalCuenta', 'cuentaContable', 'beneficiarioFiscal', 'familias.miembros']);

        $extractoCuenta = $hermano->cuentaContable;
        $extractoMovimientos = collect();
        if ($extractoCuenta) {
            $extractoMovimientos = Apunte::extractoSubcuentaConSaldo($extractoCuenta->id);
        }

        $edad = $hermano->fecha_nacimiento ? $hermano->fecha_nacimiento->age : null;
        $antiguedad = $hermano->fecha_alta ? (int) $hermano->fecha_alta->diffInYears(now()) : null;

        $deudaLoteriaPendiente = LoteriaAsignacion::query()
            ->where('hermano_id', $hermano->id)
            ->where('cobrado', false)
            ->sum('importe_a_cobrar');

        $lotesPendientes = LoteriaAsignacion::query()
            ->where('hermano_id', $hermano->id)
            ->where('cobrado', false)
            ->with('loteria')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $papeletasSalida = PapeletaSitio::query()
            ->where('hermano_id', $hermano->id)
            ->with(['ejercicio', 'insignia'])
            ->get()
            ->sortByDesc(fn (PapeletaSitio $p) => $p->ejercicio?->año ?? 0)
            ->values();

        $tunicasAsignadas = Tunica::query()
            ->where('hermano_id', $hermano->id)
            ->orderBy('codigo')
            ->get();

        $cuotaPeriodicidad = app(CuotaPeriodicidadService::class);
        $importeCuotaAnualReferencia = $cuotaPeriodicidad->importeAnualReferencia($hermano);
        $periodicidadPago = $hermano->periodicidad_pago ?: CuotaPeriodicidadService::PERIODICIDAD_MENSUAL;
        $importeCuotaPorPeriodo = match ($periodicidadPago) {
            CuotaPeriodicidadService::PERIODICIDAD_MENSUAL => round($importeCuotaAnualReferencia / 12, 2),
            CuotaPeriodicidadService::PERIODICIDAD_TRIMESTRAL => round($importeCuotaAnualReferencia / 4, 2),
            CuotaPeriodicidadService::PERIODICIDAD_SEMESTRAL => round($importeCuotaAnualReferencia / 2, 2),
            CuotaPeriodicidadService::PERIODICIDAD_ANUAL => round($importeCuotaAnualReferencia, 2),
            default => round($importeCuotaAnualReferencia / 12, 2),
        };

        $comunicadosRecibidos = ComunicadoMasivoDestinatario::query()
            ->where('hermano_id', $hermano->id)
            ->with('comunicadoMasivo.creadoPor')
            ->orderByDesc('created_at')
            ->limit(60)
            ->get();

        $familia = $hermano->familias->first();
        $familiaMiembros = $familia?->miembros?->sortBy('apellidos')->values() ?? collect();
        $familiaPendientes = $familiaMiembros->filter(fn (Hermano $m) => in_array($m->estado_cuota, ['Pendiente', 'Impagada'], true))->count();
        $hermanosRelacionables = Hermano::query()
            ->where('id', '!=', $hermano->id)
            ->orderBy('apellidos')
            ->limit(300)
            ->get(['id', 'numero_hermano', 'nombre', 'apellidos']);

        return view('hermanos.show', compact(
            'hermano',
            'edad',
            'antiguedad',
            'deudaLoteriaPendiente',
            'lotesPendientes',
            'papeletasSalida',
            'tunicasAsignadas',
            'importeCuotaAnualReferencia',
            'periodicidadPago',
            'importeCuotaPorPeriodo',
            'extractoCuenta',
            'extractoMovimientos',
            'comunicadosRecibidos',
            'familia',
            'familiaMiembros',
            'familiaPendientes',
            'hermanosRelacionables',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHermanoRequest $request, Hermano $hermano): RedirectResponse
    {
        $data = $request->validated();
        if (array_key_exists('importe_cuota_anual_referencia', $data) && ($data['importe_cuota_anual_referencia'] === '' || $data['importe_cuota_anual_referencia'] === null)) {
            $data['importe_cuota_anual_referencia'] = null;
        }
        if (empty($data['periodicidad_pago'])) {
            $data['periodicidad_pago'] = null;
        }

        if (empty($data['numero_hermano'])) {
            unset($data['numero_hermano']);
        }

        if (($data['estado'] ?? null) === 'Alta') {
            $data['fecha_baja'] = null;
        } elseif (empty($data['fecha_baja']) && $hermano->estado !== 'Alta') {
            $data['fecha_baja'] = $hermano->fecha_baja?->toDateString() ?? now()->toDateString();
        }

        $hermano->update($data);

        if (in_array($hermano->estado, ['Baja', 'Difunto'], true)) {
            $hermano->forceFill([
                'estado_cuota' => CuotaHermanoEstadoService::ESTADO_AL_CORRIENTE,
                'cuota_pendiente_ejercicio_id' => null,
            ])->save();
        }

        if ($request->hasFile('partida_bautismo')) {
            if ($hermano->partida_bautismo_path) {
                Storage::disk('local')->delete($hermano->partida_bautismo_path);
            }
            $hermano->partida_bautismo_path = $request->file('partida_bautismo')
                ->store("hermanos/{$hermano->id}/partida_bautismo", 'local');
        }

        if ($request->hasFile('dni_escaneado')) {
            if ($hermano->dni_escaneado_path) {
                Storage::disk('local')->delete($hermano->dni_escaneado_path);
            }
            $hermano->dni_escaneado_path = $request->file('dni_escaneado')
                ->store("hermanos/{$hermano->id}/dni_escaneado", 'local');
        }

        $hermano->save();

        $this->cuentaAuxiliarContableService->sincronizarEtiquetaCuentaHermano($hermano->fresh());

        return redirect()
            ->route('hermanos.index')
            ->with('status', 'Hermano actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hermano $hermano): RedirectResponse
    {
        if ($hermano->partida_bautismo_path) {
            Storage::disk('local')->delete($hermano->partida_bautismo_path);
        }
        if ($hermano->dni_escaneado_path) {
            Storage::disk('local')->delete($hermano->dni_escaneado_path);
        }

        RegistroActividad::registrar(
            Actividad::ACCION_ELIMINAR_HERMANO,
            'Eliminación de hermano n.º '.$hermano->numero_hermano.': '.$hermano->nombreCompleto()
        );

        $hermano->delete();

        return redirect()
            ->route('hermanos.index')
            ->with('status', 'Hermano eliminado correctamente.');
    }

    public function descargarDocumento(Hermano $hermano, string $tipo): Response|RedirectResponse
    {
        $path = match ($tipo) {
            'partida_bautismo' => $hermano->partida_bautismo_path,
            'dni_escaneado' => $hermano->dni_escaneado_path,
            default => null,
        };

        if (! $path) {
            return redirect()->route('hermanos.show', $hermano)->with('status', 'El documento no existe.');
        }

        try {
            return Storage::disk('local')->download($path);
        } catch (FileNotFoundException) {
            return redirect()->route('hermanos.show', $hermano)->with('status', 'No se encuentra el archivo en el almacenamiento.');
        }
    }
}
