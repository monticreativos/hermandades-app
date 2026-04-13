<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Recibos devueltos (todas las remesas)</h2>
                <p class="text-sm text-slate-600 mt-1">Seguimiento de domiciliaciones rechazadas por el banco. Los hermanos reciben aviso en el portal al procesar la respuesta.</p>
            </div>
            <a href="{{ route('economia.remesas.index') }}" class="btn-soft text-xs shrink-0">Volver a remesas</a>
        </div>

        @include('economia.partials.subnav')

        <div class="card-premium border-t-2 border-t-rose-300 overflow-hidden">
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-rose-50/80 text-left text-xs uppercase text-rose-900/80">
                        <tr>
                            <th class="px-4 py-3">Fecha estado</th>
                            <th class="px-4 py-3">Remesa</th>
                            <th class="px-4 py-3">Hermano</th>
                            <th class="px-4 py-3">Periodo</th>
                            <th class="px-4 py-3 text-right">Importe</th>
                            <th class="px-4 py-3">Motivo</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recibos as $dr)
                            @php $h = $dr->hermano; $rm = $dr->remesa; @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $dr->fecha_estado?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('economia.remesas.show', $rm) }}" class="font-semibold text-[color:var(--color-accent)] hover:underline">{{ $rm?->etiqueta_periodo }}</a>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono font-semibold">n.º {{ $h?->numero_hermano }}</span>
                                    <span class="block text-slate-600">{{ $h?->nombreCompleto() }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $dr->periodo_clave }}</td>
                                <td class="px-4 py-3 text-right font-mono">{{ number_format((float) $dr->importe, 2, ',', '.') }} €</td>
                                <td class="px-4 py-3 text-xs text-slate-600 max-w-xs">{{ \Illuminate\Support\Str::limit($dr->motivo_devolucion ?? '—', 80) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        @if ($h?->telefono)
                                            <a href="tel:{{ preg_replace('/\s+/', '', $h->telefono) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Llamar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('hermanos.show', $h) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Ficha">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-500">No hay devoluciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($recibos as $dr)
                    @php $h = $dr->hermano; $rm = $dr->remesa; @endphp
                    <article class="p-4">
                        <p class="text-xs text-slate-500">{{ $dr->fecha_estado?->format('d/m/Y H:i') }}</p>
                        <a href="{{ route('economia.remesas.show', $rm) }}" class="font-semibold text-[color:var(--color-accent)] text-sm">{{ $rm?->etiqueta_periodo }}</a>
                        <p class="mt-2 font-mono font-bold">n.º {{ $h?->numero_hermano }}</p>
                        <p class="text-sm">{{ $h?->nombreCompleto() }}</p>
                        <p class="text-xs font-mono mt-1">{{ $dr->periodo_clave }} · {{ number_format((float) $dr->importe, 2, ',', '.') }} €</p>
                        @if ($h?->telefono)
                            <a href="tel:{{ preg_replace('/\s+/', '', $h->telefono) }}" class="btn-soft text-xs mt-3 inline-block">Llamar</a>
                        @endif
                    </article>
                @empty
                    <p class="p-6 text-center text-slate-500">Sin devoluciones.</p>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/80">
                {{ $recibos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
