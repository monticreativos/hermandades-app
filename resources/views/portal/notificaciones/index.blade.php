@extends('layouts.portal')

@section('title', 'Notificaciones')

@section('content')
    <div class="space-y-8">
        <div class="portal-card">
            <h1 class="portal-heading">Avisos y mensajes</h1>
            <p class="portal-sub">Comunicados de la hermandad y respuestas a sus solicitudes de cambio de datos.</p>
        </div>

        <section>
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.15em] text-slate-500">
                <span class="h-1 w-6 rounded-full bg-[color:var(--color-accent)]"></span>
                Comunicados
            </h2>
            @if ($comunicados->isEmpty())
                <div class="portal-card-muted py-8">
                    <p class="text-slate-600">No hay comunicados dirigidos a usted.</p>
                </div>
            @else
                <ul class="space-y-3">
                    @foreach ($comunicados as $rec)
                        @php $av = $rec->aviso; @endphp
                        <li class="portal-card !p-4 {{ $rec->leido_en ? '' : 'ring-2 ring-[color:var(--color-accent)]/30 bg-amber-50/20' }}">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-bold text-[color:var(--color-primary)]">{{ $av->titulo }}</p>
                                @if (! $rec->leido_en)
                                    <span class="shrink-0 rounded-full bg-[color:var(--color-accent)]/20 px-2 py-0.5 text-[10px] font-bold uppercase text-[color:var(--color-primary)]">Nuevo</span>
                                @endif
                            </div>
                            <time class="mt-1 block text-[11px] text-slate-500">{{ $av->enviado_en?->format('d/m/Y H:i') }}</time>
                            <div class="mt-3 text-sm text-slate-700 whitespace-pre-wrap leading-relaxed">{{ $av->cuerpo }}</div>
                            @if (! $rec->leido_en)
                                <form method="POST" action="{{ route('portal.avisos-recibidos.leer', $rec) }}" class="mt-4">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline">Marcar como leído</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.15em] text-slate-500">
                <span class="h-1 w-6 rounded-full bg-slate-300"></span>
                Solicitudes de datos
            </h2>
            @if ($ultimasSolicitudes->isEmpty())
                <div class="portal-card-muted py-8">
                    <p class="text-slate-600">No hay resoluciones recientes de solicitudes de cambio.</p>
                </div>
            @else
                <ul class="space-y-3">
                    @foreach ($ultimasSolicitudes as $sol)
                        <li class="portal-card !p-4">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-bold uppercase text-slate-500">Cambio de datos</p>
                                <time class="text-[10px] text-slate-400">{{ $sol->updated_at->format('d/m/Y') }}</time>
                            </div>
                            @if ($sol->estado === \App\Models\SolicitudCambioDatos::ESTADO_APROBADA)
                                <p class="mt-2 text-sm font-semibold text-emerald-800">Aprobada — los datos han sido actualizados en la ficha.</p>
                            @else
                                <p class="mt-2 text-sm font-semibold text-rose-800">Rechazada</p>
                                @if ($sol->motivo_rechazo)
                                    <p class="mt-1 text-sm text-slate-700">{{ $sol->motivo_rechazo }}</p>
                                @endif
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
