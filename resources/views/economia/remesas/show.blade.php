@php
    use App\Models\RemesaRecibo;
    $pCob = $donut['cob'];
    $pDev = $donut['dev'];
    $pPend = $donut['pend'];
    $sumPie = $pCob + $pDev + $pPend;
    if ($sumPie > 0 && abs($sumPie - 100) > 0.2) {
        $pPend = max(0, 100 - $pCob - $pDev);
    }
@endphp
<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">{{ $remesa->etiqueta_periodo }}</h2>
                <p class="text-sm text-slate-600 mt-1">
                    Emitida el {{ $remesa->fecha_emision?->format('d/m/Y') }}
                    @if ($remesa->ejercicio)
                        · Ejercicio {{ $remesa->ejercicio->año }}
                    @endif
                    · {{ $remesa->numero_recibos }} recibos · Total {{ number_format((float) $remesa->importe_total, 2, ',', '.') }} €
                </p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('economia.remesas.descargar-xml', $remesa) }}" class="btn-accent uppercase tracking-wider text-xs">Descargar pain.008 (XML)</a>
                <a href="{{ route('economia.remesas.index') }}" class="btn-soft text-xs">Volver al listado</a>
            </div>
        </div>

        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 lg:col-span-1">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500 mb-4">Salud de la remesa</h3>
                @if ($recibos->isEmpty())
                    <p class="text-sm text-slate-500">Sin recibos.</p>
                @else
                    <div class="flex items-center gap-6">
                        <div
                            class="relative w-36 h-36 shrink-0 rounded-full shadow-inner border border-slate-200"
                            style="background: conic-gradient(#10b981 0% {{ $pCob }}%, #f97316 {{ $pCob }}% {{ $pCob + $pDev }}%, #94a3b8 {{ $pCob + $pDev }}% 100%);"
                            role="img"
                            aria-label="Distribución cobrados, devueltos y pendientes"
                        >
                            <div class="absolute inset-3 rounded-full bg-white flex items-center justify-center shadow-sm">
                                <div class="text-center">
                                    <p class="text-[10px] uppercase font-bold text-slate-500">Total</p>
                                    <p class="text-xl font-mono font-bold text-[color:var(--color-primary)]">{{ $recibos->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <ul class="text-sm space-y-2 flex-1 min-w-0">
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="text-slate-700">Cobrados: <strong class="font-mono">{{ $cob }}</strong> ({{ $pCob }}%)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full bg-orange-500 shrink-0"></span>
                                <span class="text-slate-700">Devueltos: <strong class="font-mono">{{ $dev }}</strong> ({{ $pDev }}%)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 rounded-full bg-slate-400 shrink-0"></span>
                                <span class="text-slate-700">Pendientes: <strong class="font-mono">{{ $pend }}</strong> ({{ $pPend }}%)</span>
                            </li>
                        </ul>
                    </div>
                    <p class="text-xs text-slate-500 mt-4">Tras importar la respuesta del banco, los cobros generan un asiento 572 / 430–431 de forma automática.</p>
                @endif
            </section>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 lg:col-span-2">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500 mb-3">Importar respuesta del banco</h3>
                <p class="text-sm text-slate-600 mb-4">Soporta XML camt.053 (extracción de <span class="font-mono text-xs">EndToEndId</span> con prefijo <span class="font-mono text-xs">E2E-H</span>) o CSV auxiliar: <span class="font-mono text-xs">end_to_end_id;importe;OK|DEVUELTO</span>.</p>
                <form method="POST" action="{{ route('economia.remesas.importar-respuesta', $remesa) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-end gap-3">
                    @csrf
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Archivo</label>
                        <input type="file" name="archivo_respuesta" accept=".xml,.csv,.txt" class="input-premium w-full text-sm" required>
                        @error('archivo_respuesta')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary uppercase tracking-wider text-xs shrink-0">Procesar conciliación</button>
                </form>
            </section>
        </div>

        @if ($devueltos->isNotEmpty())
            <section class="card-premium border-t-2 border-t-rose-300 p-6 mb-8">
                <h3 class="text-base font-bold text-[color:var(--color-primary)] mb-4">Recibos devueltos en esta remesa</h3>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-rose-50/80 text-left text-xs uppercase text-rose-900/80">
                            <tr>
                                <th class="px-3 py-2">Hermano</th>
                                <th class="px-3 py-2">Periodo</th>
                                <th class="px-3 py-2 text-right">Importe</th>
                                <th class="px-3 py-2">Motivo</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($devueltos as $dr)
                                @php $h = $dr->hermano; @endphp
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="font-mono font-semibold">n.º {{ $h?->numero_hermano }}</span>
                                        <span class="block text-slate-600">{{ $h?->nombreCompleto() }}</span>
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $dr->periodo_clave }}</td>
                                    <td class="px-3 py-2 text-right font-mono">{{ number_format((float) $dr->importe, 2, ',', '.') }} €</td>
                                    <td class="px-3 py-2 text-xs text-slate-600">{{ $dr->motivo_devolucion ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($h?->telefono)
                                                <a href="tel:{{ preg_replace('/\s+/', '', $h->telefono) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Llamar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('secretaria.avisos.create') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-[color:var(--color-accent)]/50 text-[color:var(--color-accent)] hover:bg-amber-50" title="Nuevo aviso (portal / email)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                            </a>
                                            <a href="{{ route('hermanos.show', $h) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Ficha">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden space-y-3">
                    @foreach ($devueltos as $dr)
                        @php $h = $dr->hermano; @endphp
                        <article class="rounded-xl border border-rose-100 bg-rose-50/40 p-4">
                            <p class="font-mono font-bold text-[color:var(--color-primary)]">n.º {{ $h?->numero_hermano }} · {{ $dr->periodo_clave }}</p>
                            <p class="text-sm text-slate-700 mt-1">{{ $h?->nombreCompleto() }}</p>
                            <p class="text-sm font-mono mt-2">{{ number_format((float) $dr->importe, 2, ',', '.') }} €</p>
                            <p class="text-xs text-slate-600 mt-2">{{ $dr->motivo_devolucion ?? '—' }}</p>
                            <div class="mt-3 flex gap-2">
                                @if ($h?->telefono)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $h->telefono) }}" class="btn-soft text-xs">Llamar</a>
                                @endif
                                <a href="{{ route('secretaria.avisos.create') }}" class="btn-soft text-xs border-[color:var(--color-accent)]/40">Aviso</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <p class="text-xs text-slate-500 mt-4">En la importación se genera un aviso en el portal del hermano. Use «Aviso» para un recordatorio adicional desde secretaría.</p>
            </section>
        @endif

        @if ($importaciones->isNotEmpty())
            <section class="card-premium p-6 mb-8">
                <h3 class="text-sm font-bold uppercase text-slate-500 mb-3">Últimas importaciones</h3>
                <ul class="text-sm divide-y divide-slate-100">
                    @foreach ($importaciones as $imp)
                        <li class="py-2 flex flex-wrap justify-between gap-2">
                            <span>{{ $imp->created_at?->format('d/m/Y H:i') }} · {{ $imp->nombre_original }} ({{ $imp->tipo_archivo }})</span>
                            <span class="font-mono text-xs text-slate-600">+{{ $imp->recibos_cobrados }} / −{{ $imp->recibos_devueltos }} / ?{{ $imp->recibos_no_encontrados }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="card-premium border-t-2 border-t-slate-200 p-6">
            <h3 class="text-base font-bold text-[color:var(--color-primary)] mb-4">Detalle de recibos</h3>
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-3 py-2">End-to-end</th>
                            <th class="px-3 py-2">Hermano</th>
                            <th class="px-3 py-2">Periodo</th>
                            <th class="px-3 py-2 text-right">Importe</th>
                            <th class="px-3 py-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recibos->sortBy('hermano.numero_hermano') as $rec)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-3 py-2 font-mono text-xs">{{ $rec->end_to_end_id }}</td>
                                <td class="px-3 py-2">
                                    <span class="font-mono">n.º {{ $rec->hermano?->numero_hermano }}</span>
                                    <span class="text-slate-600"> {{ $rec->hermano?->apellidos }}</span>
                                </td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $rec->periodo_clave }}</td>
                                <td class="px-3 py-2 text-right font-mono">{{ number_format((float) $rec->importe, 2, ',', '.') }} €</td>
                                <td class="px-3 py-2">
                                    @if ($rec->estado === RemesaRecibo::ESTADO_COBRADO)
                                        <span class="text-emerald-800 font-semibold text-xs">Cobrado</span>
                                    @elseif ($rec->estado === RemesaRecibo::ESTADO_DEVUELTO)
                                        <span class="text-orange-800 font-semibold text-xs">Devuelto</span>
                                    @else
                                        <span class="text-slate-600 text-xs">Pendiente banco</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="lg:hidden space-y-3">
                @foreach ($recibos->sortBy('hermano.numero_hermano') as $rec)
                    <article class="rounded-xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex justify-between gap-2">
                            <span class="font-mono text-xs break-all">{{ $rec->end_to_end_id }}</span>
                            <span class="font-mono font-semibold shrink-0">{{ number_format((float) $rec->importe, 2, ',', '.') }} €</span>
                        </div>
                        <p class="text-sm mt-2 text-[color:var(--color-primary)]">n.º {{ $rec->hermano?->numero_hermano }} {{ $rec->hermano?->nombreCompleto() }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $rec->periodo_clave }}</p>
                        <p class="text-xs font-semibold mt-2">{{ $rec->estado }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
