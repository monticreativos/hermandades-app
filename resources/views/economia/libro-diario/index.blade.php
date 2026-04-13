@php
    $asientoLibroDiarioConfig = array_merge($asientoModalConfig, [
        'initialShowFilters' => request()->filled('fecha_desde')
            || request()->filled('fecha_hasta')
            || request()->filled('cuenta_contable_id')
            || request()->filled('q'),
        'validation' => ['hasErrors' => $errors->any()],
        'old' => [
            'mode' => old('_asiento_mode', 'create'),
            'asiento_id' => old('_asiento_id'),
            'fecha' => old('fecha'),
            'glosa' => old('glosa'),
            'apuntes' => old('apuntes', []),
        ],
    ]);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('economia.movimiento-rapido.create') }}" class="btn-soft uppercase tracking-wider text-xs border border-[color:var(--color-accent)]/50">
                Registrar movimiento
            </a>
            <button
                type="button"
                x-data
                @click="window.dispatchEvent(new CustomEvent('asiento-modal-open', { detail: { mode: 'create' } }))"
                class="btn-accent uppercase tracking-wider text-xs"
            >
                Nuevo asiento
            </button>
        </div>
    </x-slot>

    <div
        class="py-8"
        x-data="asientoLibroDiario(@js($asientoLibroDiarioConfig))"
        @asiento-modal-open.window="openFromEvent($event.detail)"
    >
        <div class="w-full px-2 sm:px-4 lg:px-6">
            <div class="mb-5">
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Libro diario</h2>
            </div>

            @include('economia.partials.subnav')

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

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <div class="card-premium overflow-hidden border-t-2 border-t-[color:var(--color-accent)]">
                <div class="p-6 border-b border-slate-200 bg-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[color:var(--color-primary)]">Filtros</h3>
                        <button type="button" @click="showFilters = !showFilters" class="btn-soft">
                            <svg class="w-4 h-4 mr-1.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 4h18" />
                                <path d="M6 12h12" />
                                <path d="M10 20h4" />
                            </svg>
                            Filtros
                        </button>
                    </div>

                    <form
                        method="GET"
                        action="{{ route('economia.libro-diario.index') }}"
                        class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end"
                        x-show="showFilters"
                        x-transition.opacity.duration.150ms
                        x-cloak
                    >
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Desde</label>
                            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input-premium w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Hasta</label>
                            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input-premium w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Cuenta</label>
                            <select name="cuenta_contable_id" class="input-premium w-full">
                                <option value="">Todas</option>
                                @foreach ($cuentas as $c)
                                    <option value="{{ $c->id }}" @selected((string) request('cuenta_contable_id') === (string) $c->id)>
                                        {{ $c->codigo }} — {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Palabra clave</label>
                            <input type="search" name="q" value="{{ request('q') }}" placeholder="Glosa o n.º asiento" class="input-premium w-full">
                        </div>
                        <div class="md:col-span-2 lg:col-span-4 flex gap-2">
                            <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Aplicar</button>
                            <a href="{{ route('economia.libro-diario.index') }}" class="btn-soft text-xs">Limpiar</a>
                        </div>
                    </form>
                </div>

                {{-- Escritorio: tabla agrupada por asiento --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Asiento</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Cuenta</th>
                                <th class="px-4 py-3">Concepto línea</th>
                                <th class="px-4 py-3 text-right">Debe</th>
                                <th class="px-4 py-3 text-right">Haber</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($asientos as $index => $asiento)
                                @php $stripe = $index % 2 === 0 ? 'bg-slate-50/80' : 'bg-white'; @endphp
                                @foreach ($asiento->apuntes as $pi => $apunte)
                                    <tr class="border-b border-slate-100 {{ $stripe }}">
                                        @if ($pi === 0)
                                            <td class="px-4 py-2 align-top font-semibold text-[color:var(--color-primary)] whitespace-nowrap" rowspan="{{ $asiento->apuntes->count() }}">
                                                #{{ $asiento->numero_asiento }} <span class="text-slate-500 font-normal">/ {{ $asiento->ejercicio->año }}</span>
                                            </td>
                                            <td class="px-4 py-2 align-top text-slate-700 whitespace-nowrap" rowspan="{{ $asiento->apuntes->count() }}">
                                                {{ $asiento->fecha->format('d/m/Y') }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-2 text-slate-800">
                                            <span class="font-mono text-xs">{{ $apunte->cuentaContable->codigo }}</span>
                                            <span class="text-slate-600">{{ $apunte->cuentaContable->nombre }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-slate-600 max-w-xs truncate" title="{{ $apunte->concepto_detalle }}">
                                            {{ $apunte->concepto_detalle ?: '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-mono tabular-nums">{{ $apunte->debe > 0 ? number_format($apunte->debe, 2, ',', '.') : '—' }}</td>
                                        <td class="px-4 py-2 text-right font-mono tabular-nums">{{ $apunte->haber > 0 ? number_format($apunte->haber, 2, ',', '.') : '—' }}</td>
                                        @if ($pi === 0)
                                            <td class="px-4 py-2 align-top text-right whitespace-nowrap" rowspan="{{ $asiento->apuntes->count() }}">
                                                @if ($asiento->ejercicio->estaAbierto())
                                                    <button
                                                        type="button"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50"
                                                        title="Editar"
                                                        @click="openEdit({{ (int) $asiento->id }})"
                                                    >
                                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                                    </button>
                                                    <form method="POST" action="{{ route('economia.asientos.destroy', $asiento) }}" class="inline" onsubmit="return confirm('¿Eliminar este asiento?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-100 text-red-700 hover:bg-red-50" title="Eliminar">
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-slate-400">Cerrado</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @if ($asiento->apuntes->isEmpty())
                                    <tr class="{{ $stripe }}">
                                        <td class="px-4 py-2 font-semibold text-[color:var(--color-primary)]">#{{ $asiento->numero_asiento }}</td>
                                        <td class="px-4 py-2">{{ $asiento->fecha->format('d/m/Y') }}</td>
                                        <td colspan="4" class="px-4 py-2 text-slate-500">Sin apuntes</td>
                                    </tr>
                                @endif
                                <tr class="{{ $stripe }}">
                                    <td colspan="4" class="px-4 py-1 text-xs text-slate-500 italic border-b border-slate-200">
                                        {{ $asiento->glosa }}
                                    </td>
                                    <td colspan="3" class="border-b border-slate-200"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">No hay asientos con los filtros actuales.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Móvil: tarjetas por asiento --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse ($asientos as $index => $asiento)
                        <div class="{{ $index % 2 === 0 ? 'bg-slate-50/80' : 'bg-white' }} p-4 space-y-3">
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <div class="text-sm font-bold text-[color:var(--color-primary)]">
                                        Asiento #{{ $asiento->numero_asiento }} / {{ $asiento->ejercicio->año }}
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $asiento->fecha->format('d/m/Y') }}</div>
                                </div>
                                @if ($asiento->ejercicio->estaAbierto())
                                    <div class="flex gap-1 shrink-0">
                                        <button type="button" class="h-9 w-9 rounded-full border border-slate-200 flex items-center justify-center" @click="openEdit({{ (int) $asiento->id }})">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('economia.asientos.destroy', $asiento) }}" onsubmit="return confirm('¿Eliminar este asiento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-9 w-9 rounded-full border border-red-100 text-red-700 flex items-center justify-center">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            <p class="text-sm text-slate-700">{{ $asiento->glosa }}</p>
                            <ul class="space-y-2 text-sm">
                                @foreach ($asiento->apuntes as $apunte)
                                    <li class="rounded-lg border border-slate-200 p-3 bg-white">
                                        <div class="font-mono text-xs text-slate-800">{{ $apunte->cuentaContable->codigo }} {{ $apunte->cuentaContable->nombre }}</div>
                                        @if ($apunte->concepto_detalle)
                                            <div class="text-xs text-slate-500 mt-1">{{ $apunte->concepto_detalle }}</div>
                                        @endif
                                        <div class="flex justify-between mt-2 font-mono tabular-nums text-sm">
                                            <span class="text-emerald-800">D: {{ $apunte->debe > 0 ? number_format($apunte->debe, 2, ',', '.') : '—' }}</span>
                                            <span class="text-amber-800">H: {{ $apunte->haber > 0 ? number_format($apunte->haber, 2, ',', '.') : '—' }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">No hay asientos.</div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $asientos->links() }}
                </div>
            </div>
        </div>

        <x-modal name="asiento-contable" maxWidth="4xl" focusable>
            <div class="p-6 max-h-[85vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-1" x-text="mode === 'edit' ? 'Editar asiento' : 'Nuevo asiento'"></h3>
                <p class="text-sm text-slate-500 mb-4">Partida doble: la suma del debe debe coincidir con la del haber.</p>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                    x-bind:action="formAction"
                    @submit="if (!cuadrado()) { $event.preventDefault(); }"
                >
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="mode !== 'edit'">
                    <input type="hidden" name="_asiento_mode" :value="mode">
                    <input type="hidden" name="_asiento_id" :value="asientoId ?? ''" :disabled="mode !== 'edit'">

                    <div x-show="mode === 'create'" x-cloak class="mb-4 rounded-xl border border-[color:var(--color-accent)]/40 bg-amber-50/30 p-4 space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">¿Qué ha ocurrido? Descríbelo con tus palabras</label>
                        <textarea
                            x-model="iaDescripcion"
                            rows="3"
                            class="input-premium w-full"
                            placeholder="Describe aquí el hecho contable..."
                        ></textarea>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tratamiento IVA</label>
                            <select x-model="iaIvaModo" class="input-premium w-full sm:w-72">
                                <option value="auto">Auto (según contexto)</option>
                                <option value="soportado">IVA soportado (472)</option>
                                <option value="repercutido">IVA repercutido (477)</option>
                                <option value="sin_iva">Sin IVA / exento / no sujeto</option>
                            </select>
                        </div>
                        <div class="rounded-lg bg-white/70 border border-slate-200 px-3 py-2 text-xs text-slate-600">
                            <span class="font-semibold text-slate-700">Ejemplo:</span>
                            <span x-ref="ejemploIaTexto">Pago de 120€ por flores para el quinario con la cuenta del banco.</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="btn-soft text-xs border border-slate-200"
                                @click="navigator.clipboard.writeText($refs.ejemploIaTexto.innerText)"
                            >
                                Copiar ejemplo
                            </button>
                            <button type="button" class="btn-accent text-xs uppercase tracking-wider inline-flex items-center gap-2" @click="generarConIA()" :disabled="iaLoading">
                                <svg x-show="iaLoading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity=".25" stroke-width="4"></circle>
                                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                                </svg>
                                <span x-text="iaLoading ? 'Generando…' : '✨ Generar Asiento con IA'"></span>
                            </button>
                            <p class="text-xs text-slate-500">La IA propone cuentas y líneas; usted revisa y guarda.</p>
                        </div>
                        <p x-show="iaError" x-text="iaError" class="text-xs text-rose-700 font-medium"></p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Fecha</label>
                            <input type="date" name="fecha" required class="input-premium w-full" x-model="fecha">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Glosa</label>
                            <input type="text" name="glosa" required maxlength="500" class="input-premium w-full" x-model="glosa" placeholder="Concepto general del asiento">
                        </div>
                    </div>

                    <div
                        x-show="lines.length && (totalDebe() > 0 || totalHaber() > 0) && !cuadrado()"
                        x-cloak
                        class="mb-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm"
                    >
                        El asiento <strong>no está cuadrado</strong>. Diferencia: <span x-text="formatMoney(diff())"></span> €
                    </div>

                    <div class="rounded-xl border border-slate-200 overflow-hidden mb-4">
                        <div class="bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 uppercase tracking-wide">Líneas de apunte</div>
                        <template x-for="(line, idx) in lines" :key="idx">
                            <div class="p-3 border-t border-slate-100 space-y-2 relative">
                                <input type="hidden" :name="'apuntes['+idx+'][cuenta_contable_id]'" :value="line.cuenta_contable_id">
                                <div class="relative">
                                    <label class="block text-xs font-semibold text-slate-700">Cuenta</label>
                                    <input
                                        type="text"
                                        class="input-premium w-full"
                                        placeholder="Buscar por código o nombre…"
                                        x-model="line.q"
                                        @focus="line.open = true"
                                        @input.debounce.300ms="buscarCuenta(idx)"
                                        autocomplete="off"
                                    >
                                    <div
                                        x-show="line.open && line.resultados.length"
                                        x-cloak
                                        @click.outside="line.open = false"
                                        class="absolute z-20 mt-1 w-full max-h-48 overflow-auto rounded-xl border border-slate-200 bg-white shadow-lg text-sm"
                                    >
                                        <template x-for="c in line.resultados" :key="c.id">
                                            <button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b border-slate-50 last:border-0" @click="pickCuenta(idx, c)">
                                                <span x-text="c.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1" x-show="line.cuenta_label" x-text="line.cuenta_label"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Debe (€)</label>
                                        <input type="number" step="0.01" min="0" class="input-premium w-full font-mono" :name="'apuntes['+idx+'][debe]'" x-model="line.debe" @input="onImporte(idx, 'debe')">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Haber (€)</label>
                                        <input type="number" step="0.01" min="0" class="input-premium w-full font-mono" :name="'apuntes['+idx+'][haber]'" x-model="line.haber" @input="onImporte(idx, 'haber')">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">Concepto detalle</label>
                                    <input type="text" class="input-premium w-full" :name="'apuntes['+idx+'][concepto_detalle]'" x-model="line.concepto" maxlength="500">
                                </div>
                                <div
                                    x-show="esGastoDebe(line)"
                                    x-cloak
                                    class="rounded-xl border border-dashed border-[color:var(--color-accent)]/40 bg-slate-50/80 p-3 space-y-2"
                                >
                                    <p class="text-xs font-bold text-[color:var(--color-primary)] uppercase tracking-wide">Factura / ticket (grupo 6)</p>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Archivo PDF o imagen</label>
                                        <input type="file" class="input-premium w-full text-sm file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5" :name="'apuntes['+idx+'][archivo_factura]'" accept=".pdf,image/jpeg,image/png,image/webp">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Proveedor</label>
                                        <input type="text" class="input-premium w-full" :name="'apuntes['+idx+'][factura_proveedor]'" x-model="line.factura_proveedor" maxlength="255" placeholder="Razón social o nombre">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Estado pago documento</label>
                                        <select class="input-premium w-full" :name="'apuntes['+idx+'][factura_estado]'" x-model="line.factura_estado">
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Pagada">Pagada</option>
                                        </select>
                                    </div>
                                    <p x-show="line.tiene_documento" class="text-xs text-emerald-800 font-medium">Ya hay un documento archivado. Suba otro archivo para sustituirlo.</p>
                                </div>
                                <button
                                    type="button"
                                    class="text-xs text-red-600 hover:underline"
                                    @click="removeLine(idx)"
                                    x-show="lines.length > 2"
                                >Quitar línea</button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addLine()" class="btn-soft text-xs mb-4">+ Añadir línea</button>

                    <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200 mb-4 text-sm">
                        <div class="font-mono tabular-nums">
                            <span class="text-slate-600">Total debe:</span>
                            <strong class="text-emerald-800 ml-1" x-text="formatMoney(totalDebe())"></strong>
                            <span class="text-slate-400 mx-2">|</span>
                            <span class="text-slate-600">Total haber:</span>
                            <strong class="text-amber-800 ml-1" x-text="formatMoney(totalHaber())"></strong>
                        </div>
                        <span class="text-xs" :class="cuadrado() ? 'text-emerald-700 font-semibold' : 'text-amber-700 font-semibold'" x-text="cuadrado() ? 'Cuadrado' : 'Pendiente de cuadrar'"></span>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-soft" @click="$dispatch('close-modal', 'asiento-contable')">Cancelar</button>
                        <button
                            type="submit"
                            class="btn-accent disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!puedeGuardar()"
                        >
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>

</x-app-layout>
