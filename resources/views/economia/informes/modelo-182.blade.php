<x-app-layout>
    <x-slot name="header"></x-slot>
    <div class="py-8 w-full px-2 sm:px-4 lg:px-6 max-w-5xl">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Modelo 182 — resumen donativos</h2>
            <p class="mt-1 text-sm text-slate-600">Listado de movimientos marcados como <strong>aptos para desgravación</strong>, agrupados por beneficiario fiscal (unidad familiar) y ejercicio natural. El CSV es un <strong>auxiliar interno</strong>; la presentación oficial a la AEAT requiere el programa o servicio habilitado y revisión profesional.</p>
        </div>
        @include('economia.partials.subnav')

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6 flex flex-wrap items-end gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Ejercicio (año natural)</label>
                    <input type="number" name="año" value="{{ $año }}" min="2000" max="2100" class="input-premium w-32">
                </div>
                <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Ver</button>
            </form>
            <a href="{{ route('economia.informes.modelo-182.csv', ['año' => $año]) }}" class="btn-soft text-xs uppercase tracking-wider border border-[color:var(--color-accent)]/50">Descargar CSV</a>
        </div>

        <div class="card-premium overflow-x-auto border-t-2 border-t-[color:var(--color-accent)]">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-600">
                    <tr>
                        <th class="px-3 py-2 text-left">NIF</th>
                        <th class="px-3 py-2 text-left">Apellidos, nombre</th>
                        <th class="px-3 py-2 text-left">Municipio</th>
                        <th class="px-3 py-2 text-right">Importe total €</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agrupado as $fila)
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2 font-mono">{{ $fila['nif'] }}</td>
                            <td class="px-3 py-2">{{ $fila['apellidos'] }}, {{ $fila['nombre'] }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $fila['localidad'] }} ({{ $fila['cp'] }})</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format($fila['importe'], 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No hay donativos marcados para modelo 182 en este año.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
