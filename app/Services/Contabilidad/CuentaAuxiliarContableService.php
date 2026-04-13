<?php

namespace App\Services\Contabilidad;

use App\Models\CuentaContable;
use App\Models\Hermano;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;

class CuentaAuxiliarContableService
{
    public const PREFIJO_CUENTA_HERMANO = '430';

    /** Subcuentas de proveedores bajo el grupo 410 (PGC — acreedores por prestaciones). */
    public const PREFIJO_CUENTA_PROVEEDOR = '410';

    private function sufijoSeisDigitos(int $valor): string
    {
        return str_pad((string) max(0, $valor), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Código auxiliar hermano: 430.XXXXXX (número de hermano en 6 dígitos).
     * Si hubiera colisión con otra persona, 430.XXXXXX con id del hermano en 6 dígitos.
     */
    public function codigoPropuestoHermano(Hermano $hermano): string
    {
        return self::PREFIJO_CUENTA_HERMANO.'.'.$this->sufijoSeisDigitos((int) $hermano->numero_hermano);
    }

    public function codigoReservaPorIdHermano(Hermano $hermano): string
    {
        return self::PREFIJO_CUENTA_HERMANO.'.'.$this->sufijoSeisDigitos($hermano->id);
    }

    public function codigoPropuestoProveedor(Proveedor $proveedor): string
    {
        return self::PREFIJO_CUENTA_PROVEEDOR.'.'.$this->sufijoSeisDigitos($proveedor->id);
    }

    /** Colisión: código alternativo bajo el mismo grupo 410. */
    public function codigoAlternativoProveedorPorId(Proveedor $proveedor): string
    {
        return self::PREFIJO_CUENTA_PROVEEDOR.'.9'.str_pad((string) $proveedor->id, 5, '0', STR_PAD_LEFT);
    }

    public function nombreCuentaHermano(Hermano $hermano): string
    {
        return $hermano->nombreCompleto();
    }

    public function nombreCuentaProveedor(Proveedor $proveedor): string
    {
        $nif = $proveedor->nif_cif ? ' ('.strtoupper($proveedor->nif_cif).')' : '';

        return 'Proveedor — '.$proveedor->razon_social.$nif;
    }

    public function obtenerOCrearParaHermano(Hermano $hermano): CuentaContable
    {
        $hermano->loadMissing('cuentaContable');
        if ($hermano->cuenta_contable_id && $hermano->cuentaContable) {
            return $hermano->cuentaContable;
        }

        return DB::transaction(function () use ($hermano): CuentaContable {
            /** @var Hermano $h */
            $h = Hermano::query()->whereKey($hermano->id)->lockForUpdate()->firstOrFail();
            $h->loadMissing('cuentaContable');
            if ($h->cuenta_contable_id && $h->cuentaContable) {
                return $h->cuentaContable;
            }

            $codigo = $this->codigoPropuestoHermano($h);
            $existente = CuentaContable::query()->where('codigo', $codigo)->first();
            if ($existente) {
                $otro = Hermano::query()
                    ->where('cuenta_contable_id', $existente->id)
                    ->where('id', '!=', $h->id)
                    ->exists();
                if ($otro) {
                    $codigo = $this->codigoReservaPorIdHermano($h);
                    $existente = CuentaContable::query()->where('codigo', $codigo)->first();
                } else {
                    $existente->update([
                        'nombre' => $this->nombreCuentaHermano($h),
                        'hermano_id' => $h->id,
                        'proveedor_id' => null,
                    ]);
                    $h->forceFill(['cuenta_contable_id' => $existente->id])->save();

                    return $existente->fresh();
                }
            }

            if ($existente) {
                $existente->update([
                    'nombre' => $this->nombreCuentaHermano($h),
                    'hermano_id' => $h->id,
                    'proveedor_id' => null,
                ]);
                $h->forceFill(['cuenta_contable_id' => $existente->id])->save();

                return $existente->fresh();
            }

            $cuenta = CuentaContable::query()->create([
                'codigo' => $codigo,
                'nombre' => $this->nombreCuentaHermano($h),
                'tipo' => 'Activo',
                'hermano_id' => $h->id,
                'proveedor_id' => null,
            ]);

            $h->forceFill(['cuenta_contable_id' => $cuenta->id])->save();

            return $cuenta;
        });
    }

    public function obtenerOCrearParaProveedor(Proveedor $proveedor): CuentaContable
    {
        $proveedor->loadMissing('cuentaContable');
        if ($proveedor->cuenta_contable_id && $proveedor->cuentaContable) {
            return $proveedor->cuentaContable;
        }

        return DB::transaction(function () use ($proveedor): CuentaContable {
            /** @var Proveedor $p */
            $p = Proveedor::query()->whereKey($proveedor->id)->lockForUpdate()->firstOrFail();
            $p->loadMissing('cuentaContable');
            if ($p->cuenta_contable_id && $p->cuentaContable) {
                return $p->cuentaContable;
            }

            $codigo = $this->codigoPropuestoProveedor($p);
            $existente = CuentaContable::query()->where('codigo', $codigo)->first();
            if ($existente) {
                $otro = Proveedor::query()
                    ->where('cuenta_contable_id', $existente->id)
                    ->where('id', '!=', $p->id)
                    ->exists();
                if ($otro) {
                    $codigo = $this->codigoAlternativoProveedorPorId($p);
                    $existente = CuentaContable::query()->where('codigo', $codigo)->first();
                } else {
                    $existente->update([
                        'nombre' => $this->nombreCuentaProveedor($p),
                        'proveedor_id' => $p->id,
                        'hermano_id' => null,
                    ]);
                    $p->forceFill(['cuenta_contable_id' => $existente->id])->save();

                    return $existente->fresh();
                }
            }

            if ($existente) {
                $existente->update([
                    'nombre' => $this->nombreCuentaProveedor($p),
                    'proveedor_id' => $p->id,
                    'hermano_id' => null,
                ]);
                $p->forceFill(['cuenta_contable_id' => $existente->id])->save();

                return $existente->fresh();
            }

            $cuenta = CuentaContable::query()->create([
                'codigo' => $codigo,
                'nombre' => $this->nombreCuentaProveedor($p),
                'tipo' => 'Pasivo',
                'proveedor_id' => $p->id,
                'hermano_id' => null,
            ]);

            $p->forceFill(['cuenta_contable_id' => $cuenta->id])->save();

            return $cuenta;
        });
    }

    /**
     * Actualiza solo el nombre descriptivo de la cuenta (el código contable no cambia nunca).
     */
    public function sincronizarEtiquetaCuentaHermano(Hermano $hermano): void
    {
        if (! $hermano->cuenta_contable_id) {
            return;
        }
        $cuenta = $hermano->cuentaContable;
        if (! $cuenta) {
            return;
        }
        $nombre = $this->nombreCuentaHermano($hermano);
        $payload = ['nombre' => $nombre];
        if ($cuenta->hermano_id !== $hermano->id || $cuenta->proveedor_id !== null) {
            $payload['hermano_id'] = $hermano->id;
            $payload['proveedor_id'] = null;
        }
        $cuenta->update($payload);
    }

    public function sincronizarEtiquetaCuentaProveedor(Proveedor $proveedor): void
    {
        if (! $proveedor->cuenta_contable_id) {
            return;
        }
        $cuenta = $proveedor->cuentaContable;
        if (! $cuenta) {
            return;
        }
        $nombre = $this->nombreCuentaProveedor($proveedor);
        $payload = ['nombre' => $nombre];
        if ($cuenta->proveedor_id !== $proveedor->id || $cuenta->hermano_id !== null) {
            $payload['proveedor_id'] = $proveedor->id;
            $payload['hermano_id'] = null;
        }
        $cuenta->update($payload);
    }

    /**
     * Rellena hermano_id / proveedor_id en cuentas ya enlazadas desde las tablas maestras.
     */
    public function rellenarTrazabilidadInversaEnCuentas(): void
    {
        foreach (Hermano::query()->whereNotNull('cuenta_contable_id')->cursor() as $h) {
            CuentaContable::query()->whereKey($h->cuenta_contable_id)->update([
                'hermano_id' => $h->id,
                'proveedor_id' => null,
            ]);
        }

        foreach (Proveedor::query()->whereNotNull('cuenta_contable_id')->cursor() as $p) {
            CuentaContable::query()->whereKey($p->cuenta_contable_id)->update([
                'proveedor_id' => $p->id,
                'hermano_id' => null,
            ]);
        }
    }

    /**
     * @return array{hermanos_creados: int, proveedores_creados: int, hermanos_omitidos: int, proveedores_omitidos: int}
     */
    public function sincronizarMasivo(): array
    {
        $hc = 0;
        $pc = 0;
        $ho = 0;
        $po = 0;

        foreach (Hermano::query()->orderBy('id')->cursor() as $hermano) {
            if ($hermano->cuenta_contable_id) {
                $ho++;

                continue;
            }
            $this->obtenerOCrearParaHermano($hermano);
            $hc++;
        }

        foreach (Proveedor::query()->orderBy('id')->cursor() as $proveedor) {
            if ($proveedor->cuenta_contable_id) {
                $po++;

                continue;
            }
            $this->obtenerOCrearParaProveedor($proveedor);
            $pc++;
        }

        $this->rellenarTrazabilidadInversaEnCuentas();

        return [
            'hermanos_creados' => $hc,
            'proveedores_creados' => $pc,
            'hermanos_omitidos' => $ho,
            'proveedores_omitidos' => $po,
        ];
    }
}
