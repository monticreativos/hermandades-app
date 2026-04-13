<?php

namespace App\Services\Contabilidad;

use App\Models\Ejercicio;
use App\Models\Hermano;
use App\Models\RemesaRecibo;
use App\Models\RemesaSepa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RemesaSepaGeneracionService
{
    public function __construct(
        private readonly SepaPain008Generator $sepaPain008Generator,
        private readonly CuotaPeriodicidadService $periodicidadService
    ) {}

    /**
     * @return array{remesa: RemesaSepa, xml: string}
     */
    public function generarRemesa(
        Ejercicio $ejercicio,
        int $año,
        int $mes,
        string $fechaCobro,
        ?User $user = null,
    ): array {
        $mes = max(1, min(12, $mes));
        $trimestre = (int) ceil($mes / 3);

        $hermanos = Hermano::query()
            ->where('estado', 'Alta')
            ->whereNotNull('iban')
            ->where('iban', '!=', '')
            ->orderBy('numero_hermano')
            ->get()
            ->filter(function (Hermano $h): bool {
                $iban = preg_replace('/\s+/', '', (string) $h->iban);

                return strlen($iban) >= 15;
            });

        $lineas = $this->periodicidadService->lineasParaRemesa($año, $mes, $hermanos);
        if ($lineas->isEmpty()) {
            throw new \RuntimeException('No hay recibos pendientes para este periodo según la periodicidad configurada.');
        }

        $etiqueta = sprintf('Remesa %04d-%02d', $año, $mes);
        $meta = $this->sepaPain008Generator->generarMultilinea(
            $lineas,
            $fechaCobro,
            'Cuotas cofrades '.$etiqueta
        );

        $total = round($lineas->sum(fn (array $r) => (float) $r['importe']), 2);

        return DB::transaction(function () use ($ejercicio, $user, $año, $mes, $trimestre, $etiqueta, $lineas, $meta, $total): array {
            $remesa = RemesaSepa::query()->create([
                'ejercicio_id' => $ejercicio->id,
                'user_id' => $user?->id,
                'fecha_emision' => Carbon::today(),
                'año_referencia' => $año,
                'mes_referencia' => $mes,
                'trimestre_referencia' => $trimestre,
                'etiqueta_periodo' => $etiqueta,
                'numero_recibos' => $lineas->count(),
                'importe_total' => $total,
                'msg_id' => $meta['msg_id'],
                'pmt_inf_id' => $meta['pmt_inf_id'],
                'estado' => RemesaSepa::ESTADO_ENVIADA,
            ]);

            $dir = 'remesas/'.$remesa->id;
            $path = $dir.'/pain008_'.$remesa->id.'.xml';
            Storage::disk('local')->put($path, $meta['xml']);
            $remesa->update(['archivo_xml_path' => $path]);

            foreach ($lineas as $row) {
                RemesaRecibo::query()->create([
                    'remesa_id' => $remesa->id,
                    'hermano_id' => $row['hermano']->id,
                    'end_to_end_id' => $row['end_to_end_id'],
                    'periodo_clave' => $row['periodo_clave'],
                    'importe' => $row['importe'],
                    'estado' => RemesaRecibo::ESTADO_PENDIENTE_BANCO,
                ]);
            }

            return [
                'remesa' => $remesa->fresh(['recibos.hermano']),
                'xml' => $meta['xml'],
            ];
        });
    }
}
