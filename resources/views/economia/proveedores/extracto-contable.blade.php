<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('economia.facturas.index') }}" class="btn-soft text-xs">Volver a facturas</a>
            <a href="{{ route('economia.informes.libro-mayor', ['cuenta_contable_id' => $cuentaSel?->id]) }}" class="btn-accent text-xs">Abrir en Libro mayor</a>
        </div>
    </x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Libro mayor / Extracto — proveedor</h2>
            <p class="text-sm text-slate-600 mt-1">{{ $proveedor->etiquetaListado() }}</p>
        </div>

        @include('economia.partials.subnav')

        @if (! $cuentaSel)
            <div class="card-premium border-t-2 border-t-amber-400 p-6 text-sm text-amber-950">
                Este proveedor aún no tiene subcuenta auxiliar. Ejecute la sincronización en <a href="{{ route('ajustes.estado-sistema') }}" class="font-semibold underline">Estado del sistema</a> o espere a que el alta automática cree la cuenta.
            </div>
        @else
            <p class="text-xs text-slate-500 mb-4">La cuenta <span class="font-mono font-semibold">{{ $cuentaSel->codigo }}</span> es fija: el historial se conserva aunque cambien los datos fiscales del proveedor.</p>
            @include('economia.informes.partials.libro-mayor-movimientos', [
                'movimientos' => $movimientos,
                'cuentaSel' => $cuentaSel,
                'emptyHint' => 'Sin movimientos en esta subcuenta.',
                'vistaContabilidad' => true,
                'saldoPorVentana' => true,
            ])
        @endif
    </div>
</x-app-layout>
