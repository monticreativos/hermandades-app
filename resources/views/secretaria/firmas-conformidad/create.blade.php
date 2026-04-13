<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.firmas-conformidad.index') }}" class="btn-soft text-xs uppercase tracking-wider">Listado</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-2xl space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Nueva firma de conformidad</h1>

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                    @foreach ($errors->all() as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('secretaria.firmas-conformidad.store') }}" class="card-premium p-6 sm:p-8 border-t-2 border-t-[color:var(--color-accent)] space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Hermano</label>
                    <select name="hermano_id" class="input-premium w-full" required>
                        <option value="">— Elija —</option>
                        @foreach ($hermanos as $h)
                            <option value="{{ $h->id }}" @selected((string) old('hermano_id') === (string) $h->id)>N.º {{ $h->numero_hermano }} — {{ $h->nombre }} {{ $h->apellidos }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Título (ej. recepción de túnica)</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" class="input-premium w-full" required maxlength="255" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Descripción / condiciones</label>
                    <textarea name="descripcion" rows="6" class="input-premium w-full" required maxlength="10000">{{ old('descripcion') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Documento de referencia (opcional, solo públicos)</label>
                    <select name="documento_archivo_id" class="input-premium w-full">
                        <option value="">— Ninguno —</option>
                        @foreach ($documentos as $d)
                            <option value="{{ $d->id }}" @selected((string) old('documento_archivo_id') === (string) $d->id)>{{ $d->titulo }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-accent">Crear solicitud</button>
            </form>
        </div>
    </div>
</x-app-layout>
