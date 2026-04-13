<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('ajustes.index') }}" class="btn-soft text-xs">Volver a Ajustes</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6 max-w-4xl">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Estado del sistema</h2>
                <p class="text-sm text-slate-600 mt-1">Comprobaciones rápidas para secretaría y administración (Versión 1.1)</p>
            </div>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-2">Verificación — subcuentas auxiliares</h3>
                <div class="flex items-start gap-3 mb-4">
                    <span class="shrink-0 inline-flex h-10 w-10 items-center justify-center rounded-full {{ $cuentasAuxiliares['ok'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                        @if ($cuentasAuxiliares['ok'])
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/></svg>
                        @endif
                    </span>
                    <div class="text-sm text-slate-700">
                        <p><strong>Hermanos sin cuenta vinculada:</strong> {{ $cuentasAuxiliares['hermanos_sin'] }}</p>
                        <p><strong>Proveedores sin cuenta vinculada:</strong> {{ $cuentasAuxiliares['proveedores_sin'] }}</p>
                        @if (! $cuentasAuxiliares['ok'])
                            <p class="text-amber-950 mt-2">Use el botón de abajo para crear y enlazar subcuentas automáticamente (no altera códigos ya existentes).</p>
                        @endif
                    </div>
                </div>
                @if ($cuentasAuxiliares['hermanos_muestra']->isNotEmpty())
                    <p class="text-xs font-bold uppercase text-slate-500 mb-2">Muestra hermanos</p>
                    <ul class="text-sm text-slate-600 mb-3 space-y-1">
                        @foreach ($cuentasAuxiliares['hermanos_muestra'] as $h)
                            <li><a href="{{ route('hermanos.show', $h) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">N.º {{ $h->numero_hermano }} — {{ $h->nombre }} {{ $h->apellidos }}</a></li>
                        @endforeach
                    </ul>
                @endif
                @if ($cuentasAuxiliares['proveedores_muestra']->isNotEmpty())
                    <p class="text-xs font-bold uppercase text-slate-500 mb-2">Muestra proveedores</p>
                    <ul class="text-sm text-slate-600 mb-3 space-y-1">
                        @foreach ($cuentasAuxiliares['proveedores_muestra'] as $p)
                            <li>{{ $p->razon_social }} @if($p->nif_cif) <span class="font-mono text-xs">({{ $p->nif_cif }})</span> @endif</li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-2">Contabilidad — cuentas auxiliares</h3>
                <p class="text-sm text-slate-600 mb-4">Crea y vincula subcuentas <span class="font-mono text-xs">430.XXXXXX</span> para hermanos y <span class="font-mono text-xs">410.XXXXXX</span> para proveedores que aún no las tengan. Rellena la trazabilidad inversa en el plan. No modifica códigos de cuentas ya asignadas.</p>
                <form method="POST" action="{{ route('ajustes.sincronizar-cuentas-auxiliares') }}" onsubmit="return confirm('¿Ejecutar sincronización de cuentas auxiliares?');">
                    @csrf
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Sincronizar cuentas auxiliares</button>
                </form>
            </section>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-3">Almacenamiento público</h3>
                <div class="flex items-start gap-3">
                    <span class="shrink-0 inline-flex h-10 w-10 items-center justify-center rounded-full {{ $storage['ok'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        @if ($storage['ok'])
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </span>
                    <p class="text-sm text-slate-700">{{ $storage['mensaje'] }}</p>
                </div>
            </section>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-3">Ejercicios contables</h3>
                <div class="flex items-start gap-3">
                    <span class="shrink-0 inline-flex h-10 w-10 items-center justify-center rounded-full {{ $ejercicios['ok'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">
                        @if ($ejercicios['ok'])
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/></svg>
                        @endif
                    </span>
                    <p class="text-sm text-slate-700">{{ $ejercicios['mensaje'] }}</p>
                </div>
            </section>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-3">Datos críticos incompletos</h3>
                <p class="text-sm text-slate-600 mb-4">Hermanos sin DNI o sin fecha de nacimiento (afectan censo electoral y RGPD).</p>
                @if ($hermanosIncompletos->isEmpty())
                    <p class="text-sm font-semibold text-emerald-800">No se detectan incidencias en los primeros 200 registros revisados.</p>
                @else
                    <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 text-sm max-h-72 overflow-y-auto">
                        @foreach ($hermanosIncompletos as $h)
                            <li class="px-4 py-3 flex flex-wrap justify-between gap-2">
                                <span class="font-semibold text-[color:var(--color-primary)]">N.º {{ $h->numero_hermano }} — {{ $h->nombre }} {{ $h->apellidos }}</span>
                                <span class="text-xs text-slate-500">{{ $h->estado }}</span>
                                <span class="text-xs text-rose-700 w-full">
                                    @if (blank($h->dni)) Sin DNI. @endif
                                    @if (! $h->fecha_nacimiento) Sin fecha de nacimiento. @endif
                                </span>
                                <a href="{{ route('hermanos.show', $h) }}" class="text-xs font-semibold text-[color:var(--color-accent)]">Abrir ficha</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
