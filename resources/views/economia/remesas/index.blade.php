<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Remesas SEPA (pain.008)</h2>
                <p class="text-sm text-slate-600 mt-1">Historial de cuadernos enviados al banco y punto de entrada a la conciliación automática.</p>
            </div>
            <a href="{{ route('economia.remesas.create') }}" class="btn-accent uppercase tracking-wider text-xs shrink-0">Nueva remesa</a>
        </div>

        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <article class="card-premium p-4 border-t-2 border-t-emerald-400">
                <p class="text-xs font-bold uppercase text-slate-500">Recibos cobrados (total)</p>
                <p class="text-2xl font-mono font-bold text-[color:var(--color-primary)] mt-1">{{ number_format($statsGlobales['cobrados']) }}</p>
            </article>
            <article class="card-premium p-4 border-t-2 border-t-amber-400">
                <p class="text-xs font-bold uppercase text-slate-500">Pendientes de banco</p>
                <p class="text-2xl font-mono font-bold text-[color:var(--color-primary)] mt-1">{{ number_format($statsGlobales['pendientes']) }}</p>
            </article>
            <article class="card-premium p-4 border-t-2 border-t-rose-400">
                <p class="text-xs font-bold uppercase text-slate-500">Devueltos</p>
                <p class="text-2xl font-mono font-bold text-[color:var(--color-primary)] mt-1">{{ number_format($statsGlobales['devueltos']) }}</p>
                <a href="{{ route('economia.remesas.devoluciones') }}" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline mt-2 inline-block">Ver listado</a>
            </article>
        </div>

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Periodo</th>
                            <th class="px-4 py-3">Ejercicio</th>
                            <th class="px-4 py-3 text-right">Recibos</th>
                            <th class="px-4 py-3 text-right">Importe</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3 w-24"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($remesas as $r)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $r->fecha_emision?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium text-[color:var(--color-primary)]">{{ $r->etiqueta_periodo }}</td>
                                <td class="px-4 py-3">{{ $r->ejercicio?->año ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-mono">{{ $r->numero_recibos }}</td>
                                <td class="px-4 py-3 text-right font-mono">{{ number_format((float) $r->importe_total, 2, ',', '.') }} €</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                                        @class([
                                            'bg-slate-100 text-slate-700' => $r->estado === \App\Models\RemesaSepa::ESTADO_ENVIADA,
                                            'bg-amber-50 text-amber-900 border border-amber-200' => $r->estado === \App\Models\RemesaSepa::ESTADO_CONCILIACION_PARCIAL,
                                            'bg-emerald-50 text-emerald-900 border border-emerald-200' => $r->estado === \App\Models\RemesaSepa::ESTADO_CONCILIADA,
                                            'bg-slate-50 text-slate-600' => $r->estado === \App\Models\RemesaSepa::ESTADO_BORRADOR,
                                        ])">{{ $r->estado }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('economia.remesas.show', $r) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Abrir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay remesas registradas. Genere la primera con el asistente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($remesas as $r)
                    <article class="p-4">
                        <div class="flex justify-between gap-2">
                            <div>
                                <p class="font-bold text-[color:var(--color-primary)]">{{ $r->etiqueta_periodo }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $r->fecha_emision?->format('d/m/Y') }} · Ej. {{ $r->ejercicio?->año ?? '—' }}</p>
                            </div>
                            <a href="{{ route('economia.remesas.show', $r) }}" class="btn-soft text-xs shrink-0 h-9">Abrir</a>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-3 text-sm">
                            <span class="font-mono">{{ $r->numero_recibos }} rec.</span>
                            <span class="font-mono font-semibold">{{ number_format((float) $r->importe_total, 2, ',', '.') }} €</span>
                            <span class="text-xs uppercase text-slate-500">{{ $r->estado }}</span>
                        </div>
                    </article>
                @empty
                    <p class="p-6 text-center text-slate-500">No hay remesas.</p>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/80">
                {{ $remesas->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
