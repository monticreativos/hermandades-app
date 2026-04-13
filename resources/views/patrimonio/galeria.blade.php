@php
    $badgePatrimonio = static function (string $estado): string {
        return match ($estado) {
            'Excelente' => 'badge-patrimonio-excelente',
            'Bueno' => 'badge-patrimonio-bueno',
            'Regular' => 'badge-patrimonio-regular',
            'Requiere intervención urgente', 'Necesita Restauración' => 'badge-patrimonio-restauracion',
            default => 'badge-patrimonio-regular',
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex gap-2">
            <a href="{{ route('patrimonio.index') }}" class="btn-soft border border-slate-200 text-xs">Vista gestión</a>
            <a href="{{ route('patrimonio.galeria') }}" class="btn-accent text-xs uppercase tracking-wider">Galería</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-[color:var(--color-primary)]">Galería de Patrimonio Artístico</h1>
            <p class="text-sm text-slate-600 mt-1">Catálogo museístico de enseres para conservación, seguros y Cabildo de Cuentas.</p>
        </div>

        <form method="get" class="card-premium p-5 border-t-2 border-t-[color:var(--color-accent)] grid md:grid-cols-4 gap-3">
            <input name="q" value="{{ request('q') }}" class="input-premium md:col-span-2" placeholder="Buscar por nombre, autor, técnica o nº inventario">
            <select name="categoria_id" class="input-premium">
                <option value="">Todas las categorías</option>
                @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('categoria_id') === (string) $cat->id)>{{ $cat->nombre }}</option>
                @endforeach
            </select>
            <select name="estado_conservacion_id" class="input-premium">
                <option value="">Todos los estados</option>
                @foreach($estadosConservacion as $est)
                    <option value="{{ $est->id }}" @selected((string) request('estado_conservacion_id') === (string) $est->id)>{{ $est->nombre }}</option>
                @endforeach
            </select>
            <select name="tipo_ubicacion" class="input-premium">
                <option value="">Todas las ubicaciones</option>
                @foreach($tiposUbicacion as $u)
                    <option value="{{ $u }}" @selected(request('tipo_ubicacion') === $u)>{{ $u }}</option>
                @endforeach
            </select>
            <div class="md:col-span-4 flex gap-2">
                <button class="btn-accent text-xs uppercase tracking-wider px-4 py-2">Filtrar</button>
                <a href="{{ route('patrimonio.galeria') }}" class="btn-soft border border-slate-200 px-4 py-2 rounded-xl text-sm">Limpiar</a>
            </div>
        </form>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($enseres as $e)
                @php
                    $foto = $e->fotos->first();
                    $cover = $foto?->url() ?? $e->urlImagenPrincipal();
                @endphp
                <article class="card-premium overflow-hidden border border-[color:var(--color-accent)]/30 shadow-sm hover:shadow-md transition">
                    <div class="aspect-[4/3] bg-slate-100 overflow-hidden">
                        @if($cover)
                            <img src="{{ $cover }}" alt="{{ $e->nombre }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm">Sin imagen</div>
                        @endif
                    </div>
                    <div class="p-4 space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <h2 class="font-bold text-[color:var(--color-primary)] leading-tight">{{ $e->nombre }}</h2>
                            <span class="{{ $badgePatrimonio($e->estadoConservacionPatrimonio?->nombre ?? '') }} whitespace-nowrap">{{ $e->estadoConservacionPatrimonio?->nombre ?? '—' }}</span>
                        </div>
                        <p class="text-xs text-slate-500">
                            {{ $e->categoriaPatrimonio?->nombre ?? 'Sin categoría' }}
                            @if($e->autor) · {{ $e->autor }} @endif
                            @if($e->año_creacion) · {{ $e->año_creacion }} @endif
                        </p>
                        <p class="text-sm text-slate-700">{{ $e->material_tecnica ?: ($e->materiales ?: 'Material/técnica no indicada') }}</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-lg bg-slate-50 border border-slate-100 px-2 py-1.5">
                                <p class="text-slate-500">Ubicación</p>
                                <p class="font-semibold text-slate-800">{{ $e->tipo_ubicacion ?: '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 border border-slate-100 px-2 py-1.5">
                                <p class="text-slate-500">Valoración</p>
                                <p class="font-semibold text-slate-800">{{ $e->valor_estimado ? number_format((float) $e->valor_estimado, 2, ',', '.').' €' : '—' }}</p>
                            </div>
                        </div>
                        <div class="pt-2 flex items-center justify-between">
                            <span class="font-mono text-[11px] text-slate-500">{{ $e->numero_inventario ?: 'INV pendiente' }}</span>
                            <a href="{{ route('patrimonio.show', $e) }}" class="text-xs font-bold text-[color:var(--color-accent)] hover:underline">Abrir ficha</a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-slate-500">No hay enseres con estos filtros.</p>
            @endforelse
        </div>

        <div>{{ $enseres->links() }}</div>
    </div>
</x-app-layout>
