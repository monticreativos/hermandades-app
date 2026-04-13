<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Etiquetas postales y mailing</h2>
            <p class="text-sm text-slate-600 mt-1">Impresión en A4 (21 etiquetas por hoja, 3 columnas × 7 filas) y exportación CSV UTF-8</p>
        </div>

        @include('informes.partials.subnav')

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" action="{{ route('informes.etiquetas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Ámbito</label>
                    <select name="modo" class="input-premium w-full">
                        <option value="todos" @selected($modo === 'todos')>Todos los hermanos de alta</option>
                        <option value="cabezas" @selected($modo === 'cabezas')>Solo cabezas de familia (un envío por domicilio)</option>
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Código postal (opcional)</label>
                    <input type="text" name="codigo_postal" value="{{ $codigoPostal }}" maxlength="10" class="input-premium w-full" placeholder="Ej: 41007 o 41">
                </div>
                <div class="lg:col-span-6 flex flex-wrap gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Actualizar</button>
                    <a href="{{ route('informes.etiquetas.pdf', request()->query()) }}" target="_blank" rel="noopener" class="btn-soft text-xs">PDF etiquetas A4</a>
                    <a href="{{ route('informes.etiquetas.csv', request()->query()) }}" class="btn-soft text-xs">CSV mailing</a>
                </div>
            </form>
            <p class="text-xs text-slate-500 mt-4">Cabezas de familia: se agrupa por dirección + código postal + localidad; se toma el hermano con <strong>menor número de hermano</strong> como contacto postal de ese hogar.</p>
        </div>

        <p class="text-sm text-slate-600 mb-4"><span class="font-bold text-[color:var(--color-primary)]">{{ $total }}</span> etiquetas con los filtros actuales</p>

        <div class="card-premium overflow-hidden">
            <div class="hidden md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 text-left">N.º</th>
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-left">Dirección</th>
                            <th class="px-4 py-3 text-left">CP</th>
                            <th class="px-4 py-3 text-left">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($etiquetas as $h)
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-2 font-mono text-xs">{{ $h->numero_hermano }}</td>
                                <td class="px-4 py-2 font-medium">{{ $h->nombre }} {{ $h->apellidos }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $h->direccion ?: '—' }}</td>
                                <td class="px-4 py-2 font-mono text-xs">{{ $h->codigo_postal ?: '—' }}</td>
                                <td class="px-4 py-2 text-xs break-all">{{ $h->email ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="md:hidden divide-y divide-slate-100">
                @foreach ($etiquetas as $h)
                    <div class="px-4 py-3">
                        <div class="font-semibold">{{ $h->nombre }} {{ $h->apellidos }}</div>
                        <div class="text-xs text-slate-500">N.º {{ $h->numero_hermano }}</div>
                        <div class="text-sm text-slate-600 mt-1">{{ $h->direccion }} {{ $h->codigo_postal }} {{ $h->localidad }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
