@php
    /** @var \App\Models\ProductoTienda|null $producto */
    $esEdicion = isset($producto) && $producto->exists;
@endphp

<div class="grid sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nombre</label>
        <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre ?? '') }}" required maxlength="255" class="input-premium w-full" />
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Categoría</label>
        <select name="categoria" required class="input-premium w-full">
            @foreach ($categorias as $val => $label)
                <option value="{{ $val }}" @selected(old('categoria', $producto->categoria ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1">Gestione categorías en <a href="{{ route('tienda.productos.index') }}" class="underline">Catálogo</a>.</p>
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">SKU / Código barras</label>
        <input type="text" name="sku" value="{{ old('sku', $producto->sku ?? '') }}" maxlength="64" class="input-premium w-full font-mono text-sm" placeholder="Opcional" />
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Precio venta (TTC)</label>
        <input type="number" name="precio_venta" value="{{ old('precio_venta', $producto->precio_venta ?? '') }}" step="0.01" min="0" required class="input-premium w-full tabular-nums" />
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Precio coste</label>
        <input type="number" name="precio_coste" value="{{ old('precio_coste', $producto->precio_coste ?? '') }}" step="0.01" min="0" required class="input-premium w-full tabular-nums" />
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">IVA %</label>
        <input type="number" name="iva_porcentaje" value="{{ old('iva_porcentaje', $producto->iva_porcentaje ?? '21') }}" step="0.01" min="0" max="100" required class="input-premium w-full tabular-nums" />
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Stock actual</label>
        <input type="number" name="stock_actual" value="{{ old('stock_actual', $producto->stock_actual ?? 0) }}" min="0" required class="input-premium w-full tabular-nums" />
    </div>
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Stock mínimo (alerta)</label>
        <input type="number" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo ?? 0) }}" min="0" required class="input-premium w-full tabular-nums" />
    </div>
    <div class="sm:col-span-2" x-data="{drag:false,names:[]}">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Imágenes (galería)</label>
        <label
            class="block rounded-xl border-2 border-dashed p-6 text-center transition"
            :class="drag ? 'border-[color:var(--color-accent)] bg-amber-50/40' : 'border-slate-200 bg-slate-50/70'"
            @dragover.prevent="drag=true"
            @dragleave.prevent="drag=false"
            @drop.prevent="drag=false; $refs.input.files = $event.dataTransfer.files; names = [...$event.dataTransfer.files].map(f => f.name)"
        >
            <input
                x-ref="input"
                type="file"
                name="imagenes[]"
                accept="image/*"
                multiple
                class="hidden"
                @change="names = [...$event.target.files].map(f => f.name)"
            />
            <p class="text-sm font-semibold text-[color:var(--color-primary)]">Arrastra aquí las fotos o pulsa para seleccionar</p>
            <p class="text-xs text-slate-500 mt-1">Hasta 10 imágenes, máx. 4MB por archivo.</p>
            <button type="button" class="btn-soft text-xs mt-3 border border-slate-200 px-3 py-2 rounded-xl" @click="$refs.input.click()">Seleccionar archivos</button>
            <template x-if="names.length">
                <div class="mt-3 text-left max-h-28 overflow-auto text-xs text-slate-600 space-y-1">
                    <template x-for="name in names" :key="name">
                        <p>• <span x-text="name"></span></p>
                    </template>
                </div>
            </template>
        </label>
        @if ($esEdicion && $producto->imagenes()->exists())
            <p class="text-xs text-slate-500 mt-3">Imágenes actuales (marque para eliminar al guardar):</p>
            <div class="mt-2 grid grid-cols-3 sm:grid-cols-5 gap-2">
                @foreach ($producto->imagenes as $img)
                    <label class="relative block">
                        <img src="{{ $img->url() }}" alt="" class="h-20 w-full rounded-lg object-cover border border-slate-100" />
                        <span class="absolute top-1 left-1 rounded bg-white/90 px-1 text-[10px] text-slate-700">
                            <input type="checkbox" name="eliminar_imagenes[]" value="{{ $img->id }}" class="rounded border-slate-300 align-middle" />
                            borrar
                        </span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>
    <div class="sm:col-span-2 flex items-center gap-2">
        <input type="hidden" name="activo" value="0" />
        <input type="checkbox" name="activo" value="1" id="activo_tienda" @checked(old('activo', $producto->activo ?? true)) class="rounded border-slate-300" />
        <label for="activo_tienda" class="text-sm text-slate-700">Producto activo (visible en TPV y portal)</label>
    </div>
</div>
