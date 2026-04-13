<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.comunicados-masivos.index') }}" class="btn-soft text-xs uppercase tracking-wider">Historial</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">{{ $comunicado->asunto }}</h1>
                    <p class="text-sm text-slate-600 mt-1">{{ $comunicado->created_at->format('d/m/Y H:i') }} · Filtro: {{ str_replace('_', ' ', $comunicado->filtro_envio) }} · Por {{ $comunicado->creadoPor?->name ?? '—' }}</p>
                </div>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide bg-slate-100 text-slate-800">{{ $comunicado->estado }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <article class="card-premium p-4">
                    <p class="text-xs text-slate-500 font-semibold uppercase">Destinatarios</p>
                    <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ $comunicado->total_destinatarios }}</p>
                </article>
                <article class="card-premium p-4">
                    <p class="text-xs text-slate-500 font-semibold uppercase">Correos enviados</p>
                    <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ $comunicado->correos_enviados }}</p>
                </article>
                <article class="card-premium p-4">
                    <p class="text-xs text-slate-500 font-semibold uppercase">Aperturas (≥1)</p>
                    <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ $stats['abiertos'] }}</p>
                </article>
                <article class="card-premium p-4">
                    <p class="text-xs text-slate-500 font-semibold uppercase">Pendientes cola</p>
                    <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ $stats['pendientes_correo'] }}</p>
                </article>
            </div>

            <section class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500 mb-3">Vista previa del cuerpo</h2>
                <div class="prose prose-sm max-w-none border border-slate-100 rounded-xl p-4 bg-slate-50/50">
                    {!! $comunicado->cuerpo_html !!}
                </div>
            </section>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Destinatarios y seguimiento</h2>
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-2 text-left">Destinatario</th>
                                <th class="px-4 py-2 text-left">Correo enviado</th>
                                <th class="px-4 py-2 text-left">Abierto</th>
                                <th class="px-4 py-2 text-right">Veces</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($destinatarios as $d)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-2">
                                        @if ($d->hermano)
                                            <a href="{{ route('hermanos.show', $d->hermano) }}" class="font-medium text-[color:var(--color-primary)] hover:underline">N.º {{ $d->hermano?->numero_hermano }}</a>
                                            <span class="text-slate-600">{{ $d->hermano?->apellidos }}, {{ $d->hermano?->nombre }}</span>
                                        @else
                                            <span class="font-medium text-[color:var(--color-primary)]">{{ $d->nombre_destinatario ?: ($d->contactoExterno?->nombre ?? 'Contacto externo') }}</span>
                                            <span class="text-slate-600"> · {{ $d->email_destinatario ?: ($d->contactoExterno?->email ?? '—') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-slate-600">{{ $d->correo_enviado_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="px-4 py-2 text-slate-600">{{ $d->abierto_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $d->aperturas_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach ($destinatarios as $d)
                        <div class="p-4">
                            @if ($d->hermano)
                                <a href="{{ route('hermanos.show', $d->hermano) }}" class="font-semibold text-[color:var(--color-primary)]">N.º {{ $d->hermano?->numero_hermano }}</a>
                            @else
                                <p class="font-semibold text-[color:var(--color-primary)]">{{ $d->nombre_destinatario ?: ($d->contactoExterno?->nombre ?? 'Contacto externo') }}</p>
                            @endif
                            <p class="text-xs text-slate-500 mt-1">Enviado: {{ $d->correo_enviado_en?->format('d/m H:i') ?? '—' }} · Abierto: {{ $d->abierto_en?->format('d/m H:i') ?? '—' }} ({{ $d->aperturas_count }}×)</p>
                        </div>
                    @endforeach
                </div>
                <div class="px-4 py-3">{{ $destinatarios->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
