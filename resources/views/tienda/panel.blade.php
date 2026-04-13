<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.tpv') }}" class="btn-accent text-xs uppercase tracking-wider">Abrir TPV</a>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 max-w-4xl mx-auto space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Tienda y ventas</h1>
        <p class="text-sm text-slate-600">TPV táctil integrado con contabilidad (570/572, 700, 477).</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('tienda.tpv') }}" class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)] hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Terminal punto de venta</h2>
                <p class="text-sm text-slate-600 mt-2">Cobro rápido, ticket térmico PDF y Bizum / tarjeta / efectivo.</p>
            </a>
            <a href="{{ route('tienda.productos.index') }}" class="card-premium p-6 border-t-2 border-t-slate-200 hover:border-[color:var(--color-accent)]/50 hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Catálogo e inventario</h2>
                <p class="text-sm text-slate-600 mt-2">Productos, fotos, SKU y stock.</p>
            </a>
            <a href="{{ route('tienda.ventas-dia.index') }}" class="card-premium p-6 border-t-2 border-t-slate-200 hover:border-[color:var(--color-accent)]/50 hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Ventas del día</h2>
                <p class="text-sm text-slate-600 mt-2">Por cajero, método de pago y reimpresión de tickets.</p>
            </a>
            <a href="{{ route('tienda.apertura-caja.create') }}" class="card-premium p-6 border-t-2 border-t-slate-200 hover:border-[color:var(--color-accent)]/50 hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Apertura de caja</h2>
                <p class="text-sm text-slate-600 mt-2">Saldo inicial en efectivo al abrir el TPV.</p>
            </a>
            <a href="{{ route('tienda.cierre-caja.create') }}" class="card-premium p-6 border-t-2 border-t-slate-200 hover:border-[color:var(--color-accent)]/50 hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Cierre y descuadre</h2>
                <p class="text-sm text-slate-600 mt-2">Esperado vs conteo físico del mayordomo.</p>
            </a>
            <a href="{{ route('tienda.informes.index') }}" class="card-premium p-6 border-t-2 border-t-slate-200 hover:border-[color:var(--color-accent)]/50 hover:shadow-md transition">
                <h2 class="font-bold text-[color:var(--color-primary)]">Informes</h2>
                <p class="text-sm text-slate-600 mt-2">Productos estrella, beneficio y stock mínimo.</p>
            </a>
        </div>
    </div>
</x-app-layout>
