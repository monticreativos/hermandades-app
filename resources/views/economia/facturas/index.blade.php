<x-app-layout>
    <x-slot name="header"></x-slot>

    <div
        class="py-8 w-full px-2 sm:px-4 lg:px-6"
        x-data="facturasProveedoresPage(@js($pageConfig))"
    >
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Facturas</h2>
            <p class="text-sm text-slate-600 mt-1">Documentación digital de gastos vinculada al libro diario.</p>
        </div>

        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" action="{{ route('economia.facturas.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                    <div class="lg:col-span-4">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Proveedor</label>
                        <select
                            x-ref="proveedorSelect"
                            name="proveedor_id"
                            class="w-full"
                            placeholder="Buscar por razón social, nombre comercial o NIF/CIF…"
                        >
                            <option value="">Todos los proveedores</option>
                            @if ($proveedorSeleccionado)
                                <option value="{{ $proveedorSeleccionado->id }}" selected>
                                    {{ $proveedorSeleccionado->etiquetaListado() }}
                                </option>
                            @endif
                        </select>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" @click="abrirNuevo()" class="btn-soft text-xs">Nuevo proveedor</button>
                            <button type="button" @click="abrirEditar()" class="btn-soft text-xs">Editar seleccionado</button>
                            @if ($proveedorSeleccionado)
                                <a href="{{ route('economia.proveedores.extracto-contable', $proveedorSeleccionado) }}" class="btn-soft text-xs border border-[color:var(--color-accent)]/40">Libro mayor / Extracto</a>
                            @endif
                        </div>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700">Estado</label>
                        <select name="estado" class="input-premium w-full">
                            <option value="">Todos</option>
                            <option value="Pendiente" @selected(request('estado') === 'Pendiente')>Pendiente</option>
                            <option value="Pagada" @selected(request('estado') === 'Pagada')>Pagada</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700">Desde</label>
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input-premium w-full">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700">Hasta</label>
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input-premium w-full">
                    </div>
                    <div class="lg:col-span-2 flex gap-2">
                        <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Filtrar</button>
                        <a href="{{ route('economia.facturas.index') }}" class="btn-soft text-xs">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Modal proveedor --}}
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
            x-transition.opacity
            @keydown.escape.window="modalOpen = false"
        >
            <div class="absolute inset-0 bg-slate-900/50" @click="modalOpen = false"></div>
            <div
                class="relative bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                @click.stop
            >
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-start gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-[color:var(--color-primary)]" x-text="modalMode === 'create' ? 'Nuevo proveedor' : 'Editar proveedor'"></h3>
                        <p class="text-xs text-slate-500 mt-1">Datos fiscales y de contacto habituales en España (NIF/CIF/NIE, régimen de IVA, etc.).</p>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-slate-700 p-1" @click="modalOpen = false" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-4 overflow-y-auto space-y-4 text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Razón social / nombre completo <span class="text-red-600">*</span></label>
                            <input type="text" x-model="form.razon_social" class="input-premium w-full" maxlength="255">
                            <p class="text-xs text-red-600 mt-0.5" x-text="err('razon_social')"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Nombre comercial</label>
                            <input type="text" x-model="form.nombre_comercial" class="input-premium w-full" maxlength="255">
                            <p class="text-xs text-red-600 mt-0.5" x-text="err('nombre_comercial')"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Tipo <span class="text-red-600">*</span></label>
                            <select x-model="form.tipo_persona" class="input-premium w-full">
                                <option value="juridica">Persona jurídica</option>
                                <option value="autonomo">Autónomo / profesional</option>
                                <option value="fisica">Persona física</option>
                            </select>
                            <p class="text-xs text-red-600 mt-0.5" x-text="err('tipo_persona')"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">NIF / CIF / NIE</label>
                            <input type="text" x-model="form.nif_cif" class="input-premium w-full" maxlength="32" placeholder="Ej. B12345678">
                            <p class="text-xs text-red-600 mt-0.5" x-text="err('nif_cif')"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700">Régimen de IVA (proveedor)</label>
                            <select x-model="form.regimen_iva" class="input-premium w-full">
                                <option value="">— No indicado —</option>
                                <option value="general">Régimen general</option>
                                <option value="recargo_equivalencia">Recargo de equivalencia</option>
                                <option value="exento">Exento</option>
                                <option value="no_sujeto">No sujeto / inversión del sujeto pasivo</option>
                                <option value="intracomunitario">Intracomunitario</option>
                                <option value="otros">Otros</option>
                            </select>
                            <p class="text-xs text-red-600 mt-0.5" x-text="err('regimen_iva')"></p>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 pt-3">
                        <p class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Domicilio fiscal</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700">Dirección</label>
                                <input type="text" x-model="form.direccion" class="input-premium w-full">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('direccion')"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Código postal</label>
                                <input type="text" x-model="form.codigo_postal" class="input-premium w-full" maxlength="16">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('codigo_postal')"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Municipio</label>
                                <input type="text" x-model="form.municipio" class="input-premium w-full">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('municipio')"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Provincia</label>
                                <input type="text" x-model="form.provincia" class="input-premium w-full">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('provincia')"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">País (ISO-2)</label>
                                <input type="text" x-model="form.pais" class="input-premium w-full" maxlength="2" placeholder="ES">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('pais')"></p>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 pt-3">
                        <p class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Contacto y cobro</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Teléfono</label>
                                <input type="text" x-model="form.telefono" class="input-premium w-full">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('telefono')"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700">Email</label>
                                <input type="email" x-model="form.email" class="input-premium w-full">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('email')"></p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700">IBAN (domiciliación)</label>
                                <input type="text" x-model="form.iban" class="input-premium w-full font-mono text-xs" maxlength="34" placeholder="ES00 0000 0000 00 0000000000">
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('iban')"></p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700">Notas internas</label>
                                <textarea x-model="form.notas" rows="2" class="input-premium w-full"></textarea>
                                <p class="text-xs text-red-600 mt-0.5" x-text="err('notas')"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex flex-wrap justify-between gap-2 bg-slate-50/80">
                    <div>
                        <button
                            type="button"
                            x-show="modalMode === 'edit'"
                            x-cloak
                            @click="eliminarProveedor()"
                            class="text-xs font-semibold text-rose-700 hover:text-rose-900 px-3 py-2 rounded-xl border border-rose-200 bg-white"
                            :disabled="saving"
                        >
                            Eliminar
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="btn-soft text-xs" @click="modalOpen = false">Cancelar</button>
                        <button type="button" class="btn-accent text-xs" @click="guardarProveedor()" :disabled="saving" x-text="saving ? 'Guardando…' : 'Guardar'"></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="hidden md:block card-premium overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Fecha doc.</th>
                        <th class="px-4 py-3">Proveedor</th>
                        <th class="px-4 py-3">Asiento</th>
                        <th class="px-4 py-3">Importe</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentos as $doc)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                            <td class="px-4 py-2 whitespace-nowrap">{{ optional($doc->fecha_documento)->format('d/m/Y') ?: '—' }}</td>
                            <td class="px-4 py-2">{{ $doc->nombreProveedorMostrar() }}</td>
                            <td class="px-4 py-2 font-mono text-xs">
                                #{{ $doc->asiento->numero_asiento }} / {{ $doc->asiento->ejercicio->año }}
                                <span class="block text-slate-500 truncate max-w-xs">{{ \Illuminate\Support\Str::limit($doc->asiento->glosa, 48) }}</span>
                            </td>
                            <td class="px-4 py-2 font-mono tabular-nums">{{ $doc->importe_linea !== null ? number_format((float) $doc->importe_linea, 2, ',', '.') : '—' }}</td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('economia.documentos-gasto.estado', $doc) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado" onchange="this.form.submit()" class="input-premium text-xs py-1 min-w-[8rem]">
                                        <option value="Pendiente" @selected($doc->estado === 'Pendiente')>Pendiente</option>
                                        <option value="Pagada" @selected($doc->estado === 'Pagada')>Pagada</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">
                                <a href="{{ route('economia.documentos-gasto.descargar', $doc) }}" class="btn-soft text-xs">Descargar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">No hay documentos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            @forelse ($documentos as $doc)
                <div class="card-premium p-4 border border-slate-200">
                    <div class="text-xs text-slate-500">{{ optional($doc->fecha_documento)->format('d/m/Y') }}</div>
                    <div class="font-semibold text-[color:var(--color-primary)]">{{ $doc->nombreProveedorMostrar() }}</div>
                    <div class="text-xs font-mono mt-1">Asiento #{{ $doc->asiento->numero_asiento }} / {{ $doc->asiento->ejercicio->año }}</div>
                    <div class="text-sm text-slate-600 mt-1">{{ \Illuminate\Support\Str::limit($doc->asiento->glosa, 60) }}</div>
                    <div class="mt-2 font-mono text-sm">{{ $doc->importe_linea !== null ? number_format((float) $doc->importe_linea, 2, ',', '.').' €' : '—' }}</div>
                    <form method="POST" action="{{ route('economia.documentos-gasto.estado', $doc) }}" class="mt-2">
                        @csrf
                        @method('PATCH')
                        <label class="text-xs text-slate-600">Estado</label>
                        <select name="estado" onchange="this.form.submit()" class="input-premium w-full text-sm">
                            <option value="Pendiente" @selected($doc->estado === 'Pendiente')>Pendiente</option>
                            <option value="Pagada" @selected($doc->estado === 'Pagada')>Pagada</option>
                        </select>
                    </form>
                    <a href="{{ route('economia.documentos-gasto.descargar', $doc) }}" class="btn-soft text-xs mt-3 inline-block">Descargar</a>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-8">No hay documentos.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $documentos->links() }}
        </div>
    </div>
</x-app-layout>
