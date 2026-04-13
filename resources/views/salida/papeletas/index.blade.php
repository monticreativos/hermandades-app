<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6"
        x-data="papeletasSitio(@js([
            'buscarUrl' => route('salida.papeletas.buscar-hermano'),
            'storeUrl' => route('salida.papeletas.store'),
            'ejercicioId' => $ejercicioId,
            'donativoDefecto' => $config?->donativo_defecto ?? 0,
            'insignias' => $insignias->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'tramo' => $i->tramo]),
            'puestos' => \App\Models\PapeletaSitio::PUESTOS,
        ]))"
    >
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Estación de Penitencia</h2>
            <p class="text-sm text-slate-600 mt-1">Gestión de papeletas de sitio, cortejo y túnicas</p>
        </div>

        @include('salida.partials.subnav')

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            <div class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)] text-center">
                <div class="text-2xl font-bold text-[color:var(--color-primary)]">{{ $stats['total'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Total</div>
            </div>
            <div class="card-premium p-4 border-t-2 border-t-emerald-400 text-center">
                <div class="text-2xl font-bold text-emerald-700">{{ $stats['emitidas'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Emitidas</div>
            </div>
            <div class="card-premium p-4 border-t-2 border-t-amber-400 text-center">
                <div class="text-2xl font-bold text-amber-700">{{ $stats['solicitadas'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Solicitadas</div>
            </div>
            <div class="card-premium p-4 border-t-2 border-t-red-400 text-center">
                <div class="text-2xl font-bold text-red-700">{{ $stats['anuladas'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Anuladas</div>
            </div>
            <div class="card-premium p-4 border-t-2 border-t-blue-400 text-center">
                <div class="text-2xl font-bold text-blue-700">{{ number_format($stats['donativos'], 2, ',', '.') }} €</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Donativos</div>
            </div>
        </div>

        {{-- Botón nueva papeleta --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h3 class="text-lg font-bold text-[color:var(--color-primary)]">Papeletas emitidas</h3>
            <button type="button" @click="abrirNueva()" class="btn-accent text-xs uppercase tracking-wider">
                + Nueva papeleta
            </button>
        </div>

        {{-- Filtros --}}
        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" action="{{ route('salida.papeletas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700">Buscar</label>
                    <input type="search" name="q" value="{{ request('q') }}" class="input-premium w-full" placeholder="Nombre, apellidos, nº hermano…">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Puesto</label>
                    <select name="puesto" class="input-premium w-full">
                        <option value="">Todos</option>
                        @foreach (\App\Models\PapeletaSitio::PUESTOS as $p)
                            <option value="{{ $p }}" @selected(request('puesto') === $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Estado</label>
                    <select name="estado" class="input-premium w-full">
                        <option value="">Todos</option>
                        <option value="Emitida" @selected(request('estado') === 'Emitida')>Emitida</option>
                        <option value="Solicitada" @selected(request('estado') === 'Solicitada')>Solicitada</option>
                        <option value="Anulada" @selected(request('estado') === 'Anulada')>Anulada</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Tramo</label>
                    <input type="text" name="tramo" value="{{ request('tramo') }}" class="input-premium w-full" placeholder="Ej: 1, 2…">
                </div>
                <div class="lg:col-span-3 flex gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Filtrar</button>
                    <a href="{{ route('salida.papeletas.index') }}" class="btn-soft text-xs">Limpiar</a>
                </div>
            </form>
        </div>

        {{-- Tabla escritorio --}}
        <div class="card-premium overflow-hidden mb-6">
            <div class="hidden md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 text-left">N.º</th>
                            <th class="px-4 py-3 text-left">Hermano</th>
                            <th class="px-4 py-3 text-left">Puesto</th>
                            <th class="px-4 py-3 text-left">Tramo</th>
                            <th class="px-4 py-3 text-left">Insignia</th>
                            <th class="px-4 py-3 text-right">Donativo</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($papeletas as $pap)
                            @php
                                $estadoBadge = match($pap->estado) {
                                    'Emitida' => 'bg-emerald-100 text-emerald-800',
                                    'Solicitada' => 'bg-amber-100 text-amber-800',
                                    'Anulada' => 'bg-red-100 text-red-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                <td class="px-4 py-2.5 font-mono text-xs font-bold">{{ $pap->hermano->numero_hermano }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $pap->hermano->nombreCompleto() }}</td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $pap->puesto }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $pap->tramo ?: '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $pap->insignia?->nombre ?: '—' }}</td>
                                <td class="px-4 py-2.5 text-right font-mono">{{ number_format($pap->donativo_pagado, 2, ',', '.') }} €</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $estadoBadge }}">
                                        {{ $pap->estado }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="abrirEditar(@js($pap->toArray()))" class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 flex items-center justify-center" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('salida.papeletas.destroy', $pap) }}" onsubmit="return confirm('¿Eliminar esta papeleta?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-700 hover:bg-red-100 flex items-center justify-center" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500">No hay papeletas para este ejercicio.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Cards móvil --}}
            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($papeletas as $pap)
                    @php
                        $estadoBadge = match($pap->estado) {
                            'Emitida' => 'bg-emerald-100 text-emerald-800',
                            'Solicitada' => 'bg-amber-100 text-amber-800',
                            'Anulada' => 'bg-red-100 text-red-800',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-mono text-xs font-bold text-[color:var(--color-primary)]">{{ $pap->hermano->numero_hermano }}</span>
                                <span class="font-semibold text-sm text-slate-800 ml-1">{{ $pap->hermano->nombreCompleto() }}</span>
                                <div class="text-xs text-slate-600 mt-0.5">{{ $pap->puesto }} · Tramo {{ $pap->tramo ?: '—' }}</div>
                                @if ($pap->insignia)
                                    <div class="text-xs text-slate-500">{{ $pap->insignia->nombre }}</div>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $estadoBadge }}">{{ $pap->estado }}</span>
                                <div class="font-mono text-xs mt-1">{{ number_format($pap->donativo_pagado, 2, ',', '.') }} €</div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button type="button" @click="abrirEditar(@js($pap->toArray()))" class="text-xs text-blue-700 underline">Editar</button>
                            <form method="POST" action="{{ route('salida.papeletas.destroy', $pap) }}" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-700 underline">Eliminar</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center text-slate-500">No hay papeletas.</div>
                @endforelse
            </div>

            @if ($papeletas->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $papeletas->links() }}
                </div>
            @endif
        </div>

        {{-- ========== MODAL NUEVA / EDITAR PAPELETA ========== --}}
        <div
            x-show="modalOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-cloak
        >
            <div class="absolute inset-0 bg-black opacity-40 z-0" @click="cerrarModal()"></div>
            <div
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10"
                @click.stop
            >
                <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 rounded-t-2xl flex items-center justify-between z-10">
                    <h3 class="text-lg font-bold text-[color:var(--color-primary)]" x-text="modo === 'crear' ? 'Emitir Papeleta de Sitio' : 'Editar Papeleta'"></h3>
                    <button type="button" @click="cerrarModal()" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Buscador de hermano (solo crear) --}}
                    <template x-if="modo === 'crear'">
                        <div class="mb-6">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Buscar hermano</label>
                            <div class="relative">
                                <input
                                    type="search"
                                    class="input-premium w-full"
                                    placeholder="Nombre, apellidos o nº hermano…"
                                    x-model="busqueda"
                                    @input.debounce.300ms="buscarHermanos()"
                                    @focus="buscarHermanos()"
                                >
                                <div
                                    x-show="resultados.length > 0"
                                    class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-xl border border-slate-200 max-h-60 overflow-y-auto"
                                >
                                    <template x-for="h in resultados" :key="h.id">
                                        <button
                                            type="button"
                                            class="w-full text-left px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-b-0"
                                            @click="seleccionarHermano(h)"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="font-mono text-xs font-bold text-[color:var(--color-primary)]" x-text="h.numero_hermano"></span>
                                                    <span class="font-semibold text-sm ml-1" x-text="h.nombre_completo"></span>
                                                    <span class="text-xs text-slate-500 ml-2" x-text="'Alta: ' + (h.fecha_alta || '—')"></span>
                                                </div>
                                                <div class="flex gap-2">
                                                    <template x-if="h.tiene_deuda">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                                            DEUDA: <span x-text="h.deuda_loteria.toFixed(2).replace('.', ',') + ' €'" class="ml-1"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="h.tiene_papeleta">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">YA TIENE PAPELETA</span>
                                                    </template>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Hermano seleccionado --}}
                            <template x-if="hermanoSeleccionado">
                                <div class="mt-3 rounded-xl border border-slate-200 p-4" :class="hermanoSeleccionado.tiene_deuda ? 'bg-red-50 border-red-300' : 'bg-emerald-50 border-emerald-200'">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="font-bold text-sm text-[color:var(--color-primary)]">
                                                <span x-text="'N.º ' + hermanoSeleccionado.numero_hermano"></span> —
                                                <span x-text="hermanoSeleccionado.nombre_completo"></span>
                                            </div>
                                            <div class="text-xs text-slate-600 mt-0.5">
                                                Alta: <span x-text="hermanoSeleccionado.fecha_alta || '—'"></span>
                                            </div>
                                        </div>
                                        <button type="button" @click="hermanoSeleccionado = null; form.hermano_id = null" class="text-xs text-slate-500 underline">Cambiar</button>
                                    </div>

                                    <template x-if="hermanoSeleccionado.tiene_deuda">
                                        <div class="mt-2 p-3 rounded-lg bg-red-100 border border-red-300 text-red-900 text-sm font-bold flex items-center gap-2">
                                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                            <span>DEUDA PENDIENTE: <span x-text="hermanoSeleccionado.deuda_loteria.toFixed(2).replace('.', ',') + ' €'"></span> (lotería sin cobrar)</span>
                                        </div>
                                    </template>
                                    <template x-if="hermanoSeleccionado.tiene_papeleta">
                                        <div class="mt-2 p-3 rounded-lg bg-amber-100 border border-amber-300 text-amber-900 text-sm font-bold">
                                            Este hermano ya tiene papeleta emitida para este ejercicio.
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Info hermano en modo editar --}}
                    <template x-if="modo === 'editar' && editPapeleta">
                        <div class="mb-6 rounded-xl bg-slate-50 border border-slate-200 p-4">
                            <div class="font-bold text-sm text-[color:var(--color-primary)]" x-text="editPapeleta.hermano?.apellidos + ', ' + editPapeleta.hermano?.nombre"></div>
                            <div class="text-xs text-slate-600">N.º hermano: <span x-text="editPapeleta.hermano?.numero_hermano"></span></div>
                        </div>
                    </template>

                    {{-- Formulario --}}
                    <form :action="modo === 'crear' ? '{{ route('salida.papeletas.store') }}' : ('{{ url('salida/papeletas') }}/' + (editPapeleta?.id || ''))" method="POST">
                        @csrf
                        <template x-if="modo === 'editar'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>
                        <template x-if="modo === 'crear'">
                            <div>
                                <input type="hidden" name="hermano_id" :value="form.hermano_id">
                                <input type="hidden" name="ejercicio_id" :value="config.ejercicioId">
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Puesto</label>
                                <select name="puesto" class="input-premium w-full" x-model="form.puesto" @change="onPuestoChange()">
                                    <option value="">Seleccionar…</option>
                                    <template x-for="p in config.puestos" :key="p">
                                        <option :value="p" x-text="p"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tramo</label>
                                <input type="text" name="tramo" class="input-premium w-full" placeholder="Ej: 1, 2, Cruz de Guía…" x-model="form.tramo">
                            </div>
                            <div x-show="mostrarInsignia">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Insignia</label>
                                <select name="insignia_id" class="input-premium w-full" x-model="form.insignia_id">
                                    <option value="">Ninguna</option>
                                    <template x-for="i in config.insignias" :key="i.id">
                                        <option :value="i.id" x-text="i.nombre + ' (' + i.tramo + ')'"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Donativo (€)</label>
                                <input type="number" name="donativo_pagado" step="0.01" min="0" class="input-premium w-full" x-model="form.donativo_pagado">
                            </div>
                            <template x-if="modo === 'editar'">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estado</label>
                                    <select name="estado" class="input-premium w-full" x-model="form.estado">
                                        <option value="Solicitada">Solicitada</option>
                                        <option value="Emitida">Emitida</option>
                                        <option value="Anulada">Anulada</option>
                                    </select>
                                </div>
                            </template>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Notas</label>
                                <textarea name="notas" rows="2" class="input-premium w-full" x-model="form.notas"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="cerrarModal()" class="btn-soft text-xs">Cancelar</button>
                            <button
                                type="submit"
                                class="btn-accent text-xs uppercase tracking-wider"
                                :disabled="modo === 'crear' && (!form.hermano_id || !form.puesto)"
                            >
                                <span x-text="modo === 'crear' ? 'Emitir Papeleta' : 'Guardar cambios'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('papeletasSitio', (config) => ({
                config,
                modalOpen: false,
                modo: 'crear',
                busqueda: '',
                resultados: [],
                hermanoSeleccionado: null,
                editPapeleta: null,
                mostrarInsignia: false,
                form: {
                    hermano_id: null,
                    puesto: '',
                    tramo: '',
                    insignia_id: '',
                    donativo_pagado: config.donativoDefecto || 0,
                    estado: 'Emitida',
                    notas: '',
                },

                resetForm() {
                    this.form = {
                        hermano_id: null,
                        puesto: '',
                        tramo: '',
                        insignia_id: '',
                        donativo_pagado: this.config.donativoDefecto || 0,
                        estado: 'Emitida',
                        notas: '',
                    };
                    this.hermanoSeleccionado = null;
                    this.busqueda = '';
                    this.resultados = [];
                    this.editPapeleta = null;
                    this.mostrarInsignia = false;
                },

                abrirNueva() {
                    this.resetForm();
                    this.modo = 'crear';
                    this.modalOpen = true;
                },

                abrirEditar(papeleta) {
                    this.resetForm();
                    this.modo = 'editar';
                    this.editPapeleta = papeleta;
                    this.form.puesto = papeleta.puesto;
                    this.form.tramo = papeleta.tramo || '';
                    this.form.insignia_id = papeleta.insignia_id || '';
                    this.form.donativo_pagado = papeleta.donativo_pagado;
                    this.form.estado = papeleta.estado;
                    this.form.notas = papeleta.notas || '';
                    this.onPuestoChange();
                    this.modalOpen = true;
                },

                cerrarModal() {
                    this.modalOpen = false;
                },

                onPuestoChange() {
                    const puestosConInsignia = ['Vara', 'Insignia', 'Acompañante de insignia'];
                    this.mostrarInsignia = puestosConInsignia.includes(this.form.puesto);
                    if (!this.mostrarInsignia) {
                        this.form.insignia_id = '';
                    }
                },

                async buscarHermanos() {
                    if (this.busqueda.length < 1) {
                        this.resultados = [];
                        return;
                    }
                    try {
                        const url = new URL(this.config.buscarUrl, window.location.origin);
                        url.searchParams.set('q', this.busqueda);
                        const res = await fetch(url);
                        const data = await res.json();
                        this.resultados = data.hermanos || [];
                    } catch (e) {
                        this.resultados = [];
                    }
                },

                seleccionarHermano(h) {
                    this.hermanoSeleccionado = h;
                    this.form.hermano_id = h.id;
                    this.resultados = [];
                    this.busqueda = '';
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>
