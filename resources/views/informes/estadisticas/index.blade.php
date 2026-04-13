<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-[color:var(--color-primary)] leading-tight">
            Estadísticas y análisis
        </h2>
    </x-slot>

    @push('scripts')
        @vite(['resources/js/informes-estadisticas.js'])
    @endpush

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6">
            @include('informes.partials.subnav')

            <form method="GET" action="{{ route('informes.estadisticas.index') }}" class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4 sm:p-6">
                <p class="text-xs font-semibold uppercase text-slate-500 mb-3">Parámetros del informe</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="text-xs font-semibold text-slate-700">Fecha de referencia</label>
                        <input type="date" name="fecha_ref" value="{{ $fechaRef->format('Y-m-d') }}" class="input-premium w-full mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700">Antigüedad mín. voto (años)</label>
                        <input type="number" name="antiguedad_voto" min="0" max="80" value="{{ $antiguedadVoto }}" class="input-premium w-full mt-1">
                        <p class="text-xs text-slate-500 mt-1">Por defecto hermandad: {{ $defAntiguedadHermandad }} (ajustable en Ajustes).</p>
                    </div>
                    <div class="sm:col-span-2 flex flex-wrap gap-2">
                        <button type="submit" class="btn-accent">Actualizar</button>
                        <a href="{{ route('informes.estadisticas.index') }}" class="btn-soft">Restablecer</a>
                    </div>
                </div>
            </form>

            @php($k = $resumen['kpis'])
            <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <article class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                    <p class="text-xs font-semibold uppercase text-slate-500">Hermanos en alta</p>
                    <p class="text-3xl font-bold text-[color:var(--color-primary)] mt-2">{{ number_format($k['total_alta'], 0, ',', '.') }}</p>
                </article>
                <article class="card-premium p-6 border-t-2 border-t-emerald-600/40">
                    <p class="text-xs font-semibold uppercase text-slate-500">Con derecho a voto (aprox.)</p>
                    <p class="text-3xl font-bold text-emerald-800 mt-2">{{ $k['pct_voto'] }}%</p>
                    <p class="text-xs text-slate-500 mt-1">Alta, mayoría de edad a la fecha y antigüedad mínima.</p>
                </article>
                <article class="card-premium p-6 border-t-2 border-t-orange-400/60 sm:col-span-2 xl:col-span-1">
                    <p class="text-xs font-semibold uppercase text-slate-500">Morosidad (cuotas + lotería)</p>
                    <p class="text-3xl font-bold text-orange-900 mt-2">{{ $k['pct_morosidad'] }}%</p>
                    <p class="text-xs text-slate-500 mt-1">% de hermanos en alta con cuota ordinaria pendiente (tras emisión masiva en Economía) o participaciones de lotería/rifa sin cobrar.</p>
                </article>
            </section>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4 sm:p-6">
                    <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-4">Pirámide de población (en alta)</h3>
                    <div class="h-64 sm:h-72 mb-4">
                        <canvas id="chart-piramide" aria-label="Gráfico pirámide de edades"></canvas>
                    </div>
                    <ul class="space-y-2">
                        @foreach ($resumen['piramide'] as $fila)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="w-40 shrink-0 text-slate-600">{{ $fila['etiqueta'] }}</span>
                                <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                                    <div class="h-full rounded-full bg-[color:var(--color-primary)]" style="width: {{ min(100, $fila['pct']) }}%"></div>
                                </div>
                                <span class="w-16 text-right font-semibold text-[color:var(--color-primary)]">{{ $fila['total'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4 sm:p-6">
                    <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-4">Altas y bajas (últimos 3 años)</h3>
                    <div class="h-64 sm:h-72 mb-4">
                        <canvas id="chart-flujo" aria-label="Gráfico altas y bajas"></canvas>
                    </div>
                    <ul class="text-sm text-slate-600 space-y-1">
                        @foreach ($resumen['altas_bajas'] as $row)
                            <li><span class="font-semibold text-slate-800">{{ $row['año'] }}</span>: {{ $row['altas'] }} altas, {{ $row['bajas'] }} bajas — neto {{ $row['neto'] >= 0 ? '+' : '' }}{{ $row['neto'] }}</li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4 sm:p-6">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-4">Mapa de calor por código postal (top 5)</h3>
                @if (empty($resumen['top_cp']))
                    <p class="text-sm text-slate-500">No hay códigos postales registrados para hermanos en alta.</p>
                @else
                    @php($maxCp = max(array_column($resumen['top_cp'], 'total')) ?: 1)
                    <ul class="space-y-3">
                        @foreach ($resumen['top_cp'] as $row)
                            @php($w = round(100 * $row['total'] / $maxCp, 1))
                            <li>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-mono font-semibold text-[color:var(--color-primary)]">{{ $row['cp'] }}</span>
                                    <span class="text-slate-600">{{ $row['total'] }} hermano(s)</span>
                                </div>
                                <div class="h-3 rounded-lg bg-slate-100 overflow-hidden border border-slate-200">
                                    <div class="h-full rounded-lg bg-gradient-to-r from-[color:var(--color-accent)] to-[color:var(--color-primary)]" style="width: {{ $w }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div
                id="estadisticas-charts-data"
                class="hidden"
                data-piramide="@json($resumen['piramide'])"
                data-flujo="@json($resumen['altas_bajas'])"
            ></div>
        </div>
    </div>
</x-app-layout>
