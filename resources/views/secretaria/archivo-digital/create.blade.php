<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.archivo-digital.index') }}" class="btn-soft text-xs uppercase tracking-wider">Volver al archivo</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-2xl space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Subir al archivo digital</h1>

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                    @foreach ($errors->all() as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('secretaria.archivo-digital.store') }}" enctype="multipart/form-data" class="card-premium p-6 sm:p-8 border-t-2 border-t-[color:var(--color-accent)] space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Título</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" class="input-premium w-full" required maxlength="255" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Categoría</label>
                    <select name="categoria" class="input-premium w-full" required>
                        @foreach ($categorias as $k => $label)
                            <option value="{{ $k }}" @selected(old('categoria') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Nivel de acceso</label>
                    <select name="nivel_acceso" class="input-premium w-full" required>
                        @foreach ($niveles as $k => $label)
                            <option value="{{ $k }}" @selected(old('nivel_acceso', \App\Models\DocumentoArchivo::NIVEL_PUBLICO_HERMANOS) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Descripción (opcional)</label>
                    <textarea name="descripcion" rows="3" class="input-premium w-full" maxlength="2000">{{ old('descripcion') }}</textarea>
                </div>
                <div x-data="{ over:false, nombre:'' }">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Archivo (máx. 50 MB)</label>
                    <div
                        class="rounded-xl border-2 border-dashed border-[color:var(--color-accent)] bg-slate-50 p-5 text-center cursor-pointer transition"
                        :class="over ? 'bg-amber-50' : ''"
                        @click="$refs.archivoInput.click()"
                        @dragover.prevent="over=true"
                        @dragleave.prevent="over=false"
                        @drop.prevent="over=false; $refs.archivoInput.files = $event.dataTransfer.files; nombre = $event.dataTransfer.files?.[0]?.name || ''"
                    >
                        <p class="font-semibold text-[color:var(--color-primary)]">Arrastra y suelta aquí tu documento</p>
                        <p class="text-xs text-slate-500 mt-1">PDF o imagen · También puedes tocar para cargar desde móvil</p>
                        <p x-show="nombre" x-cloak class="mt-2 text-xs text-emerald-700 font-semibold" x-text="'Archivo: ' + nombre"></p>
                    </div>
                    <input
                        x-ref="archivoInput"
                        type="file"
                        name="archivo"
                        required
                        class="hidden"
                        @change="nombre = $event.target.files?.[0]?.name || ''"
                    />
                </div>
                <button type="submit" class="btn-accent">Guardar en archivo</button>
            </form>
        </div>
    </div>
</x-app-layout>
