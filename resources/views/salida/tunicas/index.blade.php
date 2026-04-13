<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6"
        x-data="{
            modalOpen: false,
            modo: 'crear',
            editId: null,
            form: { codigo: '', talla: '', estado: 'Disponible', hermano_id: '', fianza: 0, fecha_prestamo: '', fecha_devolucion: '', notas: '' },
            abrirNueva() {
                this.form = { codigo: '', talla: '', estado: 'Disponible', hermano_id: '', fianza: 0, fecha_prestamo: '', fecha_devolucion: '', notas: '' };
                this.modo = 'crear'; this.editId = null; this.modalOpen = true;
            },
            abrirEditar(t) {
                this.modo = 'editar'; this.editId = t.id;
                this.form = {
                    codigo: t.codigo, talla: t.talla, estado: t.estado,
                    hermano_id: t.hermano_id || '', fianza: t.fianza,
                    fecha_prestamo: t.fecha_prestamo ? t.fecha_prestamo.substring(0,10) : '',
                    fecha_devolucion: t.fecha_devolucion ? t.fecha_devolucion.substring(0,10) : '',
                    notas: t.notas || '',
                };
                this.modalOpen = true;
            },
        }"
    >
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Almacén de Túnicas</h2>
            <p class="text-sm text-slate-600 mt-1">Inventario y préstamo de túnicas de propiedad de la Hermandad</p>
        </div>

        @include('salida.partials.subnav')

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)] text-center">
                <div class="text-2xl font-bold text-[color:var(--color-primary)]">{{ $stats['total'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Total</div>
            </div>
            <div class="card-premium p-4 border-t-2 border-t-emerald-400 text-center">
                <div class="text-2xl font-bold text-emerald-700">{{ $stats['disponibles'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Disponibles</div>
            </div>
            <div class="card-premium p-4 border-t-2 border-t-amber-400 text-center">
                <div class="text-2xl font-bold text-amber-700">{{ $stats['prestadas'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Prestadas</div>
            </div>
            <div class="card-premium p-4 border-t-2 border-t-blue-400 text-center">
                <div class="text-2xl font-bold text-blue-700">{{ $stats['reparacion'] }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">En reparación</div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h3 class="text-lg font-bold text-[color:var(--color-primary)]">Inventario</h3>
            <button type="button" @click="abrirNueva()" class="btn-accent text-xs uppercase tracking-wider">+ Nueva túnica</button>
        </div>

        {{-- Filtros --}}
        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" action="{{ route('salida.tunicas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700">Buscar</label>
                    <input type="search" name="q" value="{{ request('q') }}" class="input-premium w-full" placeholder="Código o hermano…">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Estado</label>
                    <select name="estado" class="input-premium w-full">
                        <option value="">Todos</option>
                        <option value="Disponible" @selected(request('estado') === 'Disponible')>Disponible</option>
                        <option value="Prestada" @selected(request('estado') === 'Prestada')>Prestada</option>
                        <option value="En reparación" @selected(request('estado') === 'En reparación')>En reparación</option>
                        <option value="Baja" @selected(request('estado') === 'Baja')>Baja</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Talla</label>
                    <select name="talla" class="input-premium w-full">
                        <option value="">Todas</option>
                        @foreach ($tallas as $t)
                            <option value="{{ $t }}" @selected(request('talla') === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-4 flex gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Filtrar</button>
                    <a href="{{ route('salida.tunicas.index') }}" class="btn-soft text-xs">Limpiar</a>
                </div>
            </form>
        </div>

        {{-- Tabla --}}
        <div class="card-premium overflow-hidden mb-6">
            <div class="hidden md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 text-left">Código</th>
                            <th class="px-4 py-3 text-left">Talla</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-left">Hermano</th>
                            <th class="px-4 py-3 text-right">Fianza</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tunicas as $tun)
                            @php
                                $estadoBadge = match($tun->estado) {
                                    'Disponible' => 'bg-emerald-100 text-emerald-800',
                                    'Prestada' => 'bg-amber-100 text-amber-800',
                                    'En reparación' => 'bg-blue-100 text-blue-800',
                                    'Baja' => 'bg-red-100 text-red-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                <td class="px-4 py-2.5 font-mono text-xs font-bold">{{ $tun->codigo }}</td>
                                <td class="px-4 py-2.5">{{ $tun->talla }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $estadoBadge }}">{{ $tun->estado }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-700">{{ $tun->hermano ? $tun->hermano->nombreCompleto() : '—' }}</td>
                                <td class="px-4 py-2.5 text-right font-mono">{{ number_format($tun->fianza, 2, ',', '.') }} €</td>
                                <td class="px-4 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="abrirEditar(@js($tun->toArray()))" class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 flex items-center justify-center" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('salida.tunicas.destroy', $tun) }}" onsubmit="return confirm('¿Eliminar túnica?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-700 hover:bg-red-100 flex items-center justify-center" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">No hay túnicas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile --}}
            <div class="md:hidden divide-y divide-slate-100">
                @forelse ($tunicas as $tun)
                    @php
                        $estadoBadge = match($tun->estado) {
                            'Disponible' => 'bg-emerald-100 text-emerald-800',
                            'Prestada' => 'bg-amber-100 text-amber-800',
                            'En reparación' => 'bg-blue-100 text-blue-800',
                            'Baja' => 'bg-red-100 text-red-800',
                            default => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div class="px-4 py-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="font-mono text-xs font-bold">{{ $tun->codigo }}</span>
                                <span class="text-sm ml-2">Talla {{ $tun->talla }}</span>
                                <div class="text-xs text-slate-600 mt-0.5">{{ $tun->hermano ? $tun->hermano->nombreCompleto() : 'Sin asignar' }}</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $estadoBadge }}">{{ $tun->estado }}</span>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button type="button" @click="abrirEditar(@js($tun->toArray()))" class="text-xs text-blue-700 underline">Editar</button>
                            <form method="POST" action="{{ route('salida.tunicas.destroy', $tun) }}" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-700 underline">Eliminar</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center text-slate-500">No hay túnicas.</div>
                @endforelse
            </div>

            @if ($tunicas->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $tunicas->links() }}</div>
            @endif
        </div>

        {{-- Modal túnica --}}
        <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div class="absolute inset-0 bg-black opacity-40 z-0" @click="modalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10" @click.stop>
                <div class="border-b border-slate-200 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[color:var(--color-primary)]" x-text="modo === 'crear' ? 'Nueva Túnica' : 'Editar Túnica'"></h3>
                    <button type="button" @click="modalOpen = false" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <form
                        :action="modo === 'crear' ? '{{ route('salida.tunicas.store') }}' : ('{{ url('salida/tunicas') }}/' + editId)"
                        method="POST"
                    >
                        @csrf
                        <template x-if="modo === 'editar'"><input type="hidden" name="_method" value="PUT"></template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Código</label>
                                <input type="text" name="codigo" class="input-premium w-full" x-model="form.codigo" placeholder="TUN-001">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Talla</label>
                                <input type="text" name="talla" class="input-premium w-full" x-model="form.talla" placeholder="M, L, XL…">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Estado</label>
                                <select name="estado" class="input-premium w-full" x-model="form.estado">
                                    <option value="Disponible">Disponible</option>
                                    <option value="Prestada">Prestada</option>
                                    <option value="En reparación">En reparación</option>
                                    <option value="Baja">Baja</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Fianza (€)</label>
                                <input type="number" step="0.01" min="0" name="fianza" class="input-premium w-full" x-model="form.fianza">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha préstamo</label>
                                <input type="date" name="fecha_prestamo" class="input-premium w-full" x-model="form.fecha_prestamo">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha devolución</label>
                                <input type="date" name="fecha_devolucion" class="input-premium w-full" x-model="form.fecha_devolucion">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Notas</label>
                                <textarea name="notas" rows="2" class="input-premium w-full" x-model="form.notas"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="modalOpen = false" class="btn-soft text-xs">Cancelar</button>
                            <button type="submit" class="btn-accent text-xs uppercase tracking-wider" :disabled="!form.codigo || !form.talla">
                                <span x-text="modo === 'crear' ? 'Registrar' : 'Guardar'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
