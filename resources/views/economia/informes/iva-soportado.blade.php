<x-app-layout>
    <x-slot name="header"></x-slot>
    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">IVA</h2>
            <p class="mt-1 text-sm text-slate-600">Control conjunto de IVA soportado (472) e IVA repercutido (477) en el rango elegido.</p>
        </div>
        @include('economia.partials.subnav')

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input-premium w-full">
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Calcular</button>
                    <a href="{{ route('economia.informes.iva-soportado') }}" class="btn-soft text-xs">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <p class="text-xs font-bold uppercase text-slate-500">Total IVA soportado (debe 472)</p>
                <p class="mt-2 text-3xl font-bold text-rose-700">{{ number_format($totalIvaSoportado, 2, ',', '.') }} €</p>
            </div>
            <div class="card-premium border-t-2 border-t-emerald-300 p-6">
                <p class="text-xs font-bold uppercase text-slate-500">Total IVA repercutido (haber 477)</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($totalIvaRepercutido, 2, ',', '.') }} €</p>
            </div>
        </div>

        <div class="hidden md:block card-premium overflow-hidden mb-6">
            <div class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600 bg-slate-50">Detalle IVA soportado (472)</div>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Asiento</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3 text-right">Debe 472</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lineasSoportado as $ap)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-2 whitespace-nowrap">{{ $ap->asiento->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-mono">{{ $ap->asiento->numero_asiento }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $ap->concepto_detalle ?: $ap->asiento->glosa }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-rose-700">{{ number_format((float) $ap->debe, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay apuntes en 472 en este rango.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="md:hidden space-y-3">
            @forelse ($lineasSoportado as $ap)
                <div class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)]/50">
                    <p class="text-xs text-slate-500">{{ $ap->asiento->fecha->format('d/m/Y') }} · Asiento {{ $ap->asiento->numero_asiento }}</p>
                    <p class="mt-1 font-semibold text-rose-700">{{ number_format((float) $ap->debe, 2, ',', '.') }} €</p>
                    <p class="text-sm text-slate-600 mt-1">{{ $ap->concepto_detalle ?: $ap->asiento->glosa }}</p>
                </div>
            @empty
                <p class="text-slate-500 text-sm">Sin movimientos.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $lineasSoportado->links() }}</div>

        <div class="hidden md:block card-premium overflow-hidden mt-6">
            <div class="px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-600 bg-slate-50">Detalle IVA repercutido (477)</div>
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Asiento</th>
                        <th class="px-4 py-3">Concepto</th>
                        <th class="px-4 py-3 text-right">Haber 477</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lineasRepercutido as $ap)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-2 whitespace-nowrap">{{ $ap->asiento->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-mono">{{ $ap->asiento->numero_asiento }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $ap->concepto_detalle ?: $ap->asiento->glosa }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-emerald-700">{{ number_format((float) $ap->haber, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay apuntes en 477 en este rango.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="md:hidden space-y-3 mt-6">
            @forelse ($lineasRepercutido as $ap)
                <div class="card-premium p-4 border-t-2 border-t-emerald-300">
                    <p class="text-xs text-slate-500">{{ $ap->asiento->fecha->format('d/m/Y') }} · Asiento {{ $ap->asiento->numero_asiento }}</p>
                    <p class="mt-1 font-semibold text-emerald-700">{{ number_format((float) $ap->haber, 2, ',', '.') }} €</p>
                    <p class="text-sm text-slate-600 mt-1">{{ $ap->concepto_detalle ?: $ap->asiento->glosa }}</p>
                </div>
            @empty
                <p class="text-slate-500 text-sm">Sin movimientos de IVA repercutido.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $lineasRepercutido->links() }}</div>
    </div>
</x-app-layout>
