<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.firmas-conformidad.index') }}" class="btn-soft text-xs uppercase tracking-wider">Volver</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-2xl space-y-5">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">{{ $solicitud->titulo }}</h1>
            <p class="text-sm text-slate-600">Hermano N.º {{ $solicitud->hermano?->numero_hermano }} — {{ $solicitud->hermano?->nombreCompleto() }}</p>

            <div class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)] space-y-3">
                <p class="text-xs font-bold uppercase text-slate-500">Estado</p>
                @if ($solicitud->estado === \App\Models\FirmaConformidadSolicitud::ESTADO_FIRMADO)
                    <p class="text-emerald-800 font-semibold">Firmado el {{ $solicitud->firmado_en?->format('d/m/Y H:i') }} · IP {{ $solicitud->firmado_ip ?? '—' }}</p>
                @else
                    <p class="text-amber-800 font-semibold">Pendiente en el portal del hermano</p>
                @endif
            </div>

            <section class="card-premium p-6 border border-slate-200">
                <h2 class="text-sm font-bold uppercase text-slate-500 mb-2">Texto presentado al hermano</h2>
                <div class="text-sm text-slate-700 whitespace-pre-wrap">{{ $solicitud->descripcion }}</div>
            </section>

            @if ($solicitud->documentoArchivo)
                <p class="text-sm">
                    <a href="{{ route('secretaria.archivo-digital.descargar', $solicitud->documentoArchivo) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">Descargar documento adjunto</a>
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
