<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.informes.index') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Informes</a>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Productos estrella</h1>
        <p class="text-sm text-slate-600 mt-1">Ranking por unidades vendidas en el periodo.</p>

        <form method="get" class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)] flex flex-wrap gap-3 items-end rounded-xl">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="input-premium text-sm" />
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="input-premium text-sm" />
            </div>
            <button type="submit" class="btn-accent text-xs uppercase tracking-wider px-4 py-2">Actualizar</button>
        </form>

        <div class="hidden md:block card-premium rounded-xl overflow-hidden border border-slate-100">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3 text-right">Unidades</th>
                        <th class="px-4 py-3 text-right">Importe TTC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $i => $row)
                        <tr>
                            <td class="px-4 py-3 tabular-nums text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-[color:var(--color-primary)]">{{ $nombres[$row->producto_tienda_id] ?? 'Producto #'.$row->producto_tienda_id }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ (int) $row->unidades }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $row->importe_ttc, 2, ',', '.') }} €</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Sin ventas en el periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            @forelse ($rows as $i => $row)
                <article class="card-premium p-4 rounded-xl border border-slate-100">
                    <p class="text-xs text-slate-500">#{{ $i + 1 }}</p>
                    <p class="font-bold text-[color:var(--color-primary)]">{{ $nombres[$row->producto_tienda_id] ?? 'Producto #'.$row->producto_tienda_id }}</p>
                    <p class="text-sm mt-2"><span class="text-slate-500">Uds.</span> {{ (int) $row->unidades }}</p>
                    <p class="text-lg font-bold text-[color:var(--color-accent)] tabular-nums">{{ number_format((float) $row->importe_ttc, 2, ',', '.') }} €</p>
                </article>
            @empty
                <p class="text-center text-slate-500 py-6">Sin ventas en el periodo.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
