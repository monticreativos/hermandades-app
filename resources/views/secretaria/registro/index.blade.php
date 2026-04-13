<x-app-layout>
    <x-slot name="header">
        <span class="text-xs uppercase tracking-wider text-slate-500">Secretaría · Libro de registro</span>
    </x-slot>

    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.js-contacto-select').forEach((el) => {
                    if (!el.tomselect) new TomSelect(el, { create: false, maxItems: 1, placeholder: 'Buscar hermano o contacto externo...' });
                });
            });
        </script>
    @endpush

    <div class="py-8" x-data="{ openNuevoContacto:false }">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6">
            <h1 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Registro de entrada y salida</h1>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
            @endif

            <div x-data="{ over:false }" class="card-premium p-6 border-2 border-dashed border-[color:var(--color-accent)] text-center"
                @dragover.prevent="over=true" @dragleave.prevent="over=false" @drop.prevent="over=false; $refs.dropFile.files = $event.dataTransfer.files; $refs.dropForm.submit();"
                :class="over ? 'bg-amber-50' : ''">
                <p class="font-semibold text-[color:var(--color-primary)]">Arrastra aquí PDF/Imagen para registrar entrada automática</p>
                <p class="text-xs text-slate-500 mt-1">Se crea asiento de entrada con sello y protocolo automático.</p>
                <form x-ref="dropForm" action="{{ route('secretaria.registro.drop') }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input x-ref="dropFile" type="file" name="archivo" />
                </form>
            </div>

            <form x-data="{ overManual:false, nombreManual:'' }" method="POST" action="{{ route('secretaria.registro.store') }}" enctype="multipart/form-data" class="card-premium p-6 grid grid-cols-1 md:grid-cols-3 gap-4 border-t-2 border-t-[color:var(--color-accent)]">
                @csrf
                <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" class="input-premium" required />
                <select name="tipo_movimiento" class="input-premium" required>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                </select>
                <select name="tipo_documento" class="input-premium" required>
                    <option>Carta</option><option>Factura</option><option>Edicto</option><option>Invitacion</option>
                </select>
                <input type="text" name="remitente_destinatario" value="{{ old('remitente_destinatario') }}" placeholder="Texto libre opcional de clasificación" class="input-premium md:col-span-3" />
                <select name="remitente_hermano_id" class="input-premium js-contacto-select">
                    <option value="">Remitente: hermano</option>
                    @foreach($hermanos as $h)<option value="{{ $h->id }}">#{{ $h->numero_hermano }} {{ $h->apellidos }}, {{ $h->nombre }}</option>@endforeach
                </select>
                <select name="remitente_proveedor_id" class="input-premium js-contacto-select">
                    <option value="">Remitente: proveedor</option>
                    @foreach($proveedores as $p)<option value="{{ $p->id }}">{{ $p->razon_social }} @if($p->nif_cif) · {{ $p->nif_cif }} @endif</option>@endforeach
                </select>
                <select name="remitente_contacto_externo_id" class="input-premium js-contacto-select">
                    <option value="">Remitente: contacto externo</option>
                    @foreach($contactosExternos as $c)<option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->categoria }})</option>@endforeach
                </select>
                <select name="destinatario_hermano_id" class="input-premium js-contacto-select">
                    <option value="">Destinatario: hermano</option>
                    @foreach($hermanos as $h)<option value="{{ $h->id }}">#{{ $h->numero_hermano }} {{ $h->apellidos }}, {{ $h->nombre }}</option>@endforeach
                </select>
                <select name="destinatario_proveedor_id" class="input-premium js-contacto-select">
                    <option value="">Destinatario: proveedor</option>
                    @foreach($proveedores as $p)<option value="{{ $p->id }}">{{ $p->razon_social }} @if($p->nif_cif) · {{ $p->nif_cif }} @endif</option>@endforeach
                </select>
                <select name="destinatario_contacto_externo_id" class="input-premium js-contacto-select">
                    <option value="">Destinatario: contacto externo</option>
                    @foreach($contactosExternos as $c)<option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->categoria }})</option>@endforeach
                </select>
                <div class="md:col-span-3">
                    <button type="button" class="btn-soft text-xs" @click="openNuevoContacto=true">+ Crear contacto externo rápido</button>
                </div>
                <div class="md:col-span-1">
                    <div
                        class="rounded-xl border-2 border-dashed border-[color:var(--color-accent)] bg-slate-50 p-3 text-center cursor-pointer"
                        :class="overManual ? 'bg-amber-50' : ''"
                        @click="$refs.archivoManual.click()"
                        @dragover.prevent="overManual=true"
                        @dragleave.prevent="overManual=false"
                        @drop.prevent="overManual=false; $refs.archivoManual.files = $event.dataTransfer.files; nombreManual = $event.dataTransfer.files?.[0]?.name || ''"
                    >
                        <p class="text-xs font-semibold text-[color:var(--color-primary)]">Arrastra documento</p>
                        <p class="text-[11px] text-slate-500">o toca para cargar</p>
                        <p x-show="nombreManual" x-cloak class="mt-1 text-[11px] text-emerald-700" x-text="nombreManual"></p>
                    </div>
                    <input x-ref="archivoManual" type="file" name="archivo" class="hidden" required @change="nombreManual = $event.target.files?.[0]?.name || ''" />
                </div>
                <textarea name="extracto" rows="2" class="input-premium md:col-span-3" placeholder="Extracto del documento" required>{{ old('extracto') }}</textarea>
                <div class="md:col-span-3"><button class="btn-accent">Registrar documento</button></div>
            </form>

            <div class="card-premium overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Fecha</th><th class="px-4 py-3 text-left">Protocolo</th><th class="px-4 py-3 text-left">Tipo</th><th class="px-4 py-3 text-left">Remitente/Destinatario</th><th class="px-4 py-3 text-left">Extracto</th><th class="px-4 py-3 text-left">Archivo</th><th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $r)
                                <tr x-data="{ openEdit:false }" class="border-b border-slate-100">
                                    <td class="px-4 py-3">{{ $r->fecha?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 font-semibold text-[color:var(--color-primary)]">{{ $r->numero_protocolo }}</td>
                                    <td class="px-4 py-3">{{ ucfirst($r->tipo_movimiento) }} · {{ $r->tipo_documento }}</td>
                                    <td class="px-4 py-3">{{ $r->remitente_destinatario }}</td>
                                    <td class="px-4 py-3">{{ $r->extracto }}</td>
                                    <td class="px-4 py-3 text-xs text-slate-600">{{ $r->nombre_original }}</td>
                                    <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                        <a href="{{ route('secretaria.registro.ver', $r) }}" target="_blank" class="inline-flex h-8 px-2 items-center rounded-full border border-slate-200 text-slate-700 text-xs">Ver</a>
                                        <a href="{{ route('secretaria.registro.descargar', $r) }}" class="inline-flex h-8 px-2 items-center rounded-full border border-slate-200 text-slate-700 text-xs">Descargar</a>
                                        <button type="button" @click="openEdit = !openEdit" class="inline-flex h-8 px-2 items-center rounded-full border border-[color:var(--color-accent)] text-[color:var(--color-primary)] text-xs">Editar</button>
                                        <div x-show="openEdit" x-cloak class="mt-2 p-3 rounded-xl border border-slate-200 bg-white shadow w-[420px] ml-auto text-left">
                                            <form x-data="{ overEdit:false, nombreEdit:'' }" method="POST" action="{{ route('secretaria.registro.update', $r) }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="date" name="fecha" value="{{ $r->fecha?->format('Y-m-d') }}" class="input-premium" required />
                                                <select name="tipo_movimiento" class="input-premium" required>
                                                    <option value="entrada" @selected($r->tipo_movimiento === 'entrada')>Entrada</option>
                                                    <option value="salida" @selected($r->tipo_movimiento === 'salida')>Salida</option>
                                                </select>
                                                <select name="tipo_documento" class="input-premium" required>
                                                    <option value="Carta" @selected($r->tipo_documento === 'Carta')>Carta</option>
                                                    <option value="Factura" @selected($r->tipo_documento === 'Factura')>Factura</option>
                                                    <option value="Edicto" @selected($r->tipo_documento === 'Edicto')>Edicto</option>
                                                    <option value="Invitacion" @selected($r->tipo_documento === 'Invitacion')>Invitación</option>
                                                </select>
                                                <input type="text" name="remitente_destinatario" value="{{ $r->remitente_destinatario }}" class="input-premium" />
                                                <select name="remitente_hermano_id" class="input-premium">
                                                    <option value="">Remitente: hermano</option>
                                                    @foreach($hermanos as $h)<option value="{{ $h->id }}" @selected((int) $r->remitente_hermano_id === (int) $h->id)>#{{ $h->numero_hermano }} {{ $h->apellidos }}, {{ $h->nombre }}</option>@endforeach
                                                </select>
                                                <select name="remitente_proveedor_id" class="input-premium">
                                                    <option value="">Remitente: proveedor</option>
                                                    @foreach($proveedores as $p)<option value="{{ $p->id }}" @selected((int) $r->remitente_proveedor_id === (int) $p->id)>{{ $p->razon_social }}</option>@endforeach
                                                </select>
                                                <select name="remitente_contacto_externo_id" class="input-premium">
                                                    <option value="">Remitente: contacto externo</option>
                                                    @foreach($contactosExternos as $c)<option value="{{ $c->id }}" @selected((int) $r->remitente_contacto_externo_id === (int) $c->id)>{{ $c->nombre }} ({{ $c->categoria }})</option>@endforeach
                                                </select>
                                                <select name="destinatario_hermano_id" class="input-premium">
                                                    <option value="">Destinatario: hermano</option>
                                                    @foreach($hermanos as $h)<option value="{{ $h->id }}" @selected((int) $r->destinatario_hermano_id === (int) $h->id)>#{{ $h->numero_hermano }} {{ $h->apellidos }}, {{ $h->nombre }}</option>@endforeach
                                                </select>
                                                <select name="destinatario_proveedor_id" class="input-premium">
                                                    <option value="">Destinatario: proveedor</option>
                                                    @foreach($proveedores as $p)<option value="{{ $p->id }}" @selected((int) $r->destinatario_proveedor_id === (int) $p->id)>{{ $p->razon_social }}</option>@endforeach
                                                </select>
                                                <select name="destinatario_contacto_externo_id" class="input-premium">
                                                    <option value="">Destinatario: contacto externo</option>
                                                    @foreach($contactosExternos as $c)<option value="{{ $c->id }}" @selected((int) $r->destinatario_contacto_externo_id === (int) $c->id)>{{ $c->nombre }} ({{ $c->categoria }})</option>@endforeach
                                                </select>
                                                <textarea name="extracto" rows="2" class="input-premium" required>{{ $r->extracto }}</textarea>
                                                <label class="text-xs text-slate-500">Reemplazar archivo (opcional)</label>
                                                <div
                                                    class="rounded-xl border-2 border-dashed border-[color:var(--color-accent)] bg-slate-50 p-3 text-center cursor-pointer"
                                                    :class="overEdit ? 'bg-amber-50' : ''"
                                                    @click="$refs.archivoEdit.click()"
                                                    @dragover.prevent="overEdit=true"
                                                    @dragleave.prevent="overEdit=false"
                                                    @drop.prevent="overEdit=false; $refs.archivoEdit.files = $event.dataTransfer.files; nombreEdit = $event.dataTransfer.files?.[0]?.name || ''"
                                                >
                                                    <p class="text-[11px] font-semibold text-[color:var(--color-primary)]">Arrastra nuevo archivo</p>
                                                    <p class="text-[11px] text-slate-500">o toca para cargar</p>
                                                    <p x-show="nombreEdit" x-cloak class="mt-1 text-[11px] text-emerald-700" x-text="nombreEdit"></p>
                                                </div>
                                                <input x-ref="archivoEdit" type="file" name="archivo" class="hidden" @change="nombreEdit = $event.target.files?.[0]?.name || ''" />
                                                <div class="flex justify-end gap-2 pt-1">
                                                    <button type="button" @click="openEdit=false" class="btn-soft text-xs">Cancelar</button>
                                                    <button class="btn-accent text-xs">Guardar cambios</button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Sin registros aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $registros->links() }}</div>
            </div>
        </div>

        <div x-show="openNuevoContacto" x-cloak class="fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-[color:var(--color-primary)]">Nuevo contacto externo</h3>
                    <button type="button" class="btn-soft text-xs" @click="openNuevoContacto=false">Cerrar</button>
                </div>
                <form method="POST" action="{{ route('secretaria.directorio.quick') }}" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @csrf
                    <input name="nombre" class="input-premium" placeholder="Nombre" required>
                    <input name="entidad_institucion" class="input-premium" placeholder="Entidad">
                    <select name="categoria" class="input-premium">
                        <option>Hermandades</option><option>Ayuntamiento</option><option>Proveedores</option><option>Exaltadores</option><option>Prensa</option><option>Personalidades</option>
                    </select>
                    <input name="email" class="input-premium" placeholder="Email">
                    <div class="md:col-span-2"><button class="btn-accent text-xs">Crear (te devolverá al registro)</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
