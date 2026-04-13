<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.firmas-conformidad.create') }}" class="btn-accent text-xs uppercase tracking-wider">Nueva solicitud</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Firmas de conformidad</h1>
            <p class="text-sm text-slate-600">El hermano acepta en el portal; queda registro con fecha e IP.</p>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
            @endif

            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Título</th>
                                <th class="px-4 py-3 text-left">Hermano</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($solicitudes as $s)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $s->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 font-medium text-[color:var(--color-primary)]">{{ $s->titulo }}</td>
                                    <td class="px-4 py-3 text-slate-600">N.º {{ $s->hermano?->numero_hermano }}</td>
                                    <td class="px-4 py-3">
                                        @if ($s->estado === \App\Models\FirmaConformidadSolicitud::ESTADO_FIRMADO)
                                            <span class="text-emerald-700 font-semibold text-xs">Firmado</span>
                                        @else
                                            <span class="text-amber-700 font-semibold text-xs">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('secretaria.firmas-conformidad.show', $s) }}" class="btn-soft text-xs">Detalle</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">Sin solicitudes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse ($solicitudes as $s)
                        <a href="{{ route('secretaria.firmas-conformidad.show', $s) }}" class="block p-4 hover:bg-slate-50/80">
                            <p class="font-semibold text-[color:var(--color-primary)]">{{ $s->titulo }}</p>
                            <p class="text-xs text-slate-500 mt-1">N.º {{ $s->hermano?->numero_hermano }} · {{ $s->estado }}</p>
                        </a>
                    @empty
                        <p class="p-6 text-center text-slate-500">Sin solicitudes.</p>
                    @endforelse
                </div>
            </div>
            <div class="px-2">{{ $solicitudes->links() }}</div>
        </div>
    </div>
</x-app-layout>
