<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.avisos.create') }}" class="btn-accent text-xs uppercase tracking-wider">Nuevo aviso</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            <h1 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Avisos a hermanos</h1>
            <p class="text-sm text-slate-600">Comunicaciones masivas, individuales o a una selección. Aparecen en el portal del hermano.</p>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
            @endif

            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Título</th>
                                <th class="px-4 py-3 text-left">Alcance</th>
                                <th class="px-4 py-3 text-right">Destinatarios</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($avisos as $aviso)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $aviso->enviado_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="px-4 py-3 font-semibold text-[color:var(--color-primary)]">{{ $aviso->titulo }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $aviso->alcance }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $aviso->destinatarios_count }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('secretaria.avisos.show', $aviso) }}" class="btn-soft text-xs">Detalle</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-slate-500">Aún no hay avisos enviados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="px-2">{{ $avisos->links() }}</div>
        </div>
    </div>
</x-app-layout>
