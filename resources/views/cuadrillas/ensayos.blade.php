<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('cuadrillas.index', ['año' => $cuadrilla->año]) }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Cuadrillas</a>
    </x-slot>
    <div class="py-8 max-w-6xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Ensayos — {{ $cuadrilla->nombre }}</h1>
        @if (session('status'))<div class="rounded-xl bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('status') }}</div>@endif

        <form method="post" action="{{ route('cuadrillas.ensayos.store', $cuadrilla) }}" class="card-premium p-5 border-t-2 border-t-[color:var(--color-accent)] grid sm:grid-cols-4 gap-2">
            @csrf
            <input type="date" name="fecha" class="input-premium" required>
            <input type="time" name="hora_inicio" class="input-premium">
            <input type="text" name="lugar" class="input-premium" placeholder="Lugar">
            <button class="btn-accent text-xs uppercase tracking-wider">Crear ensayo</button>
        </form>

        @foreach($ensayos as $ensayo)
            <form method="post" action="{{ route('cuadrillas.ensayos.asistencia', [$cuadrilla, $ensayo]) }}" class="card-premium p-5 border border-slate-100 space-y-3">
                @csrf
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-[color:var(--color-primary)]">{{ $ensayo->fecha?->format('d/m/Y') }} {{ $ensayo->hora_inicio ? '· '.$ensayo->hora_inicio : '' }}</h2>
                    <span class="text-xs text-slate-500">Ausencias: {{ $ensayo->ausencias_count }}</span>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($ensayo->asistencias as $a)
                        @php $faltas = (int) ($faltasPorHermano[$a->hermano_id] ?? 0); @endphp
                        <label class="flex items-center justify-between rounded-lg border px-3 py-2 {{ $faltas >= 2 ? 'border-rose-300 bg-rose-50' : 'border-slate-200 bg-white' }}">
                            <span class="text-sm">{{ $a->hermano?->nombreCompleto() }}</span>
                            <span class="flex items-center gap-2">
                                @if($faltas >= 2)<span class="text-[10px] font-bold text-rose-700">ALERTA {{ $faltas }} faltas</span>@endif
                                <input type="checkbox" name="asistencias[{{ $a->hermano_id }}]" value="1" @checked($a->asistio) class="rounded border-slate-300">
                            </span>
                        </label>
                    @endforeach
                </div>
                <button class="btn-soft border border-slate-200 px-4 py-2 rounded-xl text-xs">Guardar asistencia</button>
            </form>
        @endforeach
    </div>
</x-app-layout>
