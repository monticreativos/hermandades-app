<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-[color:var(--color-primary)]">Cierre mensual — Arqueo caja y banco</h2>
    </x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">{{ session('error') }}</div>
        @endif

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <p class="text-sm text-slate-600 mb-4">
                Seleccione el mes cerrado. Los importes se calculan desde el libro diario (cuentas que comienzan por <span class="font-mono text-xs">570</span> y <span class="font-mono text-xs">572</span>).
                Puede generar el PDF para revisión o <strong>guardarlo en el historial</strong> (Fiscal / Censor).
            </p>
            <form method="GET" action="{{ route('economia.tesoreria.arqueo-mensual') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Año</label>
                    <input type="number" name="año" value="{{ $año }}" min="2000" max="2100" class="input-premium w-28" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Mes</label>
                    <select name="mes" class="input-premium">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected((int) $mes === $m)>{{ ucfirst(\Carbon\Carbon::createFromDate(2000, $m, 1)->locale('es')->isoFormat('MMMM')) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-soft text-xs uppercase tracking-wider">Actualizar vista</button>
            </form>
        </div>

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6 overflow-x-auto">
            <h3 class="text-base font-bold text-[color:var(--color-primary)] mb-4">Resumen — {{ $resumen['etiqueta_mes'] }}</h3>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 border-b border-slate-200">
                        <th class="py-2 pr-4">Cuenta</th>
                        <th class="py-2 pr-4">Saldo inicial</th>
                        <th class="py-2 pr-4">Gastos (Debe)</th>
                        <th class="py-2 pr-4">Ingresos (Haber)</th>
                        <th class="py-2">Saldo final</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($resumen['cuentas'] as $fila)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-4 font-mono text-xs">{{ $fila['cuenta']->codigo }} — {{ $fila['cuenta']->nombre }}</td>
                            <td class="py-2 pr-4 font-mono tabular-nums">{{ number_format($fila['saldo_inicial'], 2, ',', '.') }} €</td>
                            <td class="py-2 pr-4 font-mono tabular-nums text-rose-800">{{ number_format($fila['gastos'], 2, ',', '.') }} €</td>
                            <td class="py-2 pr-4 font-mono tabular-nums text-emerald-800">{{ number_format($fila['ingresos'], 2, ',', '.') }} €</td>
                            <td class="py-2 font-mono tabular-nums font-semibold">{{ number_format($fila['saldo_final'], 2, ',', '.') }} €</td>
                        </tr>
                    @endforeach
                    <tr class="font-bold bg-slate-50">
                        <td class="py-3 pr-4">Totales</td>
                        <td class="py-3 pr-4 font-mono tabular-nums">{{ number_format($resumen['totales']['saldo_inicial'], 2, ',', '.') }} €</td>
                        <td class="py-3 pr-4 font-mono tabular-nums text-rose-800">{{ number_format($resumen['totales']['gastos'], 2, ',', '.') }} €</td>
                        <td class="py-3 pr-4 font-mono tabular-nums text-emerald-800">{{ number_format($resumen['totales']['ingresos'], 2, ',', '.') }} €</td>
                        <td class="py-3 font-mono tabular-nums">{{ number_format($resumen['totales']['saldo_final'], 2, ',', '.') }} €</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('economia.tesoreria.arqueo-mensual.pdf') }}" target="_blank" class="inline">
                @csrf
                <input type="hidden" name="año" value="{{ $año }}" />
                <input type="hidden" name="mes" value="{{ $mes }}" />
                <input type="hidden" name="guardar_historial" value="0" />
                <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Descargar PDF</button>
            </form>
            <form method="POST" action="{{ route('economia.tesoreria.arqueo-mensual.pdf') }}" onsubmit="return confirm('¿Generar PDF y guardarlo en Historial de informes?');">
                @csrf
                <input type="hidden" name="año" value="{{ $año }}" />
                <input type="hidden" name="mes" value="{{ $mes }}" />
                <input type="hidden" name="guardar_historial" value="1" />
                <button type="submit" class="btn-soft text-xs uppercase tracking-wider border border-[color:var(--color-accent)]/50">Guardar en historial</button>
            </form>
            <a href="{{ route('economia.informes.historial') }}" class="btn-soft text-xs">Ver historial</a>
        </div>
    </div>
</x-app-layout>
