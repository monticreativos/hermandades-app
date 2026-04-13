@php
    $palos = \App\Models\CostaleroPerfil::palos();
@endphp
<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('cuadrillas.index', ['año' => $cuadrilla->año]) }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Cuadrillas</a>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Igualá — {{ $cuadrilla->nombre }}</h1>
        <p class="text-sm text-slate-600">Ordenados por altura para asignación rápida de trabajaderas.</p>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        <div class="grid lg:grid-cols-3 gap-4">
            <form method="post" action="{{ route('cuadrillas.asignar', $cuadrilla) }}" class="card-premium p-5 space-y-3 border-t-2 border-t-[color:var(--color-accent)]">
                @csrf
                <h2 class="font-bold text-[color:var(--color-primary)]">Alta / edición de costalero</h2>
                <select name="hermano_id" class="input-premium w-full" required>
                    <option value="">Seleccionar hermano</option>
                    @foreach($hermanosSinPerfil as $h)
                        <option value="{{ $h->id }}">N.º {{ $h->numero_hermano }} — {{ $h->nombreCompleto() }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input name="altura_cm" type="number" class="input-premium" placeholder="Altura cm" />
                    <input name="calzado_talla" type="number" class="input-premium" placeholder="Calzado" />
                    <input name="ropa_talla" class="input-premium" placeholder="Ropa" />
                    <input name="anios_cuadrilla" type="number" class="input-premium" placeholder="Años cuadrilla" />
                    <input name="trabajadera_numero" type="number" min="1" class="input-premium" placeholder="Trabajadera" />
                    <select name="palo" class="input-premium">
                        <option value="">Palo</option>
                        @foreach($palos as $k => $txt)<option value="{{ $k }}">{{ $txt }}</option>@endforeach
                    </select>
                </div>
                <textarea name="alergias" class="input-premium w-full" rows="2" placeholder="Alergias"></textarea>
                <textarea name="lesiones" class="input-premium w-full" rows="2" placeholder="Lesiones"></textarea>
                <button class="btn-accent text-xs uppercase tracking-wider px-4 py-2">Guardar</button>
            </form>

            <div class="lg:col-span-2 card-premium p-5 border border-slate-100">
                <h2 class="font-bold text-[color:var(--color-primary)] mb-3">Esquema de trabajaderas (clic para ver)</h2>
                <div class="space-y-2">
                    @for($t = 1; $t <= $cuadrilla->numero_trabajaderas; $t++)
                        @php $fila = $perfiles->where('trabajadera_numero', $t); @endphp
                        <details class="rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                            <summary class="cursor-pointer font-semibold text-[color:var(--color-primary)]">Trabajadera {{ $t }} ({{ $fila->count() }} hombres)</summary>
                            <div class="grid sm:grid-cols-2 gap-2 mt-3">
                                @forelse($fila as $p)
                                    <div class="rounded-lg border border-slate-200 bg-white p-2 text-sm">
                                        <p class="font-semibold">{{ $p->hermano?->nombreCompleto() }}</p>
                                        <p class="text-xs text-slate-500">{{ $p->altura_cm ? $p->altura_cm.' cm' : 'Altura pendiente' }} · {{ $palos[$p->palo] ?? 'Palo pendiente' }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-500">Sin asignaciones en esta trabajadera.</p>
                                @endforelse
                            </div>
                        </details>
                    @endfor
                </div>
            </div>
        </div>

        <div class="card-premium p-5 border border-slate-100">
            <h2 class="font-bold text-[color:var(--color-primary)] mb-3">Listado por altura</h2>
            <div class="overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2 text-left">Hermano</th><th class="px-3 py-2 text-right">Altura</th><th class="px-3 py-2">Trabajadera</th><th class="px-3 py-2">Palo</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($perfiles as $p)
                        <tr>
                            <td class="px-3 py-2">{{ $p->hermano?->nombreCompleto() }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $p->altura_cm ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $p->trabajadera_numero ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $palos[$p->palo] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
