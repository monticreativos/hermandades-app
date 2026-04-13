<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.comunicados-masivos.create') }}" class="btn-accent text-xs uppercase tracking-wider">Nuevo comunicado</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            <h1 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Comunicados por email</h1>
            <p class="text-sm text-slate-600">Redactor enriquecido, filtros de audiencia y envío masivo en cola (sin bloquear el servidor).</p>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
            @endif

            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Asunto</th>
                                <th class="px-4 py-3 text-left">Filtro</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 text-right">Dest.</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($comunicados as $c)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 font-semibold text-[color:var(--color-primary)]">{{ $c->asunto }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ str_replace('_', ' ', $c->filtro_envio) }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $c->estado }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $c->destinatarios_count }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('secretaria.comunicados-masivos.show', $c) }}" class="btn-soft text-xs">Detalle</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-slate-500">Sin comunicados masivos todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse ($comunicados as $c)
                        <a href="{{ route('secretaria.comunicados-masivos.show', $c) }}" class="block p-4 hover:bg-slate-50/80">
                            <p class="font-semibold text-[color:var(--color-primary)]">{{ $c->asunto }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $c->created_at->format('d/m/Y H:i') }} · {{ $c->estado }} · {{ $c->destinatarios_count }} dest.</p>
                        </a>
                    @empty
                        <p class="p-6 text-center text-slate-500">Sin comunicados.</p>
                    @endforelse
                </div>
            </div>
            <div class="px-2">{{ $comunicados->links() }}</div>
        </div>
    </div>
</x-app-layout>
