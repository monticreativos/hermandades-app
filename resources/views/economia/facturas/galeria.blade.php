<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('economia.facturas.index') }}" class="btn-soft text-xs uppercase tracking-wider">Listado facturas</a>
    </x-slot>
    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Galería de adjuntos</h2>
            <p class="mt-1 text-sm text-slate-600">Facturas y tickets asociados a gastos (hasta 200 documentos más recientes del periodo).</p>
        </div>
        @include('economia.partials.subnav')

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input-premium w-full">
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Filtrar</button>
                    <a href="{{ route('economia.facturas.galeria') }}" class="btn-soft text-xs">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse ($documentos as $doc)
                @php
                    $mime = strtolower((string) $doc->mime_type);
                    $esImg = str_starts_with($mime, 'image/');
                @endphp
                <div class="card-premium overflow-hidden border-t-2 border-t-[color:var(--color-accent)]/40 flex flex-col">
                    <a href="{{ route('economia.documentos-gasto.ver', $doc) }}" target="_blank" rel="noopener" class="block aspect-[4/3] bg-slate-100 relative">
                        @if ($esImg)
                            <img src="{{ route('economia.documentos-gasto.ver', $doc) }}" alt="" class="w-full h-full object-cover" loading="lazy" />
                        @else
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 p-2 text-center">
                                <span class="text-3xl font-bold text-[color:var(--color-primary)]">PDF</span>
                                <span class="text-[10px] uppercase mt-1">Pulse para abrir</span>
                            </div>
                        @endif
                    </a>
                    <div class="p-3 text-xs flex-1 flex flex-col">
                        <p class="font-semibold text-[color:var(--color-primary)] line-clamp-2">{{ $doc->nombre_original }}</p>
                        <p class="text-slate-500 mt-1">{{ $doc->fecha_documento?->format('d/m/Y') }}</p>
                        <p class="text-slate-600 mt-0.5">{{ $doc->nombreProveedorMostrar() }}</p>
                        <p class="mt-auto pt-2 font-mono text-slate-800">{{ number_format((float) $doc->importe_linea, 2, ',', '.') }} €</p>
                        <a href="{{ route('economia.documentos-gasto.descargar', $doc) }}" class="mt-2 text-[color:var(--color-accent)] font-semibold hover:underline">Descargar</a>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-slate-500 text-sm py-12 text-center">No hay documentos con archivo en este periodo.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
