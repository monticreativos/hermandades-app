<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-[color:var(--color-primary)]">Historial de informes (Economía)</h2>
    </x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">{{ session('error') }}</div>
        @endif

        <p class="text-sm text-slate-600 mb-6">Documentos generados y archivados (p. ej. arqueos mensuales de tesorería) para revisión del Fiscal o Censor.</p>

        <div class="hidden md:block card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Título</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $it)
                        <tr class="border-b border-slate-100">
                            <td class="px-4 py-2 whitespace-nowrap">{{ $it->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $it->titulo }}</td>
                            <td class="px-4 py-2 font-mono text-xs">{{ $it->tipo }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $it->usuario?->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('economia.informes.historial.descargar', $it) }}" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline">Descargar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">Aún no hay informes archivados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            @forelse ($items as $it)
                <div class="card-premium p-4 border border-slate-200 border-t-2 border-t-[color:var(--color-accent)]">
                    <p class="text-xs text-slate-500">{{ $it->created_at->format('d/m/Y H:i') }}</p>
                    <p class="font-semibold text-[color:var(--color-primary)] mt-1">{{ $it->titulo }}</p>
                    <a href="{{ route('economia.informes.historial.descargar', $it) }}" class="btn-accent text-xs mt-3 inline-block">Descargar PDF</a>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-8">Sin registros.</p>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>
