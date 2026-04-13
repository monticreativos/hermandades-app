<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('economia.loterias.index') }}" class="btn-soft text-xs">Volver a loterías</a>
    </x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <h2 class="text-xl font-bold text-[color:var(--color-primary)]">{{ $loteria->sorteo }}</h2>
            <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-700">
                <span><strong class="text-slate-500">Número:</strong> <span class="font-mono">{{ $loteria->numero }}</span></span>
                @if ($loteria->serie_fraccion)
                    <span><strong class="text-slate-500">Serie/fracción:</strong> {{ $loteria->serie_fraccion }}</span>
                @endif
                <span><strong class="text-slate-500">Precio participación:</strong> {{ number_format((float) $loteria->precio_participacion, 2, ',', '.') }} €</span>
                <span><strong class="text-slate-500">Donativo total:</strong> {{ number_format((float) $loteria->donativo, 2, ',', '.') }} €</span>
            </div>
            <div class="mt-3 flex flex-wrap gap-4 text-sm">
                <span class="font-mono">Participaciones: {{ $loteria->participacionesAsignadas() }} / {{ $loteria->total_participaciones }}</span>
                <span class="text-emerald-800 font-semibold">Libres: {{ $disponibles }}</span>
            </div>
            @if ($loteria->observaciones)
                <p class="mt-3 text-sm text-slate-600">{{ $loteria->observaciones }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 xl:col-span-1">
                <h3 class="text-base font-bold text-[color:var(--color-primary)] mb-4">Asignar taco</h3>
                <form method="POST" action="{{ route('economia.loterias.asignaciones.store', $loteria) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Hermano</label>
                        <select name="hermano_id" required class="input-premium w-full">
                            <option value="">— Elegir —</option>
                            @foreach ($hermanos as $h)
                                <option value="{{ $h->id }}" @selected((string) old('hermano_id') === (string) $h->id)>
                                    {{ $h->numero_hermano }} — {{ $h->apellidos }}, {{ $h->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Participaciones (taco)</label>
                        <input type="number" name="participaciones" value="{{ old('participaciones', 1) }}" min="1" required class="input-premium w-full font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Referencia taco</label>
                        <input type="text" name="referencia_taco" value="{{ old('referencia_taco') }}" maxlength="120" class="input-premium w-full" placeholder="Ej. Taco 3 — fracción 4/10">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Importe a cobrar (€) <span class="font-normal text-slate-400">opcional</span></label>
                        <input type="number" name="importe_a_cobrar" value="{{ old('importe_a_cobrar') }}" step="0.01" min="0" class="input-premium w-full font-mono" placeholder="Auto: precio × part. + parte donativo">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Notas</label>
                        <input type="text" name="notas" value="{{ old('notas') }}" maxlength="500" class="input-premium w-full">
                    </div>
                    <button type="submit" class="btn-accent w-full text-xs uppercase tracking-wider @if($disponibles <= 0) opacity-50 cursor-not-allowed @endif" @if($disponibles <= 0) disabled @endif>Asignar</button>
                </form>
            </div>

            <div class="xl:col-span-2 card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-wrap justify-between gap-2 items-center">
                    <h3 class="text-base font-bold text-[color:var(--color-primary)]">Control de cobro</h3>
                    <span class="text-xs text-slate-500">Pendientes primero en escritorio</span>
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Hermano</th>
                                <th class="px-4 py-3">Taco</th>
                                <th class="px-4 py-3 text-right">Part.</th>
                                <th class="px-4 py-3 text-right">Importe</th>
                                <th class="px-4 py-3">Cobro</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($asignaciones as $a)
                                <tr class="border-b border-slate-100 {{ $a->cobrado ? 'bg-slate-50/50' : 'bg-amber-50/40' }}">
                                    <td class="px-4 py-2">
                                        <span class="font-mono text-xs">{{ $a->hermano->numero_hermano }}</span>
                                        {{ $a->hermano->apellidos }}, {{ $a->hermano->nombre }}
                                    </td>
                                    <td class="px-4 py-2 text-xs text-slate-600">{{ $a->referencia_taco ?: '—' }}</td>
                                    <td class="px-4 py-2 text-right font-mono">{{ $a->participaciones }}</td>
                                    <td class="px-4 py-2 text-right font-mono tabular-nums">{{ number_format((float) $a->importe_a_cobrar, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2">
                                        @if ($a->cobrado)
                                            <span class="text-emerald-800 text-xs font-semibold">Cobrado</span>
                                            <span class="block text-xs text-slate-500">{{ optional($a->fecha_cobro)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-amber-800 text-xs font-semibold">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <form method="POST" action="{{ route('economia.loterias.asignaciones.toggle-cobro', $a) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="btn-soft text-xs">
                                                {{ $a->cobrado ? 'Marcar pendiente' : 'Marcar cobrado' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">Sin asignaciones todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse ($asignaciones as $a)
                        <div class="p-4 {{ $a->cobrado ? '' : 'bg-amber-50/50' }}">
                            <div class="font-semibold text-sm">{{ $a->hermano->apellidos }}, {{ $a->hermano->nombre }}</div>
                            <div class="text-xs font-mono text-slate-500">n.º {{ $a->hermano->numero_hermano }}</div>
                            <div class="mt-2 text-sm font-mono">{{ number_format((float) $a->importe_a_cobrar, 2, ',', '.') }} € · {{ $a->participaciones }} part.</div>
                            <form method="POST" action="{{ route('economia.loterias.asignaciones.toggle-cobro', $a) }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn-soft text-xs w-full">{{ $a->cobrado ? 'Marcar pendiente' : 'Marcar cobrado' }}</button>
                            </form>
                        </div>
                    @empty
                        <p class="p-4 text-sm text-slate-500 text-center">Sin asignaciones.</p>
                    @endforelse
                </div>
                <div class="p-4">{{ $asignaciones->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
