<x-app-layout>
    <x-slot name="header"></x-slot>
    <div class="py-8 w-full px-2 sm:px-4 lg:px-6 max-w-5xl">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Auxiliar Impuesto de Sociedades (modelo 200)</h2>
            <p class="mt-1 text-sm text-slate-600">Clasificación orientativa de <strong>ingresos contables</strong> (partidas al haber en cuentas de ingreso) según el indicador <code class="text-xs bg-slate-100 px-1 rounded">renta_is_exenta</code> guardado en cada asiento. Los asientos creados con el asistente «Registrar movimiento» quedan clasificados automáticamente; el resto puede requerir revisión manual con su asesor.</p>
        </div>
        @include('economia.partials.subnav')

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="input-premium w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="input-premium w-full">
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Actualizar</button>
                    <a href="{{ route('economia.informes.is-auxiliar') }}" class="btn-soft text-xs">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="card-premium p-5 border-t-2 border-t-emerald-500/60">
                <p class="text-xs font-bold uppercase text-slate-500">Rentas exentas (indicador Sí)</p>
                <p class="mt-2 text-2xl font-bold text-emerald-900">{{ number_format($totalExenta, 2, ',', '.') }} €</p>
            </div>
            <div class="card-premium p-5 border-t-2 border-t-amber-500/60">
                <p class="text-xs font-bold uppercase text-slate-500">Rentas no exentas (actividad económica)</p>
                <p class="mt-2 text-2xl font-bold text-amber-950">{{ number_format($totalNoExenta, 2, ',', '.') }} €</p>
            </div>
            <div class="card-premium p-5 border-t-2 border-t-slate-400">
                <p class="text-xs font-bold uppercase text-slate-500">Sin clasificar (asientos anteriores)</p>
                <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($totalSinClasificar, 2, ',', '.') }} €</p>
            </div>
        </div>

        @if ($detalleNoExenta->isNotEmpty())
            <h3 class="text-sm font-bold uppercase text-slate-600 mb-3">Detalle ingresos no exentos</h3>
            <div class="card-premium overflow-x-auto mb-8">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Asiento</th>
                            <th class="px-3 py-2 text-left">Glosa</th>
                            <th class="px-3 py-2 text-right">Importe ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detalleNoExenta as $row)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">{{ $row['asiento']->fecha->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 font-mono">{{ $row['asiento']->numero_asiento }}</td>
                                <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($row['asiento']->glosa, 80) }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ number_format($row['importe'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($detalleSinClasificar->isNotEmpty())
            <h3 class="text-sm font-bold uppercase text-slate-600 mb-3">Revisar: ingresos en asientos sin indicador</h3>
            <div class="card-premium overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Asiento</th>
                            <th class="px-3 py-2 text-left">Glosa</th>
                            <th class="px-3 py-2 text-right">Importe ingreso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($detalleSinClasificar as $row)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">{{ $row['asiento']->fecha->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 font-mono">{{ $row['asiento']->numero_asiento }}</td>
                                <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($row['asiento']->glosa, 80) }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ number_format($row['importe'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
