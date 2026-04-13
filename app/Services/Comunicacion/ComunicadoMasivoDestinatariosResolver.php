<?php

namespace App\Services\Comunicacion;

use App\Models\ComunicadoMasivo;
use App\Models\ContactoExterno;
use App\Models\Hermano;
use App\Models\PapeletaSitio;
use App\Services\Contabilidad\MorosidadHermanosService;
use Illuminate\Support\Collection;

class ComunicadoMasivoDestinatariosResolver
{
    public function __construct(
        private readonly MorosidadHermanosService $morosidadHermanosService
    ) {}

    /** @return Collection<int, array{hermano_id:int|null,contacto_externo_id:int|null,nombre:string,email:string}> */
    public function resolver(ComunicadoMasivo $comunicado): Collection
    {
        $base = match ($comunicado->filtro_envio) {
            ComunicadoMasivo::FILTRO_CON_DEUDA => $this->conDeuda(),
            ComunicadoMasivo::FILTRO_TRAMO_COFRADIA => $this->porTramo((string) $comunicado->filtro_tramo_valor),
            ComunicadoMasivo::FILTRO_SOLO_COSTALEROS => $this->soloCostaleros(),
            ComunicadoMasivo::FILTRO_CONTACTOS_EXTERNOS => $this->contactosExternos((string) $comunicado->filtro_contacto_categoria),
            ComunicadoMasivo::FILTRO_AUDIENCIA_MIXTA => $this->audienciaMixta($comunicado),
            default => $this->todosAltaConEmail(),
        };

        return $base->unique(fn ($r) => ($r['hermano_id'] ? 'h:'.$r['hermano_id'] : 'c:'.$r['contacto_externo_id']))->values();
    }

    /** @return Collection<int, array{hermano_id:int|null,contacto_externo_id:int|null,nombre:string,email:string}> */
    private function baseAltaEmail(): Collection
    {
        return Hermano::query()
            ->where('estado', 'Alta')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get()
            ->filter(fn (Hermano $h) => filter_var(trim((string) $h->email), FILTER_VALIDATE_EMAIL) !== false)
            ->map(fn (Hermano $h) => [
                'hermano_id' => $h->id,
                'contacto_externo_id' => null,
                'nombre' => $h->nombreCompleto(),
                'email' => trim((string) $h->email),
            ])
            ->values();
    }

    private function todosAltaConEmail(): Collection
    {
        return $this->baseAltaEmail();
    }

    private function conDeuda(): Collection
    {
        $idsMorosos = $this->morosidadHermanosService->listado()
            ->pluck('hermano.id')
            ->filter()
            ->all();

        $idsCuota = Hermano::query()
            ->where('estado', 'Alta')
            ->whereIn('estado_cuota', ['Pendiente', 'Impagada'])
            ->pluck('id')
            ->all();

        $idsLoteria = Hermano::query()
            ->where('estado', 'Alta')
            ->whereHas('loteriaAsignaciones', fn ($q) => $q->where('cobrado', false))
            ->pluck('id')
            ->all();

        $ids = collect($idsMorosos)->merge($idsCuota)->merge($idsLoteria)->unique()->all();

        return $this->baseAltaEmail()->filter(fn (array $h) => in_array((int) $h['hermano_id'], $ids, true))->values();
    }

    private function porTramo(string $tramo): Collection
    {
        $tramo = trim($tramo);
        if ($tramo === '') {
            return collect();
        }

        $ids = Hermano::query()
            ->where('estado', 'Alta')
            ->whereHas('papeletas', function ($q) use ($tramo): void {
                $q->where('estado', '!=', PapeletaSitio::ESTADO_ANULADA)
                    ->where('tramo', $tramo);
            })
            ->pluck('id')
            ->all();

        return $this->baseAltaEmail()->filter(fn (array $h) => in_array((int) $h['hermano_id'], $ids, true))->values();
    }

    private function soloCostaleros(): Collection
    {
        $ids = Hermano::query()
            ->where('estado', 'Alta')
            ->whereHas('papeletas', function ($q): void {
                $q->where('estado', '!=', PapeletaSitio::ESTADO_ANULADA)
                    ->where('puesto', 'Costalero');
            })
            ->pluck('id')
            ->all();

        return $this->baseAltaEmail()->filter(fn (array $h) => in_array((int) $h['hermano_id'], $ids, true))->values();
    }

    /** @return Collection<int, array{hermano_id:int|null,contacto_externo_id:int|null,nombre:string,email:string}> */
    private function contactosExternos(string $categoria): Collection
    {
        return ContactoExterno::query()
            ->when(trim($categoria) !== '', fn ($q) => $q->where('categoria', $categoria))
            ->whereNotNull('email')
            ->get()
            ->filter(fn (ContactoExterno $c) => filter_var(trim((string) $c->email), FILTER_VALIDATE_EMAIL) !== false)
            ->map(fn (ContactoExterno $c) => [
                'hermano_id' => null,
                'contacto_externo_id' => $c->id,
                'nombre' => $c->nombre,
                'email' => trim((string) $c->email),
            ])->values();
    }

    /** @return Collection<int, array{hermano_id:int|null,contacto_externo_id:int|null,nombre:string,email:string}> */
    private function audienciaMixta(ComunicadoMasivo $comunicado): Collection
    {
        $items = collect();
        $mix = collect($comunicado->audiencia_mixta ?? []);
        if ($mix->contains('hermanos_todos')) {
            $items = $items->merge($this->todosAltaConEmail());
        }
        if ($mix->contains('hermanos_con_deuda')) {
            $items = $items->merge($this->conDeuda());
        }
        foreach ($mix->filter(fn ($v) => str_starts_with((string) $v, 'contactos_categoria:')) as $entry) {
            $items = $items->merge($this->contactosExternos((string) str_replace('contactos_categoria:', '', (string) $entry)));
        }

        foreach ((array) ($comunicado->destinatarios_individuales ?? []) as $raw) {
            $raw = (string) $raw;
            if (str_starts_with($raw, 'h:')) {
                $h = Hermano::query()->find((int) str_replace('h:', '', $raw));
                if ($h && filter_var(trim((string) $h->email), FILTER_VALIDATE_EMAIL)) {
                    $items->push(['hermano_id' => $h->id, 'contacto_externo_id' => null, 'nombre' => $h->nombreCompleto(), 'email' => trim((string) $h->email)]);
                }
            } elseif (str_starts_with($raw, 'c:')) {
                $c = ContactoExterno::query()->find((int) str_replace('c:', '', $raw));
                if ($c && filter_var(trim((string) $c->email), FILTER_VALIDATE_EMAIL)) {
                    $items->push(['hermano_id' => null, 'contacto_externo_id' => $c->id, 'nombre' => $c->nombre, 'email' => trim((string) $c->email)]);
                }
            }
        }

        return $items->values();
    }
}
