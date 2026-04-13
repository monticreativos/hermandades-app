<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Censo electoral de votantes</h2>
            <p class="text-sm text-slate-600 mt-1">Criterios alineados con práctica cofrade y minimización de datos personales en el PDF</p>
        </div>

        @include('informes.partials.subnav')

        <div class="card-premium border border-slate-200 border-l-4 border-l-[color:var(--color-accent)] p-5 mb-6 bg-slate-50/80 text-sm text-slate-700">
            <p class="font-semibold text-[color:var(--color-primary)] mb-2">Criterios del listado</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Estado <strong>Alta</strong>.</li>
                <li><strong>Mayor de 18 años</strong> calculado a la fecha del informe (fecha de nacimiento obligatoria).</li>
                <li><strong>Antigüedad mínima</strong> en la hermandad: años completos desde la fecha de alta (valor por defecto desde Ajustes: {{ $defAntiguedadHermandad }} año(s); puede cambiarse abajo).</li>
                <li>Opcional: excluir hermanos con <strong>morosidad económica</strong>: lotería/rifa sin cobrar o <strong>cuota ordinaria pendiente</strong> (vinculada al asiento masivo de Economía).</li>
            </ul>
        </div>

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" action="{{ route('informes.censo.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha del informe</label>
                    <input type="date" name="fecha_informe" value="{{ $fechaInforme->format('Y-m-d') }}" class="input-premium w-full">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Antigüedad mínima (años)</label>
                    <input type="number" name="antiguedad_anos" min="0" max="80" value="{{ $antiguedadAnos }}" class="input-premium w-full" title="Por defecto desde configuración de la hermandad">
                </div>
                <div class="lg:col-span-4 flex items-center gap-2 pt-6 lg:pt-0">
                    <input type="hidden" name="excluir_morosos" value="0">
                    <input type="checkbox" id="excluir_morosos" name="excluir_morosos" value="1" class="rounded border-slate-300 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" @checked($excluirMorosos)>
                    <label for="excluir_morosos" class="text-sm text-slate-700 cursor-pointer">Excluir morosos (lotería y cuota ordinaria pendiente)</label>
                </div>
                <div class="lg:col-span-3 flex flex-wrap gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Actualizar listado</button>
                    <a href="{{ route('informes.censo.pdf', request()->query()) }}" target="_blank" rel="noopener" class="btn-soft text-xs">PDF oficial</a>
                </div>
            </form>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <p class="text-sm text-slate-600"><span class="font-bold text-[color:var(--color-primary)]">{{ $total }}</span> votantes con los filtros actuales</p>
        </div>

        <div class="card-premium overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 text-left w-20">N.º</th>
                            <th class="px-4 py-3 text-left">Apellidos, nombre</th>
                            <th class="px-4 py-3 text-left">DNI (vista previa RGPD)</th>
                            <th class="px-4 py-3 text-left">Alta</th>
                            <th class="px-4 py-3 text-left">Localidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($votantes as $h)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                <td class="px-4 py-2.5 font-mono font-bold">{{ $h->numero_hermano }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $h->apellidos }}, {{ $h->nombre }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-block px-2 py-0.5 rounded bg-slate-200 text-slate-800 font-mono text-xs">{{ app(\App\Services\Informes\CensoElectoralService::class)->enmascararDni($h->dni) }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $h->fecha_alta?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $h->localidad ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500">Ningún hermano cumple los criterios con la fecha y filtros indicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($votantes as $h)
                    <div class="px-4 py-3">
                        <div class="font-mono text-xs font-bold text-[color:var(--color-primary)]">{{ $h->numero_hermano }}</div>
                        <div class="font-semibold text-slate-800">{{ $h->apellidos }}, {{ $h->nombre }}</div>
                        <div class="text-xs text-slate-500 mt-1">Alta {{ $h->fecha_alta?->format('d/m/Y') }} · {{ $h->localidad ?: '—' }}</div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center text-slate-500">Sin resultados.</div>
                @endforelse
            </div>

            @if ($votantes->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $votantes->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
