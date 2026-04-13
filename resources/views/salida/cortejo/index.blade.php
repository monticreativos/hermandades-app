<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6"
        x-data="cortejoPage(@js([
            'toggleUrl' => url('salida/papeletas'),
            'csrfToken' => csrf_token(),
        ]))"
    >
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Organización del Cortejo</h2>
            <p class="text-sm text-slate-600 mt-1">Hermanos ordenados por antigüedad (N.º hermano) dentro de cada tramo</p>
        </div>

        @include('salida.partials.subnav')

        {{-- Resumen --}}
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="card-premium px-5 py-3 border-t-2 border-t-[color:var(--color-accent)]">
                <span class="text-sm text-slate-600">Cofrades en cortejo:</span>
                <span class="text-xl font-bold text-[color:var(--color-primary)] ml-2">{{ $totalPapeletas }}</span>
            </div>
            <form method="GET" action="{{ route('salida.cortejo.index') }}" class="flex items-end gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Ejercicio</label>
                    <select name="ejercicio_id" class="input-premium" onchange="this.form.submit()">
                        @foreach ($ejercicios as $ej)
                            <option value="{{ $ej->id }}" @selected($ej->id == $ejercicioId)>{{ $ej->año }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            @if ($ejercicioId && $totalPapeletas > 0)
                <a href="{{ route('salida.cortejo.pdf', $ejercicioId) }}" target="_blank" rel="noopener" class="btn-soft text-xs shrink-0">PDF listado (celadores)</a>
            @endif
        </div>

        {{-- Tramos --}}
        @forelse ($tramosAgrupados as $tramo => $papeletasTramo)
            @php
                $tramoLabel = $tramo ?: 'Sin tramo asignado';
            @endphp
            <div class="card-premium overflow-hidden mb-5">
                <div class="w-full flex items-center justify-between px-6 py-4 bg-gradient-to-r from-[color:var(--color-primary)] to-slate-700 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/20 text-lg font-black">{{ $tramo ?: '—' }}</span>
                        <div>
                            <span class="font-bold text-base">Tramo {{ $tramoLabel }}</span>
                            <span class="block text-xs text-white/70">{{ $papeletasTramo->count() }} cofrades</span>
                        </div>
                    </div>
                </div>

                {{-- Escritorio --}}
                <div class="hidden md:block">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3 text-left w-20">N.º</th>
                                <th class="px-4 py-3 text-left">Hermano</th>
                                <th class="px-4 py-3 text-left">Puesto</th>
                                <th class="px-4 py-3 text-left">Insignia</th>
                                <th class="px-4 py-3 text-center w-24">Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($papeletasTramo as $pap)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/80" :class="asistencias[{{ $pap->id }}] ? 'bg-emerald-50/50' : ''">
                                    <td class="px-4 py-2.5 font-mono text-xs font-bold">{{ $pap->hermano->numero_hermano }}</td>
                                    <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $pap->hermano->nombreCompleto() }}</td>
                                    <td class="px-4 py-2.5 text-slate-700">{{ $pap->puesto }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $pap->insignia?->nombre ?: '—' }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <button
                                            type="button"
                                            @click="toggleAsistencia({{ $pap->id }})"
                                            class="w-8 h-8 rounded-full flex items-center justify-center transition"
                                            :class="asistencias[{{ $pap->id }}] ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400 hover:bg-slate-300'"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Móvil (celador) --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach ($papeletasTramo as $pap)
                        <div class="px-4 py-3 flex items-center justify-between gap-3" :class="asistencias[{{ $pap->id }}] ? 'bg-emerald-50' : ''">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-[color:var(--color-primary)]">{{ $pap->hermano->numero_hermano }}</span>
                                    <span class="font-semibold text-sm text-slate-800 truncate">{{ $pap->hermano->nombreCompleto() }}</span>
                                </div>
                                <div class="text-xs text-slate-500">{{ $pap->puesto }}{{ $pap->insignia ? ' — '.$pap->insignia->nombre : '' }}</div>
                            </div>
                            <button
                                type="button"
                                @click="toggleAsistencia({{ $pap->id }})"
                                class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition"
                                :class="asistencias[{{ $pap->id }}] ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-400'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card-premium p-12 text-center">
                <p class="text-slate-500">No hay papeletas emitidas para este ejercicio.</p>
                <a href="{{ route('salida.papeletas.index') }}" class="btn-accent text-xs mt-4 inline-block">Ir a emisión de papeletas</a>
            </div>
        @endforelse
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cortejoPage', (cfg) => ({
                asistencias: {
                    @foreach ($tramosAgrupados->flatten() as $pap)
                        {{ $pap->id }}: {{ $pap->asistencia ? 'true' : 'false' }},
                    @endforeach
                },

                async toggleAsistencia(id) {
                    this.asistencias[id] = !this.asistencias[id];
                    try {
                        await fetch(cfg.toggleUrl + '/' + id + '/asistencia', {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': cfg.csrfToken,
                                'Accept': 'application/json',
                            },
                        });
                    } catch (e) {
                        this.asistencias[id] = !this.asistencias[id];
                    }
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>
