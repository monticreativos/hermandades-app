<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Apunte;
use App\Models\Asiento;
use App\Models\CuentaContable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InformeContableController extends Controller
{
    public function libroMayor(Request $request): View
    {
        $cuentas = CuentaContable::query()->orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $cuentaId = $request->integer('cuenta_contable_id') ?: null;
        $desde = $request->date('fecha_desde')?->format('Y-m-d');
        $hasta = $request->date('fecha_hasta')?->format('Y-m-d');

        $movimientos = collect();
        $cuentaSel = null;

        if ($cuentaId) {
            $cuentaSel = CuentaContable::query()->find($cuentaId);

            $movimientos = Apunte::query()
                ->with(['asiento.ejercicio', 'cuentaContable'])
                ->where('cuenta_contable_id', $cuentaId)
                ->whereHas('asiento', function ($q) use ($desde, $hasta): void {
                    if ($desde) {
                        $q->whereDate('fecha', '>=', $desde);
                    }
                    if ($hasta) {
                        $q->whereDate('fecha', '<=', $hasta);
                    }
                })
                ->get()
                ->sortBy(function (Apunte $a): array {
                    return [
                        $a->asiento->fecha->timestamp,
                        $a->asiento->numero_asiento,
                        $a->id,
                    ];
                })
                ->values();
        }

        return view('economia.informes.libro-mayor', [
            'cuentas' => $cuentas,
            'movimientos' => $movimientos,
            'cuentaSel' => $cuentaSel,
        ]);
    }

    public function balance(Request $request): View
    {
        $desde = $request->date('fecha_desde')?->format('Y-m-d');
        $hasta = $request->date('fecha_hasta')?->format('Y-m-d');

        $base = Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
            ->when($desde, fn ($qb) => $qb->whereDate('asientos.fecha', '>=', $desde))
            ->when($hasta, fn ($qb) => $qb->whereDate('asientos.fecha', '<=', $hasta));

        $filas = (clone $base)
            ->select([
                'cuentas_contables.id as cuenta_id',
                'cuentas_contables.codigo',
                'cuentas_contables.nombre',
                'cuentas_contables.tipo',
                DB::raw('SUM(apuntes.debe) as total_debe'),
                DB::raw('SUM(apuntes.haber) as total_haber'),
            ])
            ->groupBy('cuentas_contables.id', 'cuentas_contables.codigo', 'cuentas_contables.nombre', 'cuentas_contables.tipo')
            ->orderBy('cuentas_contables.codigo')
            ->get()
            ->map(function ($row): object {
                $row->saldo = (float) $row->total_debe - (float) $row->total_haber;

                return $row;
            });

        $sumDebe = $filas->sum(fn ($r) => (float) $r->total_debe);
        $sumHaber = $filas->sum(fn ($r) => (float) $r->total_haber);

        return view('economia.informes.balance', [
            'filas' => $filas,
            'sumDebe' => $sumDebe,
            'sumHaber' => $sumHaber,
        ]);
    }

    public function ivaSoportado(Request $request): View
    {
        $desde = $request->date('fecha_desde')?->format('Y-m-d');
        $hasta = $request->date('fecha_hasta')?->format('Y-m-d');

        $totalIvaSoportado = (float) Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
            ->where('cuentas_contables.codigo', 'like', '472%')
            ->when($desde, fn ($q) => $q->whereDate('asientos.fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('asientos.fecha', '<=', $hasta))
            ->sum('apuntes.debe');

        $totalIvaRepercutido = (float) Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
            ->where('cuentas_contables.codigo', 'like', '477%')
            ->when($desde, fn ($q) => $q->whereDate('asientos.fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('asientos.fecha', '<=', $hasta))
            ->sum('apuntes.haber');

        $lineasSoportado = Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
            ->where('cuentas_contables.codigo', 'like', '472%')
            ->when($desde, fn ($q) => $q->whereDate('asientos.fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('asientos.fecha', '<=', $hasta))
            ->orderByDesc('asientos.fecha')
            ->orderByDesc('asientos.numero_asiento')
            ->orderByDesc('apuntes.id')
            ->select('apuntes.*')
            ->with(['asiento.ejercicio', 'cuentaContable'])
            ->paginate(40)
            ->withQueryString();

        $lineasRepercutido = Apunte::query()
            ->join('asientos', 'asientos.id', '=', 'apuntes.asiento_id')
            ->join('cuentas_contables', 'cuentas_contables.id', '=', 'apuntes.cuenta_contable_id')
            ->where('cuentas_contables.codigo', 'like', '477%')
            ->when($desde, fn ($q) => $q->whereDate('asientos.fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('asientos.fecha', '<=', $hasta))
            ->orderByDesc('asientos.fecha')
            ->orderByDesc('asientos.numero_asiento')
            ->orderByDesc('apuntes.id')
            ->select('apuntes.*')
            ->with(['asiento.ejercicio', 'cuentaContable'])
            ->paginate(40, ['*'], 'page_repercutido')
            ->withQueryString();

        return view('economia.informes.iva-soportado', [
            'totalIvaSoportado' => $totalIvaSoportado,
            'totalIvaRepercutido' => $totalIvaRepercutido,
            'lineasSoportado' => $lineasSoportado,
            'lineasRepercutido' => $lineasRepercutido,
        ]);
    }

    public function impuestoSociedadesAuxiliar(Request $request): View
    {
        $desde = $request->date('fecha_desde')?->format('Y-m-d');
        $hasta = $request->date('fecha_hasta')?->format('Y-m-d');

        $asientos = Asiento::query()
            ->with(['apuntes.cuentaContable', 'ejercicio'])
            ->when($desde, fn ($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha', '<=', $hasta))
            ->orderBy('fecha')
            ->orderBy('numero_asiento')
            ->get();

        $exenta = 0.0;
        $noExenta = 0.0;
        $sinClasificar = 0.0;
        $detalleNoExenta = collect();
        $detalleSinClasificar = collect();

        foreach ($asientos as $asiento) {
            $ing = $asiento->totalIngresosHaber();
            if ($ing <= 0.001) {
                continue;
            }
            if ($asiento->renta_is_exenta === null) {
                $sinClasificar += $ing;
                $detalleSinClasificar->push(['asiento' => $asiento, 'importe' => $ing]);
            } elseif ($asiento->renta_is_exenta) {
                $exenta += $ing;
            } else {
                $noExenta += $ing;
                $detalleNoExenta->push(['asiento' => $asiento, 'importe' => $ing]);
            }
        }

        return view('economia.informes.impuesto-sociedades-auxiliar', [
            'totalExenta' => round($exenta, 2),
            'totalNoExenta' => round($noExenta, 2),
            'totalSinClasificar' => round($sinClasificar, 2),
            'detalleNoExenta' => $detalleNoExenta,
            'detalleSinClasificar' => $detalleSinClasificar,
        ]);
    }

    public function modelo182(Request $request): View
    {
        $año = $request->integer('año') ?: (int) now()->year;

        $agrupado = $this->agruparDonativosModelo182($año);

        return view('economia.informes.modelo-182', [
            'año' => $año,
            'agrupado' => $agrupado,
        ]);
    }

    public function modelo182Csv(Request $request): StreamedResponse
    {
        $año = $request->integer('año') ?: (int) now()->year;
        $agrupado = $this->agruparDonativosModelo182($año);

        $nombre = 'modelo_182_resumen_donativos_'.$año.'.csv';

        return response()->streamDownload(function () use ($agrupado, $año): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Ejercicio',
                'NIF',
                'Apellidos',
                'Nombre',
                'Domicilio',
                'CP',
                'Localidad',
                'Provincia',
                'Importe_eur',
                'Notas',
            ], ';');
            foreach ($agrupado as $fila) {
                fputcsv($out, [
                    $año,
                    $fila['nif'],
                    $fila['apellidos'],
                    $fila['nombre'],
                    $fila['direccion'],
                    $fila['cp'],
                    $fila['localidad'],
                    $fila['provincia'],
                    number_format($fila['importe'], 2, '.', ''),
                    'Resumen interno; verifique con su asesor frente al diseño oficial AEAT modelo 182.',
                ], ';');
            }
            fclose($out);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return Collection<int, array{nif: string, nombre: string, apellidos: string, direccion: string, cp: string, localidad: string, provincia: string, importe: float}>
     */
    private function agruparDonativosModelo182(int $año): Collection
    {
        $asientos = Asiento::query()
            ->where('apt_modelo_182', true)
            ->whereYear('fecha', $año)
            ->whereNotNull('hermano_id')
            ->with(['hermano', 'hermano.beneficiarioFiscal'])
            ->get()
            ->filter(fn (Asiento $a) => $a->hermano !== null);

        return $asientos
            ->groupBy(function (Asiento $a): int {
                return (int) ($a->hermano?->beneficiario_fiscal_hermano_id ?: $a->hermano_id);
            })
            ->map(function (Collection $grupo): array {
                /** @var Asiento $primero */
                $primero = $grupo->first();
                $hermano = $primero->hermano?->beneficiarioFiscal ?: $primero->hermano;
                if (! $hermano) {
                    return [
                        'nif' => '',
                        'nombre' => '',
                        'apellidos' => '',
                        'direccion' => '',
                        'cp' => '',
                        'localidad' => '',
                        'provincia' => '',
                        'importe' => 0.0,
                    ];
                }
                $importe = round($grupo->sum(fn (Asiento $a) => $a->importeTesoreriaPrincipal()), 2);

                return [
                    'nif' => trim((string) ($hermano?->dni ?? '')),
                    'nombre' => trim((string) ($hermano?->nombre ?? '')),
                    'apellidos' => trim((string) ($hermano?->apellidos ?? '')),
                    'direccion' => trim((string) ($hermano?->direccion ?? '')),
                    'cp' => trim((string) ($hermano?->codigo_postal ?? '')),
                    'localidad' => trim((string) ($hermano?->localidad ?? '')),
                    'provincia' => trim((string) ($hermano?->provincia ?? '')),
                    'importe' => $importe,
                ];
            })
            ->values();
    }
}
