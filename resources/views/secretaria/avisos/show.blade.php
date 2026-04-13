<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.avisos.index') }}" class="btn-soft text-xs uppercase tracking-wider">Volver</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-4xl space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">{{ $aviso->titulo }}</h1>
            <p class="text-sm text-slate-600">Enviado {{ $aviso->enviado_en?->format('d/m/Y H:i') }} · Alcance: {{ $aviso->alcance }} · Por {{ $aviso->creadoPor?->name ?? '—' }}</p>

            <div class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                <h2 class="text-xs font-bold uppercase text-slate-500 mb-2">Contenido</h2>
                <div class="prose prose-sm max-w-none text-slate-800 whitespace-pre-wrap">{{ $aviso->cuerpo }}</div>
            </div>

            <div class="card-premium p-6">
                <h2 class="text-sm font-bold text-[color:var(--color-primary)] mb-4">Destinatarios ({{ $destinatarios->total() }})</h2>
                <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-96 overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-slate-50">
                            <tr class="text-xs font-bold uppercase text-slate-500 border-b border-slate-200">
                                <th class="px-3 py-2 text-left">N.º</th>
                                <th class="px-3 py-2 text-left">Nombre</th>
                                <th class="px-3 py-2 text-left">Leído</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($destinatarios as $d)
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2">{{ $d->hermano->numero_hermano }}</td>
                                    <td class="px-3 py-2">{{ $d->hermano->nombreCompleto() }}</td>
                                    <td class="px-3 py-2">{{ $d->leido_en ? $d->leido_en->format('d/m/Y H:i') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $destinatarios->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
