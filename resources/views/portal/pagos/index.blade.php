@extends('layouts.portal')

@section('title', 'Pagos')

@php
    use App\Models\RemesaRecibo;
@endphp

@section('content')
    <div class="space-y-5">
        <div class="portal-card">
            <h1 class="portal-heading">Pagos y cuotas</h1>
            <p class="portal-sub">Consulte su situación. Puede simular un abono de cuota con Bizum (registro contable automático en bancos). La pasarela Stripe llegará en una actualización posterior.</p>
        </div>

        <section class="overflow-hidden rounded-xl border shadow-sm {{ $hermano->tieneCuotaOrdinariaPendiente() ? 'border-rose-200 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/50' }}">
            <div class="border-b border-black/5 px-4 py-3 {{ $hermano->tieneCuotaOrdinariaPendiente() ? 'bg-rose-100/70' : 'bg-emerald-100/70' }}">
                <h2 class="text-xs font-bold uppercase tracking-[0.15em] {{ $hermano->tieneCuotaOrdinariaPendiente() ? 'text-rose-900' : 'text-emerald-900' }}">Cuota ordinaria</h2>
            </div>
            <div class="p-4 sm:p-5">
                @if ($hermano->tieneCuotaOrdinariaPendiente())
                    @if ($hermano->estado_cuota === 'Impagada')
                        <p class="font-bold text-rose-900">Impagada / recibo devuelto</p>
                        <p class="mt-1 text-sm text-rose-800">Revise abajo el historial de domiciliaciones: el periodo marcado como devuelto debe regularizarse.</p>
                    @else
                        <p class="font-bold text-rose-900">Pendiente</p>
                        @if ($hermano->cuotaPendienteEjercicio)
                            <p class="mt-1 text-sm text-rose-800">Ejercicio {{ $hermano->cuotaPendienteEjercicio->año }}</p>
                        @endif
                    @endif
                    <p class="mt-3 text-sm text-slate-700">Tras realizar el Bizum a la cuenta indicada por la hermandad, confirme aquí el importe para dejar constancia en contabilidad (simulación; no sustituye al cobro real).</p>
                    <form method="POST" action="{{ route('portal.pagos.bizum.cuota') }}" class="mt-4 space-y-3 rounded-xl border border-[color:var(--color-accent)]/35 bg-white/90 p-4">
                        @csrf
                        <div>
                            <label for="importe_bizum" class="block text-xs font-bold uppercase text-slate-600 mb-1">Importe abonado (€)</label>
                            <input id="importe_bizum" name="importe" type="number" step="0.01" min="0.01" required class="portal-input" inputmode="decimal" placeholder="Ej. 50,00" />
                            @error('importe')
                                <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                            @enderror
                        </div>
                        <label class="flex items-start gap-2 text-xs text-slate-600">
                            <input type="checkbox" name="confirmar" value="1" required class="mt-0.5 rounded border-slate-300 text-[color:var(--color-accent)]" />
                            <span>Confirmo que he enviado el Bizum por el importe indicado (entorno de pruebas / simulación).</span>
                        </label>
                        @error('confirmar')
                            <p class="text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="portal-btn-accent w-full">Registrar pago en mi cuenta</button>
                    </form>
                    <p class="mt-3 text-[11px] text-slate-500 leading-relaxed">El sistema genera un asiento Debe 572 / Haber 431 con su número de hermano, igual que en «Registrar movimiento» del panel de economía.</p>
                @else
                    <p class="font-bold text-emerald-900">Al corriente</p>
                    <p class="mt-1 text-sm text-emerald-800">No consta cuota ordinaria pendiente.</p>
                @endif
            </div>
        </section>

        @if ($historialRecibos->isNotEmpty())
            <section class="portal-card">
                <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-slate-600">Historial de recibos (domiciliación)</h2>
                <p class="mt-2 text-sm text-slate-600">Estado de cada cargo enviado al banco. Si un recibo figura como devuelto, puede abonarlo por Bizum arriba e indicar el importe acordado con tesorería.</p>
                <div class="mt-4 space-y-3">
                    @foreach ($historialRecibos as $rec)
                        @php
                            $estadoLabel = match ($rec->estado) {
                                RemesaRecibo::ESTADO_COBRADO => ['Cobrado', 'text-emerald-800 bg-emerald-50 border-emerald-200'],
                                RemesaRecibo::ESTADO_DEVUELTO => ['Devuelto', 'text-rose-900 bg-rose-50 border-rose-200'],
                                default => ['Pendiente banco', 'text-slate-700 bg-slate-50 border-slate-200'],
                            };
                            [$etiquetaEstado, $clasesEstado] = $estadoLabel;
                        @endphp
                        <div class="rounded-xl border {{ $clasesEstado }} px-4 py-3">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-mono text-slate-600">{{ $rec->remesa?->etiqueta_periodo ?? 'Remesa' }} · {{ $rec->periodo_clave }}</p>
                                    <p class="mt-1 font-mono font-bold text-lg">{{ number_format((float) $rec->importe, 2, ',', '.') }} €</p>
                                    @if ($rec->estado === RemesaRecibo::ESTADO_DEVUELTO && $rec->motivo_devolucion)
                                        <p class="mt-2 text-xs text-rose-900/90">{{ $rec->motivo_devolucion }}</p>
                                    @endif
                                </div>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide border border-black/10 bg-white/80 text-[color:var(--color-primary)]">{{ $etiquetaEstado }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($deudaLoteria > 0)
            <section class="portal-card border-amber-200 bg-amber-50/30">
                <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-amber-900">Lotería</h2>
                <p class="mt-2 text-lg font-bold text-amber-950">{{ number_format($deudaLoteria, 2, ',', '.') }} €</p>
                <p class="mt-1 text-sm text-amber-900/90">Pendiente de abono en secretaría o por los canales habituales de la hermandad.</p>
            </section>
        @endif

        <p class="text-center text-xs text-slate-500 px-2">
            Para dudas, contacte con secretaría. Los movimientos contables oficiales figuran en el panel administrativo.
        </p>
    </div>
@endsection
