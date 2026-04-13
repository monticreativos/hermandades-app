@extends('layouts.portal')

@section('title', 'Inicio')

@section('content')
    @php
        $antiguedad = $hermano->fecha_alta ? (int) $hermano->fecha_alta->diffInYears(now()) : null;
        $cuotaOk = ! in_array($hermano->estado_cuota, ['Pendiente', 'Impagada'], true);
        $saludo = now()->hour < 12 ? 'Buenos días' : (now()->hour < 20 ? 'Buenas tardes' : 'Buenas noches');
    @endphp

    <div class="space-y-5">
        {{-- Cabecera saludo --}}
        <section class="portal-card !overflow-hidden !p-0">
            <div class="bg-gradient-to-br from-[color:var(--color-primary)] to-slate-800 px-5 py-5 text-white">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[color:var(--color-accent)]">{{ $saludo }}</p>
                <h1 class="mt-1 text-xl font-bold leading-snug">{{ $hermano->nombre }}</h1>
                <p class="mt-0.5 text-sm text-slate-300">{{ $hermano->nombreCompleto() }}</p>
            </div>
            <div class="grid grid-cols-2 divide-x divide-slate-100 border-t border-slate-100 bg-white">
                <div class="p-4 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">N.º hermano</p>
                    <p class="mt-1 text-2xl font-bold tabular-nums text-[color:var(--color-primary)]">{{ $hermano->numero_hermano }}</p>
                </div>
                <div class="p-4 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Antigüedad</p>
                    <p class="mt-1 text-2xl font-bold text-[color:var(--color-primary)]">{{ $antiguedad !== null ? $antiguedad.' años' : '—' }}</p>
                </div>
            </div>
        </section>

        @php
            $badgeEstado = match ($hermano->estado) {
                'Alta' => 'badge-estado-alta',
                'Baja' => 'badge-estado-baja',
                default => 'badge-estado-difunto',
            };
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="{{ $badgeEstado }}">{{ $hermano->estado }}</span>
            @if (! empty($medallaAntiguedad))
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $medallaAntiguedad['color'] }}">{{ $medallaAntiguedad['titulo'] }}</span>
            @endif
            @if ($solicitudCambioPendiente)
                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">Cambio de datos en revisión</span>
            @endif
        </div>

        @if ($tablonAvisos->isNotEmpty())
            <section class="portal-card border-t-2 border-t-[color:var(--color-accent)]/60">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500">Tablón de anuncios</h2>
                    <a href="{{ route('portal.notificaciones.index') }}" class="text-[10px] font-bold uppercase text-[color:var(--color-accent)]">Ver todos</a>
                </div>
                <ul class="mt-3 space-y-3">
                    @foreach ($tablonAvisos as $ah)
                        @php $a = $ah->aviso; @endphp
                        @if ($a)
                            <li class="rounded-xl border border-slate-100 bg-slate-50/80 p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-bold text-[color:var(--color-primary)]">{{ $a->titulo }}</p>
                                    @if ($a->urgente)
                                        <span class="shrink-0 rounded-full bg-rose-100 px-2 py-0.5 text-[9px] font-bold uppercase text-rose-800">Urgente</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-1">{{ $a->enviado_en?->format('d/m/Y') ?? $a->created_at->format('d/m/Y') }}</p>
                                <p class="text-sm text-slate-700 mt-2 line-clamp-4">{{ \Illuminate\Support\Str::limit(strip_tags($a->cuerpo), 220) }}</p>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endif

        <a href="{{ route('portal.tienda.index') }}" class="block portal-card border-t-2 border-t-[color:var(--color-accent)] hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--color-accent)]">Tienda cofrade</p>
                    <h2 class="text-base font-bold text-[color:var(--color-primary)] mt-1">Click &amp; collect</h2>
                    <p class="text-sm text-slate-600 mt-1">Reserve artículos o pague por Bizum y recójalos en la Casa Hermandad.</p>
                </div>
                <span class="shrink-0 text-2xl text-[color:var(--color-accent)]" aria-hidden="true">›</span>
            </div>
        </a>

        <a href="{{ route('portal.cuadrilla.index') }}" class="block portal-card border-t-2 border-t-[color:var(--color-accent)]/70 hover:shadow-md transition">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-[color:var(--color-accent)]">Cuadrilla</p>
                    <h2 class="text-base font-bold text-[color:var(--color-primary)] mt-1">Mi trabajadera</h2>
                    <p class="text-sm text-slate-600 mt-1">Consulte ensayos, puesto asignado y cuadrante de relevos.</p>
                </div>
                <span class="shrink-0 text-2xl text-[color:var(--color-accent)]" aria-hidden="true">›</span>
            </div>
        </a>

        {{-- Cuota --}}
        <section class="overflow-hidden rounded-xl border shadow-sm {{ $cuotaOk ? 'border-emerald-200/90 bg-gradient-to-br from-emerald-50 via-white to-white' : 'border-rose-200/90 bg-gradient-to-br from-rose-50 via-white to-white' }}">
            <div class="flex items-center justify-between border-b border-black/5 px-4 py-3 {{ $cuotaOk ? 'bg-emerald-100/60' : 'bg-rose-100/60' }}">
                <p class="text-xs font-bold uppercase tracking-[0.15em] {{ $cuotaOk ? 'text-emerald-900' : 'text-rose-900' }}">Cuota ordinaria</p>
                <a href="{{ route('portal.pagos.index') }}" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline">Pagos</a>
            </div>
            <div class="p-4 sm:p-5">
                @if ($cuotaOk)
                    <p class="text-lg font-bold text-emerald-900">Al corriente</p>
                    <p class="mt-1 text-sm text-emerald-800/90">No consta cuota pendiente en el ejercicio registrado.</p>
                @else
                    @if ($hermano->estado_cuota === 'Impagada')
                        <p class="text-lg font-bold text-rose-900">Impagada / devolución</p>
                        <p class="mt-1 text-sm text-rose-800">Consulte en Pagos el historial de recibos y regularice por Bizum si procede.</p>
                    @else
                        <p class="text-lg font-bold text-rose-900">Pendiente</p>
                        @if ($hermano->cuotaPendienteEjercicio)
                            <p class="mt-1 text-sm text-rose-800">Ejercicio: <span class="font-semibold">{{ $hermano->cuotaPendienteEjercicio->año }}</span></p>
                        @endif
                    @endif
                    <a href="{{ route('portal.pagos.index') }}" class="portal-btn-primary mt-4">Ver opciones de pago</a>
                @endif
            </div>
        </section>

        @if ($proximoEvento)
            <section class="portal-card border-t-2 border-t-[color:var(--color-accent)]/70">
                <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500">Próximo evento</h2>
                <p class="mt-2 text-base font-bold text-[color:var(--color-primary)]">{{ $proximoEvento['titulo'] }}</p>
                <p class="text-sm text-slate-600">{{ $proximoEvento['fecha'] }}</p>
                <p class="mt-2 text-sm font-semibold text-[color:var(--color-accent)]">Cuenta atrás: {{ $proximoEvento['cuentaAtras'] }}</p>
            </section>
        @endif

        {{-- Estación de penitencia / papeleta --}}
        <section class="portal-card">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-[0.12em] text-slate-500">Estación de penitencia</h2>
                    @if ($campañaActiva)
                        <p class="mt-1 text-base font-bold text-[color:var(--color-primary)]">Salida {{ $campañaActiva->año }}</p>
                        @if ($campañaActiva->fecha_salida)
                            <p class="text-sm text-slate-600">Fecha: {{ $campañaActiva->fecha_salida->translatedFormat('l j \d\e F') }}</p>
                        @endif
                    @else
                        <p class="mt-1 text-sm text-slate-600">No hay campaña activa configurada.</p>
                    @endif
                </div>
                @if ($repartoAbierto)
                    <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-800">Reparto abierto</span>
                @endif
            </div>

            <div class="mt-4 space-y-3">
                @if ($papeletaCampaña)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">Su papeleta ({{ $papeletaCampaña->ejercicio?->año ?? $campañaActiva?->año }})</p>
                        <p class="mt-2 text-sm"><span class="font-semibold text-slate-700">Estado:</span> {{ $papeletaCampaña->estado }}</p>
                        @if ($papeletaCampaña->puesto)
                            <p class="text-sm"><span class="font-semibold text-slate-700">Puesto:</span> {{ $papeletaCampaña->puesto }}</p>
                        @endif
                        @if ($papeletaCampaña->estado === \App\Models\PapeletaSitio::ESTADO_EMITIDA)
                            <a href="{{ route('portal.papeletas.pdf', $papeletaCampaña) }}" target="_blank" rel="noopener" class="portal-btn-accent mt-4 inline-flex w-full sm:w-auto">
                                Descargar PDF de papeleta
                            </a>
                        @endif
                    </div>
                @elseif ($mostrarBotonPapeleta)
                    <p class="text-sm text-slate-600">Puede solicitar información sobre la papeleta de sitio durante el periodo de reparto.</p>
                    <a href="{{ route('portal.papeleta.info') }}" class="portal-btn-accent">Papeleta de sitio</a>
                @elseif ($repartoAbierto && ! $puedeAccionPapeleta)
                    <p class="text-sm text-rose-800">No puede solicitar papeleta: regularice cuota u otras deudas en <a href="{{ route('portal.pagos.index') }}" class="font-semibold underline">Pagos</a>.</p>
                @else
                    <p class="text-sm text-slate-600">Cuando abra el reparto de papeletas, podrá seguir los pasos desde aquí.</p>
                @endif
            </div>
        </section>

        {{-- Accesos rápidos --}}
        <section>
            <h2 class="mb-3 text-xs font-bold uppercase tracking-[0.15em] text-slate-500">Accesos</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ $papeletaCampaña ? route('portal.papeletas.pdf', $papeletaCampaña) : route('portal.papeleta.info') }}" class="portal-card !p-4 flex flex-col items-center text-center transition hover:border-[color:var(--color-accent)]/40 hover:shadow-md">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-[color:var(--color-primary)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                    <span class="mt-2 text-sm font-bold text-[color:var(--color-primary)]">Mi papeleta</span>
                    <span class="mt-0.5 text-[11px] text-slate-500">Descargar / consultar</span>
                </a>
                <a href="{{ route('portal.tienda.index') }}" class="portal-card !p-4 flex flex-col items-center text-center transition hover:border-[color:var(--color-accent)]/40 hover:shadow-md">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-[color:var(--color-primary)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974a1.125 1.125 0 011.119 1.257z"/></svg>
                    </span>
                    <span class="mt-2 text-sm font-bold text-[color:var(--color-primary)]">Tienda</span>
                    <span class="mt-0.5 text-[11px] text-slate-500">Reservas y compras</span>
                </a>
                <a href="{{ route('portal.perfil.index') }}" class="portal-card !p-4 flex flex-col items-center text-center transition hover:border-[color:var(--color-accent)]/40 hover:shadow-md">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-[color:var(--color-primary)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                    </span>
                    <span class="mt-2 text-sm font-bold text-[color:var(--color-primary)]">Mi perfil</span>
                    <span class="mt-0.5 text-[11px] text-slate-500">Datos y cambios</span>
                </a>
                <a href="{{ route('portal.pagos.index') }}" class="portal-card !p-4 flex flex-col items-center text-center transition hover:border-[color:var(--color-accent)]/40 hover:shadow-md">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-[color:var(--color-primary)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 15h19.5m-16.5-5.25h6.75m-6.75 5.25h6.75m-6.75-10.5h13.5a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25H5.25a2.25 2.25 0 01-2.25-2.25v-10.5a2.25 2.25 0 012.25-2.25z"/></svg>
                    </span>
                    <span class="mt-2 text-sm font-bold text-[color:var(--color-primary)]">Pagos</span>
                    <span class="mt-0.5 text-[11px] text-slate-500">Cuotas y avisos</span>
                </a>
                <a href="{{ route('portal.notificaciones.index') }}" class="portal-card !p-4 flex flex-col items-center text-center transition hover:border-[color:var(--color-accent)]/40 hover:shadow-md">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-[color:var(--color-primary)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.082A2.918 2.918 0 0118 14.75V11.25m0 0a3 3 0 10-6 0m6 0a3 3 0 10-6 0m6 0h.008v.008H18V11.25z"/></svg>
                    </span>
                    <span class="mt-2 text-sm font-bold text-[color:var(--color-primary)]">Avisos</span>
                    <span class="mt-0.5 text-[11px] text-slate-500">Comunicaciones</span>
                </a>
                <a href="{{ route('portal.documentos.index') }}" class="portal-card !p-4 flex flex-col items-center text-center transition hover:border-[color:var(--color-accent)]/40 hover:shadow-md">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-[color:var(--color-primary)]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                    <span class="mt-2 text-sm font-bold text-[color:var(--color-primary)]">Documentos</span>
                    <span class="mt-0.5 text-[11px] text-slate-500">PDF y certificados</span>
                </a>
            </div>
        </section>
    </div>
@endsection
