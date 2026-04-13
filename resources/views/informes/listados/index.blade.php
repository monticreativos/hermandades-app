<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-[color:var(--color-primary)] leading-tight">
            Listados a medida (Excel)
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6">
            @include('informes.partials.subnav')

            @if (session('error'))
                <div class="p-3 rounded-xl bg-orange-50 text-orange-900 text-sm border border-orange-200">
                    {{ session('error') }}
                </div>
            @endif

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4 sm:p-6">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-2">Generador de listados</h3>
                <p class="text-sm text-slate-600 mb-6">Marque las columnas que desea exportar y el filtro de estado. Se generará un archivo <strong>.xlsx</strong> listo para Excel o LibreOffice.</p>

                <form method="POST" action="{{ route('informes.listados.export') }}" class="space-y-6">
                    @csrf
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Estado del hermano</p>
                        <select name="estado" class="input-premium max-w-md">
                            <option value="todos" @selected(old('estado', 'Alta') === 'todos')>Todos</option>
                            <option value="Alta" @selected(old('estado', 'Alta') === 'Alta')>Alta</option>
                            <option value="Baja" @selected(old('estado') === 'Baja')>Baja</option>
                            <option value="Difunto" @selected(old('estado') === 'Difunto')>Difunto</option>
                        </select>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-3">Columnas a incluir</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @php($cols = [
                                'numero_hermano' => 'N.º hermano',
                                'nombre_completo' => 'Nombre y apellidos',
                                'telefono' => 'Teléfono',
                                'email' => 'Email',
                                'antiguedad' => 'Antigüedad (años)',
                                'dni' => 'DNI/NIE',
                                'codigo_postal' => 'Código postal',
                                'localidad' => 'Localidad',
                                'estado' => 'Estado',
                            ])
                            @foreach ($cols as $value => $label)
                                <label class="flex items-center gap-2 text-sm rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" name="columnas[]" value="{{ $value }}" class="rounded border-slate-300 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]"
                                        @checked(in_array($value, old('columnas', ['nombre_completo', 'telefono', 'email', 'antiguedad']), true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('columnas')
                            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="btn-accent">Descargar Excel</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
