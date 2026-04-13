@php
    use Illuminate\Support\Facades\Storage;

    $badgePatrimonio = static function (string $estado): string {
        return match ($estado) {
            'Excelente' => 'badge-patrimonio-excelente',
            'Bueno' => 'badge-patrimonio-bueno',
            'Regular' => 'badge-patrimonio-regular',
            'Necesita Restauración' => 'badge-patrimonio-restauracion',
            'En Restauración' => 'badge-patrimonio-en-restauracion',
            default => 'badge-patrimonio-regular',
        };
    };

    $thumbUrl = static function ($path): ?string {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    };

    $inicialesNombre = static function (?string $nombre): string {
        $parts = collect(explode(' ', trim((string) $nombre)))->filter()->take(2);

        return $parts->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('') ?: 'E';
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <button
            type="button"
            x-data
            @click="$dispatch('open-modal', 'crear-enser')"
            class="btn-accent uppercase tracking-wider text-xs"
        >
            Nuevo Enser
        </button>
    </x-slot>

    <div
        class="py-8"
        x-data="{
            showFilters: false,
            ensers: @js($enseresJson),
            currentEnser: null,
            getEnserById(id) {
                return this.ensers.find((e) => String(e.id) === String(id)) ?? null;
            },
            fillEditForm(id) {
                this.currentEnser = this.getEnserById(id);
                window.dispatchEvent(new CustomEvent('set-enser-edicion', { detail: this.currentEnser }));
                $dispatch('open-modal', 'editar-enser');
            },
            askDelete(id) {
                this.currentEnser = this.getEnserById(id);
                window.dispatchEvent(new CustomEvent('set-enser-eliminacion', { detail: this.currentEnser }));
                $dispatch('open-modal', 'eliminar-enser');
            }
        }"
        x-init="$nextTick(() => {
            @if ($errors->any() && old('edit_enser_id'))
                fillEditForm({{ (int) old('edit_enser_id') }});
            @elseif ($errors->any())
                $dispatch('open-modal', 'crear-enser');
            @endif
        })"
    >
        <div class="w-full px-2 sm:px-4 lg:px-6">
            <div class="mb-5">
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Inventario de Patrimonio</h2>
            </div>

            @if (session('status'))
                <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any() && ! old('edit_enser_id'))
                <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">
                    Revisa los datos del formulario.
                </div>
            @endif

            <div class="card-premium overflow-hidden border-t-2 border-t-[color:var(--color-accent)]">
                <div class="p-6 border-b border-slate-200 bg-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[color:var(--color-primary)]">Filtros</h3>
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
                        action="{{ route('patrimonio.index') }}"
                        class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 items-end"
                        :class="{ 'hidden': !showFilters }"
                    >
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Categoría</label>
                            <select name="categoria_id" class="input-premium">
                                <option value="">Todas</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat->id }}" @selected((string) request('categoria_id') === (string) $cat->id)>{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Ubicación</label>
                            <select name="ubicacion" class="input-premium">
                                <option value="">Todas</option>
                                @foreach ($ubicaciones as $ub)
                                    <option value="{{ $ub }}" @selected(request('ubicacion') === $ub)>{{ $ub }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Estado conservación</label>
                            <select name="estado_conservacion_id" class="input-premium">
                                <option value="">Todos</option>
                                @foreach ($estadosConservacion as $est)
                                    <option value="{{ $est->id }}" @selected((string) request('estado_conservacion_id') === (string) $est->id)>{{ $est->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-700">Búsqueda</label>
                            <input type="text" name="q" value="{{ request('q') }}" class="input-premium" placeholder="Nombre, autor, materiales…">
                        </div>
                        <div class="md:col-span-3 flex gap-2 pt-1">
                            <button type="submit" class="btn-primary">Aplicar</button>
                            <a href="{{ route('patrimonio.index') }}" class="btn-soft">Limpiar</a>
                        </div>
                    </form>
                </div>

                <div class="hidden md:block overflow-x-auto w-full">
                    <table class="w-full table-fixed divide-y divide-gray-200">
                        <colgroup>
                            <col class="w-[10%]">
                            <col class="w-[28%]">
                            <col class="w-[16%]">
                            <col class="w-[16%]">
                            <col class="w-[18%]">
                            <col class="w-[12%]">
                        </colgroup>
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide"></th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Nombre</th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Categoría</th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Ubicación</th>
                                <th class="px-4 py-4 text-left text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Estado</th>
                                <th class="px-4 py-4 text-right text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($enseres as $enser)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3">
                                        @php $u = $thumbUrl($enser->imagen_principal_path); @endphp
                                        @if ($u)
                                            <img src="{{ $u }}" alt="" class="h-11 w-11 rounded-lg object-cover border border-slate-200">
                                        @else
                                            <div class="h-11 w-11 rounded-lg border border-dashed border-slate-300 bg-slate-50 text-[10px] font-bold text-slate-600 flex items-center justify-center">
                                                {{ $inicialesNombre($enser->nombre) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium text-slate-900 truncate">{{ $enser->nombre }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700 truncate">{{ $enser->categoriaPatrimonio?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-4 text-sm text-slate-700 truncate">{{ $enser->ubicacion ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="{{ $badgePatrimonio($enser->estadoConservacionPatrimonio?->nombre ?? '') }}">
                                            {{ $enser->estadoConservacionPatrimonio?->nombre ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        <div class="inline-flex items-center justify-end gap-2 w-full">
                                            <a href="{{ route('patrimonio.show', $enser) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Ver">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </a>
                                            <button type="button" @click="fillEditForm({{ $enser->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-slate-300 text-slate-700 hover:bg-slate-100" title="Editar">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 20h9"/>
                                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="askDelete({{ $enser->id }})" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-rose-200 text-rose-700 hover:bg-rose-50" title="Eliminar">
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
                                    <td colspan="6" class="px-4 py-6 text-sm text-center text-gray-500">No hay enseres registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden p-4 space-y-4 bg-[color:var(--color-bg)]">
                    @forelse ($enseres as $enser)
                        @php $u = $thumbUrl($enser->imagen_principal_path); @endphp
                        <article class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4">
                            <div class="flex gap-3">
                                @if ($u)
                                    <img src="{{ $u }}" alt="" class="h-16 w-16 shrink-0 rounded-xl object-cover border border-slate-200">
                                @else
                                    <div class="h-16 w-16 shrink-0 rounded-xl border border-dashed border-slate-300 bg-slate-50 text-sm font-bold text-slate-600 flex items-center justify-center">
                                        {{ $inicialesNombre($enser->nombre) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-[color:var(--color-primary)] text-base leading-snug">{{ $enser->nombre }}</p>
                                    <p class="text-xs text-slate-600 mt-1">{{ $enser->categoriaPatrimonio?->nombre ?? 'Sin categoría' }}</p>
                                    <p class="text-sm text-slate-800 mt-1"><span class="font-semibold text-slate-900">Ubicación:</span> {{ $enser->ubicacion ?: '—' }}</p>
                                    <div class="mt-2">
                                        <span class="{{ $badgePatrimonio($enser->estadoConservacionPatrimonio?->nombre ?? '') }}">{{ $enser->estadoConservacionPatrimonio?->nombre ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <a href="{{ route('patrimonio.show', $enser) }}" class="btn-soft w-full text-center">Ver</a>
                                <button type="button" @click="fillEditForm({{ $enser->id }})" class="btn-soft w-full">Editar</button>
                                <button type="button" @click="askDelete({{ $enser->id }})" class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-medium rounded-xl bg-rose-50 border border-rose-200 text-rose-700">Eliminar</button>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-6 border border-dashed rounded-xl">
                            <p class="text-sm text-gray-600">No hay enseres registrados.</p>
                            <button type="button" @click="$dispatch('open-modal', 'crear-enser')" class="mt-3 btn-accent text-xs uppercase">Registrar primer enser</button>
                        </div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $enseres->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal crear --}}
    <x-modal name="crear-enser" :show="$errors->any() && ! old('edit_enser_id')" maxWidth="2xl">
        <form
            method="POST"
            action="{{ route('patrimonio.store') }}"
            enctype="multipart/form-data"
            class="p-8"
            x-data="{
                section: 'basico',
                imagenCrearFileName: '',
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
            <h3 class="text-2xl font-bold text-[color:var(--color-primary)] mb-5">Nuevo Enser</h3>

            @if ($errors->any() && ! old('edit_enser_id'))
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                    <ul class="list-disc ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-3 gap-2 mb-5">
                <button type="button" @click="section = 'basico'" :class="section === 'basico' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-2 py-2 text-[11px] sm:text-xs rounded-xl font-semibold leading-tight">Datos básicos</button>
                <button type="button" @click="section = 'artistico'" :class="section === 'artistico' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-2 py-2 text-[11px] sm:text-xs rounded-xl font-semibold leading-tight">Datos artísticos</button>
                <button type="button" @click="section = 'estado'" :class="section === 'estado' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-2 py-2 text-[11px] sm:text-xs rounded-xl font-semibold leading-tight">Estado y valor</button>
            </div>

            <div x-show="section === 'basico'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" value="{{ old('nombre') }}" class="input-premium" required>
                </div>
                <div class="md:col-span-2 flex flex-col gap-2">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <label class="text-xs font-semibold text-slate-700">Categoría *</label>
                        <button type="button" class="btn-soft text-[10px] uppercase" @click="$dispatch('open-modal', 'gestionar-categorias-patrimonio')">Gestionar categorías</button>
                    </div>
                    <select name="categoria_id" class="input-premium" required>
                        <option value="">Seleccionar</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}" @selected((string) old('categoria_id') === (string) $cat->id)>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Ubicación</label>
                    <input name="ubicacion" value="{{ old('ubicacion') }}" class="input-premium" placeholder="Ej: Almacén, Casa Hermandad, Vitrina 4">
                </div>
            </div>

            <div x-show="section === 'artistico'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Autor</label>
                    <input name="autor" value="{{ old('autor') }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Año creación</label>
                    <input type="number" name="año_creacion" value="{{ old('año_creacion') }}" class="input-premium" min="1000" max="2100" placeholder="Ej: 1998">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Materiales</label>
                    <input name="materiales" value="{{ old('materiales') }}" class="input-premium" placeholder="Ej: Plata de ley, madera de cedro">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Descripción detallada</label>
                    <textarea name="descripcion_detallada" rows="4" class="input-premium" placeholder="Ficha técnica, notas históricas…">{{ old('descripcion_detallada') }}</textarea>
                </div>
            </div>

            <div x-show="section === 'estado'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2 flex flex-col gap-2">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <label class="text-xs font-semibold text-slate-700">Estado conservación *</label>
                        <button type="button" class="btn-soft text-[10px] uppercase" @click="$dispatch('open-modal', 'gestionar-estados-conservacion')">Gestionar estados</button>
                    </div>
                    <select name="estado_conservacion_id" class="input-premium" required>
                        @foreach ($estadosConservacion as $est)
                            <option value="{{ $est->id }}" @selected((string) old('estado_conservacion_id', $defaultEstadoConservacionId) === (string) $est->id)>{{ $est->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Última revisión</label>
                    <input type="date" name="ultima_revision" value="{{ old('ultima_revision') }}" class="input-premium">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Valor estimado (€)</label>
                    <input type="number" step="0.01" name="valor_estimado" value="{{ old('valor_estimado') }}" class="input-premium" placeholder="Seguro / inventario">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Imagen principal (JPG/PNG/WEBP)</label>
                    <input
                        x-ref="imagenCrearInput"
                        type="file"
                        name="imagen_principal"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="hidden"
                        @change="handleFileSelect('imagenCrearInput', 'imagenCrearFileName', $event)"
                    >
                    <div
                        class="mt-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center transition hover:border-[color:var(--color-accent)] hover:bg-amber-50/30"
                        @dragover.prevent
                        @drop="handleDrop('imagenCrearInput', 'imagenCrearFileName', $event)"
                    >
                        <p class="text-sm text-slate-700">Arrastra y suelta aquí, o</p>
                        <button type="button" class="mt-2 btn-soft" @click="$refs.imagenCrearInput.click()">Seleccionar imagen</button>
                        <p class="mt-2 text-xs text-slate-500" x-show="!imagenCrearFileName">Ningún archivo seleccionado</p>
                        <div class="mt-2 inline-flex items-center gap-2" x-show="imagenCrearFileName">
                            <span class="text-xs font-semibold text-emerald-700" x-text="imagenCrearFileName"></span>
                            <button type="button" class="text-xs text-rose-700 underline" @click="clearFile('imagenCrearInput', 'imagenCrearFileName')">Quitar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'crear-enser')" class="btn-soft">Cancelar</button>
                <button type="submit" class="btn-accent">Guardar</button>
            </div>
        </form>
    </x-modal>

    <x-modal name="gestionar-categorias-patrimonio" :show="false" maxWidth="xl">
        <div class="p-6">
            <h3 class="text-xl font-bold text-[color:var(--color-primary)] mb-4">Categorías de patrimonio</h3>

            <form method="POST" action="{{ route('patrimonio.categorias.store') }}" class="grid grid-cols-1 gap-3 mb-5">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700">Nueva categoría</label>
                    <input type="text" name="nombre" class="input-premium" placeholder="Ej: Orfebrería" required>
                </div>
                <div>
                    <button type="submit" class="btn-primary">Añadir categoría</button>
                </div>
            </form>

            <div class="space-y-2 max-h-72 overflow-auto">
                @foreach ($categorias as $cat)
                    <div class="card-premium p-3 flex flex-col sm:flex-row sm:items-end gap-2">
                        <form method="POST" action="{{ route('patrimonio.categorias.update', $cat) }}" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-2 items-end">
                            @csrf
                            @method('PUT')
                            <div class="sm:col-span-10">
                                <label class="text-xs font-semibold text-slate-700">Nombre</label>
                                <input type="text" name="nombre" value="{{ $cat->nombre }}" class="input-premium" required>
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" class="btn-soft w-full">Guardar</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('patrimonio.categorias.destroy', $cat) }}" onsubmit="return confirm('¿Eliminar esta categoría?')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-full sm:w-10 h-10 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50" title="Eliminar">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v6"/>
                                    <path d="M14 11v6"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </x-modal>

    <x-modal name="gestionar-estados-conservacion" :show="false" maxWidth="xl">
        <div class="p-6">
            <h3 class="text-xl font-bold text-[color:var(--color-primary)] mb-4">Estados de conservación</h3>

            <form method="POST" action="{{ route('patrimonio.estados-conservacion.store') }}" class="grid grid-cols-1 gap-3 mb-5">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700">Nuevo estado</label>
                    <input type="text" name="nombre" class="input-premium" placeholder="Ej: En depósito" required>
                </div>
                <div>
                    <button type="submit" class="btn-primary">Añadir estado</button>
                </div>
            </form>

            <div class="space-y-2 max-h-72 overflow-auto">
                @foreach ($estadosConservacion as $estadoRow)
                    <div class="card-premium p-3 flex flex-col sm:flex-row sm:items-end gap-2">
                        <form method="POST" action="{{ route('patrimonio.estados-conservacion.update', $estadoRow) }}" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-2 items-end">
                            @csrf
                            @method('PUT')
                            <div class="sm:col-span-10">
                                <label class="text-xs font-semibold text-slate-700">Nombre</label>
                                <input type="text" name="nombre" value="{{ $estadoRow->nombre }}" class="input-premium" required>
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" class="btn-soft w-full">Guardar</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('patrimonio.estados-conservacion.destroy', $estadoRow) }}" onsubmit="return confirm('¿Eliminar este estado?')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-full sm:w-10 h-10 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50" title="Eliminar">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v6"/>
                                    <path d="M14 11v6"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </x-modal>

    {{-- Modal editar --}}
    <x-modal name="editar-enser" :show="false" maxWidth="2xl">
        <form
            method="POST"
            :action="currentEnser ? '{{ url('patrimonio') }}/' + currentEnser.id : '#'"
            enctype="multipart/form-data"
            class="p-8"
            x-data="{
                currentEnser: null,
                section: 'basico',
                imagenEditFileName: '',
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
            x-on:set-enser-edicion.window="currentEnser = $event.detail ?? null; section = 'basico'; imagenEditFileName = ''"
        >
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_enser_id" x-bind:value="currentEnser?.id ?? ''">

            <h3 class="text-2xl font-bold text-[color:var(--color-primary)] mb-5">Editar Enser</h3>

            @if ($errors->any() && old('edit_enser_id'))
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                    <ul class="list-disc ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-3 gap-2 mb-5">
                <button type="button" @click="section = 'basico'" :class="section === 'basico' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-2 py-2 text-[11px] sm:text-xs rounded-xl font-semibold leading-tight">Datos básicos</button>
                <button type="button" @click="section = 'artistico'" :class="section === 'artistico' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-2 py-2 text-[11px] sm:text-xs rounded-xl font-semibold leading-tight">Datos artísticos</button>
                <button type="button" @click="section = 'estado'" :class="section === 'estado' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-2 py-2 text-[11px] sm:text-xs rounded-xl font-semibold leading-tight">Estado y valor</button>
            </div>

            <div x-show="section === 'basico'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Nombre *</label>
                    <input name="nombre" required :value="currentEnser?.nombre ?? ''" class="input-premium">
                </div>
                <div class="md:col-span-2 flex flex-col gap-2">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <label class="text-xs font-semibold text-slate-700">Categoría *</label>
                        <button type="button" class="btn-soft text-[10px] uppercase" @click="$dispatch('open-modal', 'gestionar-categorias-patrimonio')">Gestionar categorías</button>
                    </div>
                    <select name="categoria_id" class="input-premium" required>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}" :selected="String(currentEnser?.categoria_id ?? '') === '{{ (string) $cat->id }}'">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Ubicación</label>
                    <input name="ubicacion" :value="currentEnser?.ubicacion ?? ''" class="input-premium">
                </div>
            </div>

            <div x-show="section === 'artistico'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Autor</label>
                    <input name="autor" :value="currentEnser?.autor ?? ''" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Año creación</label>
                    <input type="number" name="año_creacion" class="input-premium" min="1000" max="2100" :value="currentEnser?.año_creacion ?? ''">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Materiales</label>
                    <input name="materiales" :value="currentEnser?.materiales ?? ''" class="input-premium">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Descripción detallada</label>
                    <textarea name="descripcion_detallada" rows="4" class="input-premium" :value="currentEnser?.descripcion_detallada ?? ''"></textarea>
                </div>
            </div>

            <div x-show="section === 'estado'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2 flex flex-col gap-2">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <label class="text-xs font-semibold text-slate-700">Estado conservación *</label>
                        <button type="button" class="btn-soft text-[10px] uppercase" @click="$dispatch('open-modal', 'gestionar-estados-conservacion')">Gestionar estados</button>
                    </div>
                    <select name="estado_conservacion_id" class="input-premium" required>
                        @foreach ($estadosConservacion as $est)
                            <option value="{{ $est->id }}" :selected="String(currentEnser?.estado_conservacion_id ?? '') === '{{ (string) $est->id }}'">{{ $est->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Última revisión</label>
                    <input type="date" name="ultima_revision" :value="currentEnser?.ultima_revision ?? ''" class="input-premium">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Valor estimado (€)</label>
                    <input type="number" step="0.01" name="valor_estimado" :value="currentEnser?.valor_estimado ?? ''" class="input-premium">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-semibold text-slate-700">Nueva imagen principal (opcional)</label>
                    <input
                        x-ref="imagenEditInput"
                        type="file"
                        name="imagen_principal"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="hidden"
                        @change="handleFileSelect('imagenEditInput', 'imagenEditFileName', $event)"
                    >
                    <div
                        class="mt-2 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5 text-center transition hover:border-[color:var(--color-accent)] hover:bg-amber-50/30"
                        @dragover.prevent
                        @drop="handleDrop('imagenEditInput', 'imagenEditFileName', $event)"
                    >
                        <p class="text-sm text-slate-700">Arrastra y suelta aquí, o</p>
                        <button type="button" class="mt-2 btn-soft" @click="$refs.imagenEditInput.click()">Seleccionar imagen</button>
                        <p class="mt-2 text-xs text-slate-500" x-show="!imagenEditFileName">Dejar vacío para conservar la actual</p>
                        <div class="mt-2 inline-flex items-center gap-2" x-show="imagenEditFileName">
                            <span class="text-xs font-semibold text-emerald-700" x-text="imagenEditFileName"></span>
                            <button type="button" class="text-xs text-rose-700 underline" @click="clearFile('imagenEditInput', 'imagenEditFileName')">Quitar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'editar-enser')" class="btn-soft">Cancelar</button>
                <button type="submit" class="btn-primary">Actualizar</button>
            </div>
        </form>
    </x-modal>

    {{-- Modal eliminar --}}
    <x-modal name="eliminar-enser" :show="false" maxWidth="md">
        <form
            method="POST"
            :action="currentEnser ? '{{ url('patrimonio') }}/' + currentEnser.id : '#'"
            class="p-6"
            x-data="{ currentEnser: null }"
            x-on:set-enser-eliminacion.window="currentEnser = $event.detail ?? null"
        >
            @csrf
            @method('DELETE')
            <h3 class="text-lg font-bold text-[color:var(--color-primary)]">Eliminar enser</h3>
            <p class="mt-2 text-sm text-slate-600">
                Se eliminará <strong x-text="currentEnser ? currentEnser.nombre : ''"></strong> y su imagen asociada si existe.
            </p>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'eliminar-enser')" class="btn-soft">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold bg-rose-700 text-white hover:bg-rose-800">Eliminar</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
