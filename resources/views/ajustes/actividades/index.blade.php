<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('ajustes.index') }}" class="btn-soft text-xs">Volver a Ajustes</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Registro de actividad</h2>
                <p class="text-sm text-slate-600 mt-1">Auditoría de acciones relevantes (quién, qué y cuándo)</p>
            </div>

            <div class="hidden md:block overflow-x-auto card-premium border-t-2 border-t-[color:var(--color-accent)]">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3 text-left">Fecha</th>
                            <th class="px-4 py-3 text-left">Usuario</th>
                            <th class="px-4 py-3 text-left">Acción</th>
                            <th class="px-4 py-3 text-left">Descripción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($actividades as $act)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $act->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $act->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $act->accion }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $act->descripcion }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Aún no hay registros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden space-y-3">
                @forelse ($actividades as $act)
                    <article class="card-premium p-4 text-sm">
                        <p class="text-xs text-slate-500">{{ $act->created_at->format('d/m/Y H:i') }}</p>
                        <p class="font-semibold text-[color:var(--color-primary)] mt-1">{{ $act->user?->name ?? 'Sistema' }}</p>
                        <p class="font-mono text-[10px] text-slate-500 mt-1">{{ $act->accion }}</p>
                        <p class="text-slate-700 mt-2">{{ $act->descripcion }}</p>
                    </article>
                @empty
                    <p class="text-center text-slate-500 text-sm py-8">Aún no hay registros.</p>
                @endforelse
            </div>

            <div class="pb-4">
                {{ $actividades->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
