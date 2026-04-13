<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.tpv') }}" class="btn-accent text-xs uppercase tracking-wider">TPV</a>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 space-y-8">
        <div>
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Informes de tienda</h1>
            <p class="text-sm text-slate-600 mt-1">Decisiones de mayordomía con datos reales de caja y márgenes.</p>
        </div>

        <div class="grid sm:grid-cols-1 gap-4">
            <a href="{{ route('tienda.informes.ranking') }}" class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)] hover:shadow-md transition rounded-xl">
                <h2 class="font-bold text-[color:var(--color-primary)]">Productos estrella</h2>
                <p class="text-sm text-slate-600 mt-2">Ranking de unidades vendidas por producto (rango de fechas).</p>
            </a>
            <a href="{{ route('tienda.informes.margenes') }}" class="card-premium p-6 border-t-2 border-t-slate-200 hover:border-[color:var(--color-accent)]/40 rounded-xl hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Beneficio estimado</h2>
                <p class="text-sm text-slate-600 mt-2">Base imponible de ventas menos coste snapshot por línea.</p>
            </a>
            <a href="{{ route('tienda.informes.stock-bajo') }}" class="card-premium p-6 border-t-2 border-t-slate-200 hover:border-[color:var(--color-accent)]/40 rounded-xl hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Reposición</h2>
                <p class="text-sm text-slate-600 mt-2">Productos activos en o por debajo del stock mínimo.</p>
            </a>
        </div>

        <a href="{{ route('tienda.panel') }}" class="text-sm text-slate-500 hover:text-[color:var(--color-primary)]">← Panel tienda</a>
    </div>
</x-app-layout>
