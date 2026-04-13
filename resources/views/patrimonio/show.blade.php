@php
    use Illuminate\Support\Facades\Storage;

    $img = $enser->imagen_principal_path;
    $imgUrl = $img ? (str_starts_with($img, 'http') ? $img : Storage::url($img)) : null;

    $estadoNombre = $enser->estadoConservacionPatrimonio?->nombre ?? '';
    $badgePatrimonio = match ($estadoNombre) {
        'Excelente' => 'badge-patrimonio-excelente',
        'Bueno' => 'badge-patrimonio-bueno',
        'Regular' => 'badge-patrimonio-regular',
        'Necesita Restauración' => 'badge-patrimonio-restauracion',
        'En Restauración' => 'badge-patrimonio-en-restauracion',
        default => 'badge-patrimonio-regular',
    };

    $iniciales = collect(explode(' ', trim($enser->nombre)))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('') ?: 'E';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-bold text-xl text-[color:var(--color-primary)] leading-tight truncate">
                Ficha de patrimonio
            </h2>
            <a href="{{ route('patrimonio.index') }}" class="btn-soft shrink-0">Volver</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            @if ($estadoNombre === 'Necesita Restauración')
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 flex items-start gap-3">
                    <span class="shrink-0 mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-200 text-rose-900 font-bold text-xs">!</span>
                    <div>
                        <p class="font-bold">Atención: necesita restauración</p>
                        <p class="mt-1 text-rose-800/90">Este enser figura como pendiente de intervención. Conviene priorizar valoración técnica y documentar el estado antes de traslados o exposición.</p>
                    </div>
                </div>
            @endif

            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">{{ $enser->nombre }}</h2>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="shrink-0">
                        @if ($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $enser->nombre }}" class="w-full max-w-xs rounded-xl border border-slate-200 object-cover shadow-sm aspect-square lg:aspect-auto lg:h-64 lg:w-64">
                        @else
                            <div class="w-full max-w-xs aspect-square lg:h-64 lg:w-64 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-3xl font-bold text-slate-500">
                                {{ $iniciales }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $badgePatrimonio }}">{{ $estadoNombre ?: '—' }}</span>
                            @if ($enser->categoriaPatrimonio)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">{{ $enser->categoriaPatrimonio->nombre }}</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500">Ubicación</p>
                                <p class="mt-1 text-slate-900 font-medium">{{ $enser->ubicacion ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500">Autor</p>
                                <p class="mt-1 text-slate-800">{{ $enser->autor ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500">Año creación</p>
                                <p class="mt-1 text-slate-800">{{ $enser->año_creacion ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500">Última revisión</p>
                                <p class="mt-1 text-slate-800">{{ optional($enser->ultima_revision)->format('d/m/Y') ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500">Valor estimado</p>
                                <p class="mt-1 text-slate-800">{{ $enser->valor_estimado !== null ? number_format((float) $enser->valor_estimado, 2, ',', '.').' €' : '—' }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs uppercase font-semibold text-slate-500">Materiales</p>
                                <p class="mt-1 text-slate-800">{{ $enser->materiales ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @if ($enser->descripcion_detallada)
                <section class="card-premium p-6">
                    <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-3">Descripción detallada</h3>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $enser->descripcion_detallada }}</p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
