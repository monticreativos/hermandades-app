<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.informes.index') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Informes</a>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Productos bajo mínimo</h1>

        <div class="hidden md:block card-premium rounded-xl overflow-hidden border border-slate-100">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Categoría</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-right">Mínimo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productos as $p)
                        <tr class="bg-amber-50/30">
                            <td class="px-4 py-3 font-medium text-[color:var(--color-primary)]">{{ $p->nombre }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->categoria }}</td>
                            <td class="px-4 py-3 text-right font-bold tabular-nums text-amber-900">{{ $p->stock_actual }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ $p->stock_minimo }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Todo en orden: no hay alertas de stock.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            @forelse ($productos as $p)
                <article class="card-premium p-4 rounded-xl border border-amber-200 bg-amber-50/20">
                    <p class="font-bold text-[color:var(--color-primary)]">{{ $p->nombre }}</p>
                    <p class="text-xs text-slate-600 mt-1">{{ $p->categoria }}</p>
                    <p class="mt-2 text-sm">Stock <span class="font-bold tabular-nums text-amber-900">{{ $p->stock_actual }}</span> · Mín. {{ $p->stock_minimo }}</p>
                </article>
            @empty
                <p class="text-center text-slate-500 py-6">Sin alertas.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
