<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Libro mayor</h2>
        </div>

        @include('economia.partials.subnav')

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Cuenta</label>
                    <select name="cuenta_contable_id" class="input-premium w-full">
                        <option value="">— Seleccione —</option>
                        @foreach ($cuentas as $c)
                            <option value="{{ $c->id }}" @selected((string) request('cuenta_contable_id') === (string) $c->id)>
                                {{ $c->codigo }} — {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input-premium w-full">
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Ver movimientos</button>
                    <a href="{{ route('economia.informes.libro-mayor') }}" class="btn-soft text-xs">Limpiar</a>
                </div>
            </form>
        </div>

        @include('economia.informes.partials.libro-mayor-movimientos', [
            'movimientos' => $movimientos,
            'cuentaSel' => $cuentaSel,
            'emptyHint' => request()->filled('cuenta_contable_id') ? 'Sin movimientos en el periodo.' : 'Elija una cuenta y pulse «Ver movimientos».',
        ])
    </div>
</x-app-layout>
