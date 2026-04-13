@extends('layouts.portal')

@section('title', 'Mi Trabajadera')

@section('content')
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-bold text-[color:var(--color-primary)]">Mi trabajadera</h1>
            <p class="text-sm text-slate-600 mt-1">Su puesto, ensayos y cuadrante de relevo de la cuadrilla.</p>
        </div>

        @if (! $perfil)
            <section class="portal-card">
                <p class="text-sm text-slate-600">Actualmente no figura en una cuadrilla activa.</p>
            </section>
        @else
            <section class="portal-card border-t-2 border-t-[color:var(--color-accent)]">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">{{ $perfil->cuadrilla?->nombre }} · {{ strtoupper($perfil->cuadrilla?->paso ?? '') }}</h2>
                <p class="text-sm mt-2">Trabajadera <strong>{{ $perfil->trabajadera_numero ?? '—' }}</strong> · Palo <strong>{{ \App\Models\CostaleroPerfil::palos()[$perfil->palo] ?? '—' }}</strong></p>
                <p class="text-xs text-slate-500 mt-1">Altura {{ $perfil->altura_cm ?? '—' }} cm · Años en cuadrilla {{ $perfil->anios_cuadrilla }}</p>
                @if ($faltas >= 2)
                    <p class="mt-3 text-xs font-bold text-rose-700">Aviso: acumula {{ $faltas }} faltas a ensayos.</p>
                @endif
            </section>

            <section class="portal-card">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">Ensayos</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse($ensayos as $a)
                        <li class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <span>{{ $a->ensayo?->fecha?->format('d/m/Y') }} {{ $a->ensayo?->hora_inicio ? '· '.$a->ensayo?->hora_inicio : '' }}</span>
                            <span class="{{ $a->asistio ? 'text-emerald-700' : 'text-rose-700' }} font-semibold">{{ $a->asistio ? 'Asistió' : 'Falta' }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">Sin registros.</li>
                    @endforelse
                </ul>
            </section>

            @if ($relevoActual)
                <section class="portal-card">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">Cuadrante de relevo</h2>
                    <p class="text-sm mt-2">{{ $relevoActual->titulo }} · {{ $relevoActual->fecha_salida?->format('d/m/Y') }}</p>
                    <a href="{{ route('cuadrillas.relevos.pdf', [$perfil->cuadrilla_id, $relevoActual->id]) }}" target="_blank" class="portal-btn-accent mt-3">Ver PDF</a>
                </section>
            @endif

            <section class="portal-card">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">Avisos del capataz</h2>
                <ul class="mt-3 space-y-2">
                    @forelse($avisos as $av)
                        <li class="rounded-lg border border-slate-100 p-3">
                            <p class="font-semibold text-sm">{{ $av->titulo }}</p>
                            <p class="text-xs text-slate-600 mt-1">{{ $av->mensaje }}</p>
                            <p class="text-[11px] text-slate-500 mt-2">{{ $av->enviado_en?->format('d/m/Y H:i') }}</p>
                        </li>
                    @empty
                        <li class="text-slate-500 text-sm">Sin avisos.</li>
                    @endforelse
                </ul>
            </section>
        @endif
    </div>
@endsection
