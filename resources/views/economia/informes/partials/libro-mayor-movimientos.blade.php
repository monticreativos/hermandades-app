@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Apunte> $movimientos */
    /** @var \App\Models\CuentaContable|null $cuentaSel */
    $cuentaSel = $cuentaSel ?? null;
    $emptyHint = $emptyHint ?? 'Sin movimientos en el periodo.';
    $vistaContabilidad = $vistaContabilidad ?? false;
    $saldoPorVentana = $saldoPorVentana ?? false;
@endphp

@if ($cuentaSel)
    <div class="mb-3 text-sm text-slate-700">
        Cuenta <span class="font-mono font-semibold">{{ $cuentaSel->codigo }}</span> {{ $cuentaSel->nombre }}
    </div>
@endif

<div class="hidden md:block card-premium overflow-hidden border-t-2 border-t-[color:var(--color-accent)]">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
            @if ($vistaContabilidad)
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Concepto</th>
                    <th class="px-4 py-3 text-right">Debe</th>
                    <th class="px-4 py-3 text-right">Haber</th>
                    <th class="px-4 py-3 text-right">Saldo acumulado</th>
                </tr>
            @else
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Asiento</th>
                    <th class="px-4 py-3">Glosa</th>
                    <th class="px-4 py-3">Detalle</th>
                    <th class="px-4 py-3 text-right">Debe</th>
                    <th class="px-4 py-3 text-right">Haber</th>
                    <th class="px-4 py-3 text-right">Saldo</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @php $saldo = 0.0; @endphp
            @forelse ($movimientos as $m)
                @php
                    $usaVentana = $saldoPorVentana && $vistaContabilidad && array_key_exists('saldo_acumulado', $m->getAttributes());
                    if ($usaVentana) {
                        $saldoMostrar = (float) $m->saldo_acumulado;
                    } else {
                        $saldo += (float) $m->debe - (float) $m->haber;
                        $saldoMostrar = $saldo;
                    }
                    $conceptoFila = $vistaContabilidad
                        ? trim(($m->concepto_detalle ? $m->concepto_detalle.' — ' : '').$m->asiento->glosa)
                        : null;
                @endphp
                @if ($vistaContabilidad)
                    <tr class="border-b border-slate-100">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $m->asiento->fecha->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-slate-700 max-w-md" title="{{ $conceptoFila }}">{{ \Illuminate\Support\Str::limit($conceptoFila, 120) }}</td>
                        <td class="px-4 py-2 text-right font-mono tabular-nums">{{ $m->debe > 0 ? number_format($m->debe, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right font-mono tabular-nums">{{ $m->haber > 0 ? number_format($m->haber, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right font-mono tabular-nums font-semibold">{{ number_format($saldoMostrar, 2, ',', '.') }}</td>
                    </tr>
                @else
                    <tr class="border-b border-slate-100">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $m->asiento->fecha->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 font-mono text-xs">#{{ $m->asiento->numero_asiento }} / {{ $m->asiento->ejercicio->año }}</td>
                        <td class="px-4 py-2 text-slate-700 max-w-xs truncate" title="{{ $m->asiento->glosa }}">{{ $m->asiento->glosa }}</td>
                        <td class="px-4 py-2 text-slate-500 text-xs max-w-xs truncate">{{ $m->concepto_detalle ?: '—' }}</td>
                        <td class="px-4 py-2 text-right font-mono tabular-nums">{{ $m->debe > 0 ? number_format($m->debe, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right font-mono tabular-nums">{{ $m->haber > 0 ? number_format($m->haber, 2, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-right font-mono tabular-nums font-semibold">{{ number_format($saldoMostrar, 2, ',', '.') }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="{{ $vistaContabilidad ? 5 : 7 }}" class="px-4 py-8 text-center text-slate-500">{{ $emptyHint }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="md:hidden space-y-3">
    @php $saldoM = 0.0; @endphp
    @forelse ($movimientos as $m)
        @php
            $usaVentanaM = $saldoPorVentana && $vistaContabilidad && array_key_exists('saldo_acumulado', $m->getAttributes());
            if ($usaVentanaM) {
                $saldoMostrarM = (float) $m->saldo_acumulado;
            } else {
                $saldoM += (float) $m->debe - (float) $m->haber;
                $saldoMostrarM = $saldoM;
            }
            $conceptoM = $vistaContabilidad
                ? trim(($m->concepto_detalle ? $m->concepto_detalle.' — ' : '').$m->asiento->glosa)
                : null;
        @endphp
        <div class="card-premium p-4 border border-slate-200 border-t-2 border-t-[color:var(--color-accent)]">
            <div class="text-xs text-slate-500">{{ $m->asiento->fecha->format('d/m/Y') }}@if (! $vistaContabilidad) · Asiento #{{ $m->asiento->numero_asiento }}@endif</div>
            @if ($vistaContabilidad)
                <p class="text-sm font-medium text-slate-800 mt-1">{{ $conceptoM }}</p>
            @else
                <p class="text-sm font-medium text-slate-800 mt-1">{{ $m->asiento->glosa }}</p>
                @if ($m->concepto_detalle)
                    <p class="text-xs text-slate-500 mt-1">{{ $m->concepto_detalle }}</p>
                @endif
            @endif
            <div class="flex justify-between mt-2 font-mono text-sm tabular-nums">
                <span>D {{ $m->debe > 0 ? number_format($m->debe, 2, ',', '.') : '—' }}</span>
                <span>H {{ $m->haber > 0 ? number_format($m->haber, 2, ',', '.') : '—' }}</span>
            </div>
            <div class="mt-2 text-sm font-semibold text-[color:var(--color-primary)]">{{ $vistaContabilidad ? 'Saldo acumulado' : 'Saldo' }}: {{ number_format($saldoMostrarM, 2, ',', '.') }} €</div>
        </div>
    @empty
        <p class="text-sm text-slate-500 text-center py-8">{{ $emptyHint }}</p>
    @endforelse
</div>
