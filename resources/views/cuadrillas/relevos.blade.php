<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('cuadrillas.index', ['año' => $cuadrilla->año]) }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Cuadrillas</a>
    </x-slot>
    <div class="py-8 max-w-6xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Cuadrantes de Relevo — {{ $cuadrilla->nombre }}</h1>
        @if (session('status'))<div class="rounded-xl bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('status') }}</div>@endif

        <form method="post" action="{{ route('cuadrillas.relevos.store', $cuadrilla) }}" class="card-premium p-5 border-t-2 border-t-[color:var(--color-accent)] grid sm:grid-cols-4 gap-2">
            @csrf
            <input type="text" name="titulo" class="input-premium sm:col-span-2" placeholder="Título cuadrante" required>
            <input type="date" name="fecha_salida" class="input-premium" required>
            <button class="btn-accent text-xs uppercase tracking-wider">Crear cuadrante</button>
        </form>

        @foreach($relevos as $relevo)
            <div class="card-premium p-5 border border-slate-100 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-[color:var(--color-primary)]">{{ $relevo->titulo }} · {{ $relevo->fecha_salida?->format('d/m/Y') }}</h2>
                    <a href="{{ route('cuadrillas.relevos.pdf', [$cuadrilla, $relevo]) }}" target="_blank" class="btn-soft border border-slate-200 text-xs px-3 py-1.5 rounded-xl">PDF</a>
                </div>
                <form method="post" action="{{ route('cuadrillas.relevos.detalle.store', [$cuadrilla, $relevo]) }}" class="grid md:grid-cols-6 gap-2">
                    @csrf
                    <input name="punto" class="input-premium md:col-span-2" placeholder="Punto de relevo" required>
                    <input type="time" name="hora_desde" class="input-premium">
                    <input type="time" name="hora_hasta" class="input-premium">
                    <select name="hermano_id" class="input-premium">
                        <option value="">Costalero</option>
                        @foreach($costaleros as $cst)
                            <option value="{{ $cst->hermano_id }}">{{ $cst->hermano?->nombreCompleto() }}</option>
                        @endforeach
                    </select>
                    <button class="btn-soft border border-slate-200 text-xs rounded-xl">Añadir</button>
                </form>
                <div class="overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2 text-left">Punto</th><th class="px-3 py-2">Hora</th><th class="px-3 py-2">Costalero</th><th class="px-3 py-2">Turno</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach($relevo->detalles as $d)
                            <tr>
                                <td class="px-3 py-2">{{ $d->punto }}</td>
                                <td class="px-3 py-2">{{ $d->hora_desde }} {{ $d->hora_hasta ? ' - '.$d->hora_hasta : '' }}</td>
                                <td class="px-3 py-2">{{ $d->hermano?->nombreCompleto() ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $d->turno ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
