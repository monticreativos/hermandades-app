<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.solicitudes-cambio.index', ['estado' => \App\Models\SolicitudCambioDatos::ESTADO_PENDIENTE]) }}" class="btn-soft text-xs uppercase tracking-wider">Pendientes</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Solicitudes de cambio de datos</h1>
                    <p class="text-sm text-slate-600 mt-1">Portal del hermano — revisión de secretaría</p>
                </div>
                @if ($pendientesCount > 0)
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-900">{{ $pendientesCount }} pendiente(s)</span>
                @endif
            </div>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
            @endif

            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4">
                <form method="GET" action="{{ route('secretaria.solicitudes-cambio.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Estado</label>
                        <select name="estado" class="input-premium min-w-[12rem]" onchange="this.form.submit()">
                            <option value="{{ \App\Models\SolicitudCambioDatos::ESTADO_PENDIENTE }}" @selected($filtroEstado === \App\Models\SolicitudCambioDatos::ESTADO_PENDIENTE)>Pendientes</option>
                            <option value="{{ \App\Models\SolicitudCambioDatos::ESTADO_APROBADA }}" @selected($filtroEstado === \App\Models\SolicitudCambioDatos::ESTADO_APROBADA)>Aprobadas</option>
                            <option value="{{ \App\Models\SolicitudCambioDatos::ESTADO_RECHAZADA }}" @selected($filtroEstado === \App\Models\SolicitudCambioDatos::ESTADO_RECHAZADA)>Rechazadas</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="md:hidden space-y-3">
                @forelse ($solicitudes as $sol)
                    <article class="card-premium p-4 border-l-4 {{ $sol->estado === 'Pendiente' ? 'border-l-amber-500' : ($sol->estado === 'Aprobada' ? 'border-l-emerald-500' : 'border-l-rose-500') }}">
                        <p class="text-xs text-slate-500">#{{ $sol->id }} · {{ $sol->created_at->format('d/m/Y H:i') }}</p>
                        <p class="font-bold text-[color:var(--color-primary)] mt-1">{{ $sol->hermano->nombreCompleto() }}</p>
                        <p class="text-sm text-slate-600">N.º {{ $sol->hermano->numero_hermano }}</p>
                        <span class="inline-block mt-2 text-xs font-bold uppercase px-2 py-0.5 rounded-full bg-slate-100">{{ $sol->estado }}</span>
                        <a href="{{ route('secretaria.solicitudes-cambio.show', $sol) }}" class="btn-primary w-full justify-center mt-3 text-xs">Abrir</a>
                    </article>
                @empty
                    <p class="text-center text-slate-500 py-8">No hay solicitudes en este filtro.</p>
                @endforelse
            </div>

            <div class="hidden md:block card-premium overflow-hidden border-t-2 border-t-[color:var(--color-accent)]">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Hermano</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($solicitudes as $sol)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $sol->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-[color:var(--color-primary)]">{{ $sol->hermano->nombreCompleto() }}</span>
                                        <span class="text-slate-500">· n.º {{ $sol->hermano->numero_hermano }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs font-bold uppercase">{{ $sol->estado }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('secretaria.solicitudes-cambio.show', $sol) }}" class="btn-soft text-xs">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay solicitudes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-2">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
