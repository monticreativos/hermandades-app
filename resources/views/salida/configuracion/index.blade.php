<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6"
        x-data="configuracionSalida(@js([
            'insigniasUrl' => route('salida.insignias.index'),
            'insigniasStoreUrl' => route('salida.insignias.store'),
            'insigniasBaseUrl' => url('salida/insignias'),
            'insignias' => $insignias->toArray(),
        ]))"
    >
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Configuración de Salida</h2>
            <p class="text-sm text-slate-600 mt-1">Parámetros de la Estación de Penitencia e insignias del cortejo</p>
        </div>

        @include('salida.partials.subnav')

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif

        <div class="card-premium border border-slate-200 border-l-4 border-l-[color:var(--color-accent)] p-5 mb-6 bg-slate-50/80">
            <h3 class="text-sm font-bold text-[color:var(--color-primary)] mb-2">Cómo se guarda la Estación de Penitencia</h3>
            <ul class="text-sm text-slate-700 space-y-1.5 list-disc list-inside">
                <li><strong>Configuración por año:</strong> cada vez que pulsa «Guardar configuración», se crea o actualiza el registro de ese <strong>año natural</strong> en la tabla <code class="text-xs bg-white px-1 rounded">configuracion_salidas</code>. Los años anteriores <strong>no se borran</strong>.</li>
                <li><strong>Papeletas:</strong> van ligadas al <strong>ejercicio contable</strong> (año). Al cerrar un ejercicio y abrir otro, las papeletas del año pasado siguen en base de datos y son consultables en la ficha del hermano.</li>
                <li><strong>Insignias:</strong> catálogo general de la hermandad (se reutilizan cada campaña). Si necesita variaciones solo para un año, use las notas o cree insignias con nombre descriptivo.</li>
            </ul>
            @if ($ejercicioActual)
                <p class="text-xs text-slate-600 mt-3">Ejercicio contable abierto: <strong>{{ $ejercicioActual->año }}</strong> (coincide con el año por defecto al emitir papeletas).</p>
            @else
                <p class="text-xs text-amber-800 mt-3">No hay ejercicio marcado como «Abierto» en Economía. Cree o abra un ejercicio para alinear papeletas y contabilidad.</p>
            @endif
        </div>

        <div class="flex flex-wrap items-end gap-3 mb-4">
            <form method="GET" action="{{ route('salida.configuracion.index') }}" class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Editar configuración del año</label>
                    <select name="año" class="input-premium min-w-[8rem]" onchange="this.form.submit()">
                        @foreach ($añosSelector as $a)
                            <option value="{{ $a }}" @selected((int) $a === (int) $añoEditar)>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- Configuración general --}}
        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-4">Datos de la Salida — {{ $añoEditar }}</h3>
            <form method="POST" action="{{ route('salida.configuracion.guardar') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @csrf
                <input type="hidden" name="año" value="{{ $añoEditar }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha de salida</label>
                    <input type="date" name="fecha_salida" value="{{ $config?->fecha_salida?->format('Y-m-d') }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Donativo por defecto (€)</label>
                    <input type="number" step="0.01" min="0" name="donativo_defecto" value="{{ old('donativo_defecto', $config?->donativo_defecto ?? 0) }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Inicio reparto papeletas</label>
                    <input type="date" name="fecha_inicio_reparto" value="{{ $config?->fecha_inicio_reparto?->format('Y-m-d') }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fin reparto papeletas</label>
                    <input type="date" name="fecha_fin_reparto" value="{{ $config?->fecha_fin_reparto?->format('Y-m-d') }}" class="input-premium w-full">
                </div>
                <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                    <input type="hidden" name="activa" value="0">
                    <input type="checkbox" id="activa_salida" name="activa" value="1" class="rounded border-slate-300 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" @checked(old('activa', $config?->activa ?? true))>
                    <label for="activa_salida" class="text-sm text-slate-700 cursor-pointer">
                        <span class="font-semibold">Campaña de salida activa</span> para este año (referencia en secretaría; puede desmarcar años ya cerrados).
                    </label>
                </div>
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notas</label>
                    <textarea name="notas" rows="2" class="input-premium w-full">{{ old('notas', $config?->notas) }}</textarea>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider w-full">Guardar configuración</button>
                </div>
            </form>
        </div>

        @if ($historial->isNotEmpty())
            <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-3">Historial guardado (no se elimina al cambiar de año)</h3>
            <div class="card-premium overflow-hidden mb-6">
                <div class="hidden md:block">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-4 py-3 text-left">Año</th>
                                <th class="px-4 py-3 text-left">Fecha salida</th>
                                <th class="px-4 py-3 text-right">Donativo def.</th>
                                <th class="px-4 py-3 text-center">Activa</th>
                                <th class="px-4 py-3 text-left">Reparto</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historial as $h)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                    <td class="px-4 py-2.5 font-bold">{{ $h->año }}</td>
                                    <td class="px-4 py-2.5">{{ $h->fecha_salida?->format('d/m/Y') ?: '—' }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono">{{ number_format((float) $h->donativo_defecto, 2, ',', '.') }} €</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if ($h->activa)
                                            <span class="text-[10px] font-bold uppercase text-emerald-700">Sí</span>
                                        @else
                                            <span class="text-[10px] text-slate-500">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-600">
                                        {{ $h->fecha_inicio_reparto?->format('d/m') ?: '—' }} — {{ $h->fecha_fin_reparto?->format('d/m/Y') ?: '—' }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <a href="{{ route('salida.configuracion.index', ['año' => $h->año]) }}" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline">Abrir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach ($historial as $h)
                        <div class="px-4 py-3 flex items-center justify-between gap-2">
                            <div>
                                <span class="font-bold">{{ $h->año }}</span>
                                <span class="text-xs text-slate-500 block">Salida {{ $h->fecha_salida?->format('d/m/Y') ?: '—' }}</span>
                            </div>
                            <a href="{{ route('salida.configuracion.index', ['año' => $h->año]) }}" class="btn-soft text-xs">Ver</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Insignias --}}
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-[color:var(--color-primary)]">Insignias del Cortejo</h3>
            <button type="button" @click="abrirNuevaInsignia()" class="btn-accent text-xs uppercase tracking-wider">+ Nueva insignia</button>
        </div>

        <div class="card-premium overflow-hidden mb-6">
            <div class="hidden md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 text-left">Orden</th>
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-left">Tramo</th>
                            <th class="px-4 py-3 text-center">Máx. portadores</th>
                            <th class="px-4 py-3 text-center">Máx. acompañantes</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="ins in listaInsignias" :key="ins.id">
                            <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                <td class="px-4 py-2.5 font-mono text-xs font-bold" x-text="ins.orden"></td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800" x-text="ins.nombre"></td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                        :class="ins.tramo === 'Cristo' ? 'bg-violet-100 text-violet-800' : ins.tramo === 'Virgen' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'"
                                        x-text="ins.tramo">
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center" x-text="ins.max_portadores"></td>
                                <td class="px-4 py-2.5 text-center" x-text="ins.max_acompanantes"></td>
                                <td class="px-4 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="abrirEditarInsignia(ins)" class="w-8 h-8 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 flex items-center justify-center" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" @click="eliminarInsignia(ins)" class="w-8 h-8 rounded-full bg-red-50 text-red-700 hover:bg-red-100 flex items-center justify-center" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <template x-if="listaInsignias.length === 0">
                    <div class="px-4 py-12 text-center text-slate-500">No hay insignias configuradas.</div>
                </template>
            </div>

            {{-- Mobile --}}
            <div class="md:hidden divide-y divide-slate-100">
                <template x-for="ins in listaInsignias" :key="ins.id">
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-sm" x-text="ins.nombre"></span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold ml-2"
                                    :class="ins.tramo === 'Cristo' ? 'bg-violet-100 text-violet-800' : ins.tramo === 'Virgen' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'"
                                    x-text="ins.tramo">
                                </span>
                            </div>
                            <div class="flex gap-1">
                                <button type="button" @click="abrirEditarInsignia(ins)" class="text-xs text-blue-700 underline">Editar</button>
                                <button type="button" @click="eliminarInsignia(ins)" class="text-xs text-red-700 underline">Eliminar</button>
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Orden: <span x-text="ins.orden"></span> · Portadores: <span x-text="ins.max_portadores"></span> · Acomp.: <span x-text="ins.max_acompanantes"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal insignia --}}
        <div
            x-show="insModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-cloak
        >
            <div class="absolute inset-0 bg-black opacity-40 z-0" @click="insModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10" @click.stop>
                <div class="border-b border-slate-200 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                    <h3 class="text-lg font-bold text-[color:var(--color-primary)]" x-text="insModo === 'crear' ? 'Nueva Insignia' : 'Editar Insignia'"></h3>
                    <button type="button" @click="insModalOpen = false" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre</label>
                            <input type="text" class="input-premium w-full" x-model="insForm.nombre" placeholder="Cruz de Guía, Senatus…">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tramo</label>
                            <select class="input-premium w-full" x-model="insForm.tramo">
                                <option value="Cristo">Cristo</option>
                                <option value="Virgen">Virgen</option>
                                <option value="General">General</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Orden</label>
                            <input type="number" min="0" class="input-premium w-full" x-model.number="insForm.orden">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Máx. portadores</label>
                            <input type="number" min="1" class="input-premium w-full" x-model.number="insForm.max_portadores">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Máx. acompañantes</label>
                            <input type="number" min="0" class="input-premium w-full" x-model.number="insForm.max_acompanantes">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Notas</label>
                            <textarea rows="2" class="input-premium w-full" x-model="insForm.notas"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="insModalOpen = false" class="btn-soft text-xs">Cancelar</button>
                        <button type="button" @click="guardarInsignia()" class="btn-accent text-xs uppercase tracking-wider" :disabled="!insForm.nombre">
                            <span x-text="insModo === 'crear' ? 'Crear' : 'Guardar'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('configuracionSalida', (cfg) => ({
                listaInsignias: cfg.insignias,
                insModalOpen: false,
                insModo: 'crear',
                insEditId: null,
                insForm: { nombre: '', tramo: 'Cristo', orden: 0, max_portadores: 1, max_acompanantes: 0, notas: '' },

                csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                },

                resetInsForm() {
                    this.insForm = { nombre: '', tramo: 'Cristo', orden: 0, max_portadores: 1, max_acompanantes: 0, notas: '' };
                    this.insEditId = null;
                },

                abrirNuevaInsignia() {
                    this.resetInsForm();
                    this.insModo = 'crear';
                    this.insModalOpen = true;
                },

                abrirEditarInsignia(ins) {
                    this.insModo = 'editar';
                    this.insEditId = ins.id;
                    this.insForm = {
                        nombre: ins.nombre,
                        tramo: ins.tramo,
                        orden: ins.orden,
                        max_portadores: ins.max_portadores,
                        max_acompanantes: ins.max_acompanantes,
                        notas: ins.notas || '',
                    };
                    this.insModalOpen = true;
                },

                async guardarInsignia() {
                    const url = this.insModo === 'crear'
                        ? cfg.insigniasStoreUrl
                        : cfg.insigniasBaseUrl + '/' + this.insEditId;
                    const method = this.insModo === 'crear' ? 'POST' : 'PUT';

                    const res = await fetch(url, {
                        method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                        body: JSON.stringify(this.insForm),
                    });

                    if (res.ok) {
                        const data = await res.json();
                        if (this.insModo === 'crear') {
                            this.listaInsignias.push(data);
                        } else {
                            const idx = this.listaInsignias.findIndex(i => i.id === this.insEditId);
                            if (idx !== -1) this.listaInsignias[idx] = data;
                        }
                        this.listaInsignias.sort((a, b) => a.orden - b.orden);
                        this.insModalOpen = false;
                    }
                },

                async eliminarInsignia(ins) {
                    if (!confirm('¿Eliminar la insignia "' + ins.nombre + '"?')) return;
                    const res = await fetch(cfg.insigniasBaseUrl + '/' + ins.id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                    });
                    if (res.ok) {
                        this.listaInsignias = this.listaInsignias.filter(i => i.id !== ins.id);
                    }
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>
