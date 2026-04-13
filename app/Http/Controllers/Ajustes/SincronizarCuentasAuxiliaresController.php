<?php

namespace App\Http\Controllers\Ajustes;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Services\Contabilidad\CuentaAuxiliarContableService;
use App\Support\RegistroActividad;
use Illuminate\Http\RedirectResponse;

class SincronizarCuentasAuxiliaresController extends Controller
{
    public function store(CuentaAuxiliarContableService $servicio): RedirectResponse
    {
        $r = $servicio->sincronizarMasivo();

        RegistroActividad::registrar(
            Actividad::ACCION_SINCRONIZAR_CUENTAS_AUXILIARES,
            'Sincronización cuentas auxiliares: hermanos creados '.$r['hermanos_creados'].', proveedores '.$r['proveedores_creados'].'.'
        );

        return redirect()
            ->route('ajustes.estado-sistema')
            ->with('status', 'Cuentas auxiliares sincronizadas: '.$r['hermanos_creados'].' hermanos y '.$r['proveedores_creados'].' proveedores con cuenta nueva. Omitidos (ya tenían cuenta): '.$r['hermanos_omitidos'].' hermanos, '.$r['proveedores_omitidos'].' proveedores.');
    }
}
