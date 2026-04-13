<x-app-layout>
    <x-slot name="header">
        <button
            type="button"
            x-data
            @click="$dispatch('open-modal', 'crear-hermano')"
            class="btn-accent uppercase tracking-wider text-xs"
        >
            Nuevo Hermano
        </button>
    </x-slot>

    <div
        class="py-8"
        x-data="{
            showFilters: false,
            hermanos: @js($hermanosJson),
            currentHermano: null,
            getHermanoById(id) {
                return this.hermanos.find((h) => String(h.id) === String(id)) ?? null;
            },
            fillEditForm(id) {
                this.currentHermano = this.getHermanoById(id);
                window.dispatchEvent(new CustomEvent('set-hermano-edicion', { detail: this.currentHermano }));
                $dispatch('open-modal', 'editar-hermano');
            },
            askDelete(id) {
                this.currentHermano = this.getHermanoById(id);
                window.dispatchEvent(new CustomEvent('set-hermano-eliminacion', { detail: this.currentHermano }));
                $dispatch('open-modal', 'eliminar-hermano');
            }
        }"
    >
        <div class="w-full px-2 sm:px-4 lg:px-6">
            <div class="mb-5">
                <h1 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Listado de Hermanos</h1>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 rounded-md bg-green-50 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-md bg-red-50 text-red-800 text-sm">
                    Revisa los datos del formulario.
                </div>
            @endif

            <div class="card-premium overflow-hidden border-t-2 border-t-[color:var(--color-accent)]">
                <div class="p-6 border-b border-slate-200 bg-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[color:var(--color-primary)]">Filtros y búsqueda</h3>
                        <button type="button" @click="showFilters = !showFilters" class="btn-soft">
                            <svg class="w-4 h-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 4h18" />
                                <path d="M6 12h12" />
                                <path d="M10 20h4" />
                            </svg>
                            Filtros
                        </button>
                    </div>

                    <form
                        method="GET"
                        action="{{ route('hermanos.index') }}"
                        class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 items-end"
                        :class="{ 'hidden': !showFilters }"
                    >
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Busqueda Nombre/DNI</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="input-premium" placeholder="Ej: Juan / 12345678Z">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-semibold text-slate-700">Estado</label>
                            <select name="estado" class="input-premium">
                                <option value="">Todos</option>
                                @foreach (['Alta', 'Baja', 'Difunto'] as $estado)
                                    <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3 flex gap-2 pt-1">
                            <button type="submit" class="btn-primary">Aplicar</button>
                            <a href="{{ route('hermanos.index') }}" class="btn-soft">Limpiar</a>
                        </div>
                    </form>
                </div>

                <div class="hidden md:block overflow-x-auto w-full">
                    <table class="w-full table-fixed divide-y divide-gray-200">
                        <colgroup>
                            <col class="w-[8%]">
                            <col class="w-[26%]">
                            <col class="w-[14%]">
                            <col class="w-[12%]">
                            <col class="w-[14%]">
                            <col class="w-[26%]">
                        </colgroup>
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Numero</th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Nombre</th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">DNI</th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Estado</th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Telefono</th>
                                <th class="px-4 py-4 text-right text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($hermanos as $hermano)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-4 text-sm font-semibold text-[color:var(--color-primary)]">{{ $hermano->numero_hermano }}</td>
                                    <td class="px-4 py-4 text-sm font-medium text-slate-900 truncate">{{ $hermano->nombre }} {{ $hermano->apellidos }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-800 font-mono truncate">{{ $hermano->dni }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @php
                                            $estadoClass = match($hermano->estado) {
                                                'Alta' => 'badge-estado-alta',
                                                'Baja' => 'badge-estado-baja',
                                                default => 'badge-estado-difunto',
                                            };
                                        @endphp
                                        <span class="{{ $estadoClass }}">
                                            {{ $hermano->estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-slate-800 truncate">{{ $hermano->telefono }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <div class="inline-flex items-center justify-end gap-2 w-full">
                                            <a href="{{ route('hermanos.show', $hermano) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Ver">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </a>
                                            <button type="button" @click="fillEditForm({{ $hermano->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Editar">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 20h9"/>
                                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="askDelete({{ $hermano->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-rose-200 text-rose-700 hover:bg-rose-50" title="Eliminar">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="3 6 5 6 21 6"/>
                                                    <path d="M19 6l-1 14H6L5 6"/>
                                                    <path d="M10 11v6"/>
                                                    <path d="M14 11v6"/>
                                                    <path d="M9 6V4h6v2"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-sm text-center text-gray-500">No hay hermanos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden p-4 space-y-4 bg-[color:var(--color-bg)]">
                    @forelse ($hermanos as $hermano)
                        <article class="card-premium p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-[color:var(--color-primary)] text-base">{{ $hermano->nombre }} {{ $hermano->apellidos }}</p>
                                    <p class="text-xs text-slate-600">N. {{ $hermano->numero_hermano }}</p>
                                </div>
                                @php
                                    $estadoCardClass = match($hermano->estado) {
                                        'Alta' => 'badge-estado-alta',
                                        'Baja' => 'badge-estado-baja',
                                        default => 'badge-estado-difunto',
                                    };
                                @endphp
                                <span class="{{ $estadoCardClass }}">{{ $hermano->estado }}</span>
                            </div>
                            <div class="mt-3 text-sm text-slate-700 space-y-1">
                                <p><span class="font-semibold text-slate-900">DNI:</span> {{ $hermano->dni }}</p>
                                <p><span class="font-semibold text-slate-900">Telefono:</span> {{ $hermano->telefono ?: 'Sin telefono' }}</p>
                            </div>
                            <div class="mt-4 flex items-center justify-end gap-2">
                                <a href="{{ route('hermanos.show', $hermano) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Ver">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <button type="button" @click="fillEditForm({{ $hermano->id }})" class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Editar">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </button>
                                <button type="button" @click="askDelete({{ $hermano->id }})" class="inline-flex items-center justify-center w-10 h-10 rounded-full border border-rose-200 text-rose-700 hover:bg-rose-50" title="Eliminar">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-6 border border-dashed rounded-lg">
                            <p class="text-sm text-gray-600">No hay hermanos registrados.</p>
                            <button type="button" @click="$dispatch('open-modal', 'crear-hermano')" class="mt-3 px-3 py-2 text-xs rounded bg-gray-800 text-white">
                                Crear primer hermano
                            </button>
                        </div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $hermanos->links() }}
                </div>
            </div>
        </div>
    </div>

    <x-modal name="crear-hermano" :show="$errors->any()" maxWidth="2xl">
        <form
            method="POST"
            action="{{ route('hermanos.store') }}"
            enctype="multipart/form-data"
            class="p-8"
            x-data="{
                step: 1,
                totalSteps: 4,
                partidaFileName: '',
                dniFileName: '',
                handleFileSelect(refName, fileNameKey, event) {
                    const file = event?.target?.files?.[0];
                    if (!file) return;
                    this[fileNameKey] = file.name;
                },
                handleDrop(refName, fileNameKey, event) {
                    event.preventDefault();
                    const file = event?.dataTransfer?.files?.[0];
                    if (!file) return;
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs[refName].files = dt.files;
                    this[fileNameKey] = file.name;
                },
                clearFile(refName, fileNameKey) {
                    this.$refs[refName].value = '';
                    this[fileNameKey] = '';
                }
            }"
        >
            @csrf
            <h3 class="text-2xl font-bold text-[color:var(--color-primary)] mb-5">Nuevo Hermano</h3>

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                    <ul class="list-disc ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-5">
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-[color:var(--color-accent)] transition-all" :style="`width:${(step/totalSteps)*100}%`"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-slate-600">Paso <span x-text="step"></span> de <span x-text="totalSteps"></span></p>
            </div>

            <div x-show="step === 1" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Numero de hermano</label>
                    <input name="numero_hermano" value="{{ old('numero_hermano') }}" class="input-premium" placeholder="Auto si se deja vacio">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">DNI/NIE *</label>
                    <input name="dni" required value="{{ old('dni') }}" class="input-premium" placeholder="12345678Z">
                    @error('dni') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" required value="{{ old('nombre') }}" class="input-premium" placeholder="Nombre">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Apellidos *</label>
                    <input name="apellidos" required value="{{ old('apellidos') }}" class="input-premium" placeholder="Apellidos">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" required value="{{ old('fecha_nacimiento') }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Sexo *</label>
                    <select name="sexo" class="input-premium">
                    @foreach (['Hombre', 'Mujer', 'Otro'] as $sexo)
                        <option value="{{ $sexo }}" @selected(old('sexo') === $sexo)>{{ $sexo }}</option>
                    @endforeach
                    </select>
                </div>
            </div>

            <div x-show="step === 2" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Telefono</label>
                    <input name="telefono" value="{{ old('telefono') }}" class="input-premium" placeholder="Telefono">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Email</label>
                    <input name="email" value="{{ old('email') }}" class="input-premium" placeholder="Email">
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Direccion</label>
                    <input name="direccion" value="{{ old('direccion') }}" class="input-premium" placeholder="Direccion completa">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Localidad</label>
                    <input name="localidad" value="{{ old('localidad') }}" class="input-premium" placeholder="Localidad">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Provincia</label>
                    <input name="provincia" value="{{ old('provincia') }}" class="input-premium" placeholder="Provincia">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Codigo Postal</label>
                    <input name="codigo_postal" value="{{ old('codigo_postal') }}" class="input-premium" placeholder="41000">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Estado *</label>
                    <select name="estado" class="input-premium">
                    @foreach (['Alta', 'Baja', 'Difunto'] as $estado)
                        <option value="{{ $estado }}" @selected(old('estado', 'Alta') === $estado)>{{ $estado }}</option>
                    @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de alta</label>
                    <input type="date" name="fecha_alta" value="{{ old('fecha_alta') }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de baja</label>
                    <input type="date" name="fecha_baja" value="{{ old('fecha_baja') }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de bautismo</label>
                    <input type="date" name="fecha_bautismo" value="{{ old('fecha_bautismo') }}" class="input-premium">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Parroquia de bautismo</label>
                    <input name="parroquia_bautismo" value="{{ old('parroquia_bautismo') }}" class="input-premium" placeholder="Parroquia Bautismo">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Observaciones</label>
                    <textarea name="observaciones" class="input-premium" placeholder="Observaciones">{{ old('observaciones') }}</textarea>
                </div>
            </div>

            <div x-show="step === 3" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2 flex justify-between items-center">
                    <label class="text-xs font-semibold text-slate-700">Banco y datos de cuenta</label>
                    <button type="button" class="btn-soft" @click="$dispatch('open-modal', 'gestionar-bancos')">Gestionar Bancos</button>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Banco</label>
                    <select name="banco_id" class="input-premium">
                        <option value="">Seleccionar banco</option>
                        @foreach ($bancos as $banco)
                            <option value="{{ $banco->id }}" @selected((string)old('banco_id') === (string)$banco->id)>
                                {{ $banco->nombre }}{{ $banco->codigo ? ' ('.$banco->codigo.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Sucursal</label>
                    <input name="sucursal" value="{{ old('sucursal') }}" class="input-premium" placeholder="Sucursal / Oficina">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">IBAN</label>
                    <input name="iban" value="{{ old('iban') }}" class="input-premium" placeholder="ES....">
                    @error('iban') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Titular cuenta (adulto)</label>
                    <input name="titular_cuenta" value="{{ old('titular_cuenta') }}" class="input-premium" placeholder="Titular de la cuenta">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Titular para menor de edad (padre/madre/tutor)</label>
                    <input name="titular_cuenta_menor" value="{{ old('titular_cuenta_menor') }}" class="input-premium" placeholder="Nombre del padre/madre/tutor">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Periodicidad cuota (domiciliación)</label>
                    <select name="periodicidad_pago" class="input-premium">
                        @foreach (\App\Services\Contabilidad\CuotaPeriodicidadService::periodicidades() as $per)
                            <option value="{{ $per }}" @selected(old('periodicidad_pago', 'Mensual') === $per)>{{ $per }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Importe cuota anual referencia (€)</label>
                    <input type="number" name="importe_cuota_anual_referencia" value="{{ old('importe_cuota_anual_referencia') }}" step="0.01" min="0" class="input-premium font-mono" placeholder="Vacío = hermandad">
                    <p class="text-[10px] text-slate-500 mt-1">Si se deja vacío se usa el importe por defecto de ajustes / 60 €.</p>
                </div>
            </div>

            <div x-show="step === 4" class="grid grid-cols-1 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Partida de Bautismo (PDF/JPG/PNG)</label>
                    <input
                        x-ref="partidaInput"
                        type="file"
                        name="partida_bautismo"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"
                        @change="handleFileSelect('partidaInput', 'partidaFileName', $event)"
                    >
                    <div
                        class="mt-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center transition hover:border-[color:var(--color-accent)] hover:bg-amber-50/30"
                        @dragover.prevent
                        @drop="handleDrop('partidaInput', 'partidaFileName', $event)"
                    >
                        <p class="text-sm text-slate-700">Arrastra y suelta aquí, o</p>
                        <button type="button" class="mt-2 btn-soft" @click="$refs.partidaInput.click()">Seleccionar archivo</button>
                        <p class="mt-2 text-xs text-slate-500" x-show="!partidaFileName">Ningún archivo seleccionado</p>
                        <div class="mt-2 inline-flex items-center gap-2" x-show="partidaFileName">
                            <span class="text-xs font-semibold text-emerald-700" x-text="partidaFileName"></span>
                            <button type="button" class="text-xs text-rose-700 underline" @click="clearFile('partidaInput', 'partidaFileName')">Quitar</button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">DNI Escaneado (PDF/JPG/PNG)</label>
                    <input
                        x-ref="dniInput"
                        type="file"
                        name="dni_escaneado"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"
                        @change="handleFileSelect('dniInput', 'dniFileName', $event)"
                    >
                    <div
                        class="mt-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center transition hover:border-[color:var(--color-accent)] hover:bg-amber-50/30"
                        @dragover.prevent
                        @drop="handleDrop('dniInput', 'dniFileName', $event)"
                    >
                        <p class="text-sm text-slate-700">Arrastra y suelta aquí, o</p>
                        <button type="button" class="mt-2 btn-soft" @click="$refs.dniInput.click()">Seleccionar archivo</button>
                        <p class="mt-2 text-xs text-slate-500" x-show="!dniFileName">Ningún archivo seleccionado</p>
                        <div class="mt-2 inline-flex items-center gap-2" x-show="dniFileName">
                            <span class="text-xs font-semibold text-emerald-700" x-text="dniFileName"></span>
                            <button type="button" class="text-xs text-rose-700 underline" @click="clearFile('dniInput', 'dniFileName')">Quitar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'crear-hermano')" class="btn-soft">Cancelar</button>
                <button type="button" x-show="step > 1" @click="step--" class="btn-soft">Anterior</button>
                <button type="button" x-show="step < totalSteps" @click="step++" class="btn-primary">Siguiente</button>
                <button type="submit" x-show="step === totalSteps" class="btn-accent">Crear Hermano</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="gestionar-bancos" :show="false" maxWidth="xl">
        <div class="p-6">
            <h3 class="text-xl font-bold text-[color:var(--color-primary)] mb-4">Gestion de Bancos</h3>

            <form method="POST" action="{{ route('bancos.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
                @csrf
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Nombre del banco</label>
                    <input type="text" name="nombre" class="input-premium" placeholder="Ej: CaixaBank" required>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Codigo</label>
                    <input type="text" name="codigo" class="input-premium" placeholder="2100">
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="btn-primary">Añadir banco</button>
                </div>
            </form>

            <div class="space-y-2 max-h-72 overflow-auto">
                @foreach ($bancos as $banco)
                    <div class="card-premium p-3">
                        <form method="POST" action="{{ route('bancos.update', $banco) }}" class="grid grid-cols-1 md:grid-cols-6 gap-2 items-end">
                            @csrf
                            @method('PUT')
                            <div class="md:col-span-3">
                                <label class="text-xs font-semibold text-slate-700">Banco</label>
                                <input type="text" name="nombre" value="{{ $banco->nombre }}" class="input-premium" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs font-semibold text-slate-700">Codigo</label>
                                <input type="text" name="codigo" value="{{ $banco->codigo }}" class="input-premium">
                            </div>
                            <div class="md:col-span-1 flex gap-2">
                                <button type="submit" class="btn-soft w-full">Guardar</button>
                        </form>
                        <form method="POST" action="{{ route('bancos.destroy', $banco) }}" onsubmit="return confirm('¿Eliminar banco?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 mt-6">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v6"/>
                                    <path d="M14 11v6"/>
                                </svg>
                            </button>
                        </form>
                            </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-modal>

    <x-modal name="editar-hermano" :show="false" maxWidth="2xl">
        <form
            method="POST"
            :action="currentHermano ? '{{ url('hermanos') }}/' + currentHermano.id : '#'"
            enctype="multipart/form-data"
            class="p-8"
            x-data="{
                currentHermano: null,
                section: 'personales',
                partidaEditFileName: '',
                dniEditFileName: '',
                handleFileSelect(refName, fileNameKey, event) {
                    const file = event?.target?.files?.[0];
                    if (!file) return;
                    this[fileNameKey] = file.name;
                },
                handleDrop(refName, fileNameKey, event) {
                    event.preventDefault();
                    const file = event?.dataTransfer?.files?.[0];
                    if (!file) return;
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs[refName].files = dt.files;
                    this[fileNameKey] = file.name;
                },
                clearFile(refName, fileNameKey) {
                    this.$refs[refName].value = '';
                    this[fileNameKey] = '';
                }
            }"
            x-on:set-hermano-edicion.window="currentHermano = $event.detail ?? null; section = 'personales'; partidaEditFileName = ''; dniEditFileName = ''"
        >
            @csrf
            @method('PUT')
            <h3 class="text-2xl font-bold text-[color:var(--color-primary)] mb-5">Editar Hermano</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-5">
                <button type="button" @click="section = 'personales'" :class="section === 'personales' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Personales</button>
                <button type="button" @click="section = 'contacto'" :class="section === 'contacto' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Contacto</button>
                <button type="button" @click="section = 'bancarios'" :class="section === 'bancarios' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Bancarios</button>
                <button type="button" @click="section = 'documentacion'" :class="section === 'documentacion' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Documentación</button>
            </div>

            <div x-show="section === 'personales'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Numero de hermano</label>
                    <input name="numero_hermano" :value="currentHermano?.numero_hermano ?? ''" class="input-premium" placeholder="Numero Hermano">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">DNI/NIE *</label>
                    <input name="dni" required :value="currentHermano?.dni ?? ''" class="input-premium" placeholder="DNI/NIE">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" required :value="currentHermano?.nombre ?? ''" class="input-premium" placeholder="Nombre">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Apellidos *</label>
                    <input name="apellidos" required :value="currentHermano?.apellidos ?? ''" class="input-premium" placeholder="Apellidos">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" required :value="currentHermano?.fecha_nacimiento ?? ''" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Sexo *</label>
                    <select name="sexo" class="input-premium">
                        <template x-for="sexo in ['Hombre','Mujer','Otro']" :key="sexo">
                            <option :value="sexo" :selected="currentHermano?.sexo === sexo" x-text="sexo"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Estado *</label>
                    <select name="estado" class="input-premium">
                        <template x-for="estado in ['Alta','Baja','Difunto']" :key="estado">
                            <option :value="estado" :selected="currentHermano?.estado === estado" x-text="estado"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div x-show="section === 'contacto'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Telefono</label>
                    <input name="telefono" :value="currentHermano?.telefono ?? ''" class="input-premium" placeholder="Telefono">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Email</label>
                    <input name="email" :value="currentHermano?.email ?? ''" class="input-premium" placeholder="Email">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Direccion</label>
                    <input name="direccion" :value="currentHermano?.direccion ?? ''" class="input-premium" placeholder="Direccion">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Localidad</label>
                    <input name="localidad" :value="currentHermano?.localidad ?? ''" class="input-premium" placeholder="Localidad">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Provincia</label>
                    <input name="provincia" :value="currentHermano?.provincia ?? ''" class="input-premium" placeholder="Provincia">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Codigo Postal</label>
                    <input name="codigo_postal" :value="currentHermano?.codigo_postal ?? ''" class="input-premium" placeholder="Codigo Postal">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de alta</label>
                    <input type="date" name="fecha_alta" :value="currentHermano?.fecha_alta ?? ''" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de baja</label>
                    <input type="date" name="fecha_baja" :value="currentHermano?.fecha_baja ?? ''" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Fecha de bautismo</label>
                    <input type="date" name="fecha_bautismo" :value="currentHermano?.fecha_bautismo ?? ''" class="input-premium">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Parroquia de bautismo</label>
                    <input name="parroquia_bautismo" :value="currentHermano?.parroquia_bautismo ?? ''" class="input-premium" placeholder="Parroquia Bautismo">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Observaciones</label>
                    <textarea name="observaciones" class="input-premium" placeholder="Observaciones" x-text="currentHermano?.observaciones ?? ''"></textarea>
                </div>
            </div>

            <div x-show="section === 'bancarios'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Banco</label>
                    <select name="banco_id" class="input-premium">
                        <option value="">Seleccionar banco</option>
                        @foreach ($bancos as $banco)
                            <option value="{{ $banco->id }}" :selected="String(currentHermano?.banco_id ?? '') === '{{ (string)$banco->id }}'">
                                {{ $banco->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Sucursal</label>
                    <input name="sucursal" :value="currentHermano?.sucursal ?? ''" class="input-premium" placeholder="Sucursal">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">IBAN</label>
                    <input name="iban" :value="currentHermano?.iban ?? ''" class="input-premium" placeholder="IBAN">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Titular cuenta (adulto)</label>
                    <input name="titular_cuenta" :value="currentHermano?.titular_cuenta ?? ''" class="input-premium" placeholder="Titular de la cuenta">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Titular para menor (padre/madre/tutor)</label>
                    <input name="titular_cuenta_menor" :value="currentHermano?.titular_cuenta_menor ?? ''" class="input-premium" placeholder="Titular para menor">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Periodicidad cuota</label>
                    <select name="periodicidad_pago" class="input-premium">
                        @foreach (\App\Services\Contabilidad\CuotaPeriodicidadService::periodicidades() as $per)
                            <option value="{{ $per }}" :selected="(currentHermano?.periodicidad_pago || 'Mensual') === '{{ $per }}'">{{ $per }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Cuota anual referencia (€)</label>
                    <input type="number" name="importe_cuota_anual_referencia" step="0.01" min="0" :value="currentHermano?.importe_cuota_anual_referencia ?? ''" class="input-premium font-mono" placeholder="Vacío = defecto">
                </div>
            </div>

            <div x-show="section === 'documentacion'" class="grid grid-cols-1 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Reemplazar Partida de Bautismo (PDF/JPG/PNG)</label>
                    <input
                        x-ref="partidaEditInput"
                        type="file"
                        name="partida_bautismo"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"
                        @change="handleFileSelect('partidaEditInput', 'partidaEditFileName', $event)"
                    >
                    <div
                        class="mt-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center transition hover:border-[color:var(--color-accent)] hover:bg-amber-50/30"
                        @dragover.prevent
                        @drop="handleDrop('partidaEditInput', 'partidaEditFileName', $event)"
                    >
                        <p class="text-sm text-slate-700">Arrastra y suelta aquí, o</p>
                        <button type="button" class="mt-2 btn-soft" @click="$refs.partidaEditInput.click()">Seleccionar archivo</button>
                        <p class="mt-2 text-xs text-slate-500" x-show="!partidaEditFileName">Ningún archivo seleccionado</p>
                        <div class="mt-2 inline-flex items-center gap-2" x-show="partidaEditFileName">
                            <span class="text-xs font-semibold text-emerald-700" x-text="partidaEditFileName"></span>
                            <button type="button" class="text-xs text-rose-700 underline" @click="clearFile('partidaEditInput', 'partidaEditFileName')">Quitar</button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Reemplazar DNI Escaneado (PDF/JPG/PNG)</label>
                    <input
                        x-ref="dniEditInput"
                        type="file"
                        name="dni_escaneado"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="hidden"
                        @change="handleFileSelect('dniEditInput', 'dniEditFileName', $event)"
                    >
                    <div
                        class="mt-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center transition hover:border-[color:var(--color-accent)] hover:bg-amber-50/30"
                        @dragover.prevent
                        @drop="handleDrop('dniEditInput', 'dniEditFileName', $event)"
                    >
                        <p class="text-sm text-slate-700">Arrastra y suelta aquí, o</p>
                        <button type="button" class="mt-2 btn-soft" @click="$refs.dniEditInput.click()">Seleccionar archivo</button>
                        <p class="mt-2 text-xs text-slate-500" x-show="!dniEditFileName">Ningún archivo seleccionado</p>
                        <div class="mt-2 inline-flex items-center gap-2" x-show="dniEditFileName">
                            <span class="text-xs font-semibold text-emerald-700" x-text="dniEditFileName"></span>
                            <button type="button" class="text-xs text-rose-700 underline" @click="clearFile('dniEditInput', 'dniEditFileName')">Quitar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'editar-hermano')" class="btn-soft">Cancelar</button>
                <button type="submit" class="btn-primary">Actualizar</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="eliminar-hermano" :show="false" maxWidth="md">
        <form
            method="POST"
            :action="currentHermano ? '{{ url('hermanos') }}/' + currentHermano.id : '#'"
            class="p-6"
            x-data="{ currentHermano: null }"
            x-on:set-hermano-eliminacion.window="currentHermano = $event.detail ?? null"
        >
            @csrf
            @method('DELETE')
            <h3 class="text-lg font-semibold text-gray-900">Eliminar Hermano</h3>
            <p class="mt-2 text-sm text-gray-600">
                Esta accion eliminara el registro de <strong x-text="currentHermano ? currentHermano.nombre + ' ' + currentHermano.apellidos : ''"></strong>.
            </p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'eliminar-hermano')" class="px-4 py-2 border rounded-md text-sm">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md text-sm">Eliminar</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
