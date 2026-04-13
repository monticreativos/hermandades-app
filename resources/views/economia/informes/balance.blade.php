<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Balance de sumas y saldos</h2>
        </div>

        @include('economia.partials.subnav')

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input-premium w-full">
                </div>
                <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Actualizar</button>
                <a href="{{ route('economia.informes.balance') }}" class="btn-soft text-xs">Todo</a>
            </form>
        </div>

        <div class="hidden md:block card-premium overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Cuenta</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3 text-right">Sumas Debe</th>
                        <th class="px-4 py-3 text-right">Sumas Haber</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($filas as $r)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                            <td class="px-4 py-2 font-mono text-xs">{{ $r->codigo }}</td>
                            <td class="px-4 py-2 text-slate-800">{{ $r->nombre }}</td>
                            <td class="px-4 py-2 text-xs text-slate-500">{{ $r->tipo }}</td>
                            <td class="px-4 py-2 text-right font-mono tabular-nums">{{ number_format((float) $r->total_debe, 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-mono tabular-nums">{{ number_format((float) $r->total_haber, 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-mono tabular-nums font-medium">{{ number_format((float) $r->saldo, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100 font-semibold text-sm">
                    <tr>
                        <td colspan="3" class="px-4 py-3">Totales</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums">{{ number_format($sumDebe, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums">{{ number_format($sumHaber, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono tabular-nums">{{ number_format($sumDebe - $sumHaber, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="md:hidden space-y-2">
            @forelse ($filas as $r)
                <div class="card-premium p-4 border border-slate-200">
                    <div class="font-mono text-xs text-[color:var(--color-primary)]">{{ $r->codigo }}</div>
                    <div class="text-sm font-medium">{{ $r->nombre }}</div>
                    <div class="text-xs text-slate-500 mb-2">{{ $r->tipo }}</div>
                    <div class="flex justify-between text-sm font-mono tabular-nums">
                        <span>Debe {{ number_format((float) $r->total_debe, 2, ',', '.') }}</span>
                        <span>Haber {{ number_format((float) $r->total_haber, 2, ',', '.') }}</span>
                    </div>
                    <div class="mt-2 text-sm font-semibold">Saldo {{ number_format((float) $r->saldo, 2, ',', '.') }} €</div>
                </div>
            @empty
                <p class="text-sm text-slate-500 text-center py-6">Sin movimientos en el periodo.</p>
            @endforelse
            <div class="p-4 rounded-xl bg-slate-100 text-sm font-semibold">
                Total debe {{ number_format($sumDebe, 2, ',', '.') }} € · Total haber {{ number_format($sumHaber, 2, ',', '.') }} €
            </div>
        </div>
    </div>
</x-app-layout>
