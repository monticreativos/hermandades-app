<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.informes.index') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Informes</a>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Beneficio estimado</h1>
        <p class="text-sm text-slate-600">Suma de bases imponibles de líneas de venta menos coste unitario (snapshot al vender) por cantidad.</p>

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

        <div class="card-premium p-6 rounded-xl border border-slate-100 space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-slate-600">Ventas (base imponible)</span>
                <span class="font-bold tabular-nums text-[color:var(--color-primary)]">{{ number_format($ventasBase, 2, ',', '.') }} €</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-600">Coste de mercancías vendidas</span>
                <span class="font-bold tabular-nums text-slate-700">− {{ number_format($coste, 2, ',', '.') }} €</span>
            </div>
            <div class="border-t border-slate-100 pt-4 flex justify-between items-center">
                <span class="font-bold text-[color:var(--color-primary)]">Margen bruto estimado</span>
                <span class="text-2xl font-bold tabular-nums text-[color:var(--color-accent)]">{{ number_format($margen, 2, ',', '.') }} €</span>
            </div>
        </div>
    </div>
</x-app-layout>
