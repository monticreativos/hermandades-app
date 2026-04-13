<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <h2 class="font-bold text-xl text-[color:var(--color-primary)]">Dashboard de Mayordomía</h2>
            <a href="{{ route('economia.movimiento-rapido.create') }}" class="btn-accent text-xs uppercase tracking-wider">Nuevo movimiento</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 space-y-6">
        <form method="get" class="flex items-end gap-2">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Ejercicio</label>
                <select name="ejercicio" class="input-premium">
                    @foreach($ejercicios as $e)
                        <option value="{{ $e }}" @selected((int)$e === (int)$ejercicioActual)>{{ $e }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-accent text-xs uppercase tracking-wider px-4 py-2">Aplicar</button>
        </form>

        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="card-premium p-5 border-t-2 border-t-[color:var(--color-accent)]"><p class="text-xs uppercase text-slate-500">Saldo Caja (570)</p><p class="text-3xl font-bold text-[color:var(--color-primary)]">{{ number_format($saldo570,2,',','.') }} €</p></div>
            <div class="card-premium p-5 border-t-2 border-t-slate-200"><p class="text-xs uppercase text-slate-500">Saldo Banco (572)</p><p class="text-3xl font-bold text-[color:var(--color-primary)]">{{ number_format($saldo572,2,',','.') }} €</p></div>
            <div class="card-premium p-5 border-t-2 border-t-slate-200"><p class="text-xs uppercase text-slate-500">Ventas TPV semana</p><p class="text-3xl font-bold text-[color:var(--color-primary)]">{{ number_format($ventasSemana,2,',','.') }} €</p></div>
            <div class="card-premium p-5 border-t-2 border-t-slate-200"><p class="text-xs uppercase text-slate-500">Top Ventas</p><p class="text-base font-bold text-[color:var(--color-primary)] mt-1">{{ $topVentas['nombre'] ?? '—' }}</p><p class="text-xs text-slate-500">{{ $topVentas ? $topVentas['uds'].' uds' : 'Sin ventas' }}</p></div>
        </div>

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-5">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="font-bold text-[color:var(--color-primary)]">Operativa rápida</h3>
                <span class="text-xs text-slate-500">Accesos directos de trabajo diario</span>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <a href="{{ route('economia.libro-diario.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Contabilidad</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Libro diario</p>
                </a>
                <a href="{{ route('economia.movimiento-rapido.create') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Caja/Banco</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Registrar cobro/pago</p>
                </a>
                <a href="{{ route('economia.facturas.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Gastos</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Facturas y documentos</p>
                </a>
                <a href="{{ route('economia.remesas.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Cuotas</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Remesas SEPA</p>
                </a>
                <a href="{{ route('economia.cuotas.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Seguimiento</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Estado de cuotas</p>
                </a>
                <a href="{{ route('economia.tesoreria.arqueo-mensual') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Tesorería</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Arqueo mensual</p>
                </a>
                <a href="{{ route('economia.informes.balance') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Informes</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Balance</p>
                </a>
                <a href="{{ route('tienda.ventas-dia.index') }}" class="rounded-xl border border-slate-200 px-4 py-3 hover:border-[color:var(--color-accent)]/60 hover:bg-amber-50/40">
                    <p class="text-xs uppercase text-slate-500">Tienda</p>
                    <p class="font-semibold text-[color:var(--color-primary)]">Ventas del día</p>
                </a>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-4">
            <div class="card-premium p-5 border border-slate-100">
                <h3 class="font-bold text-[color:var(--color-primary)]">Estado de cuotas</h3>
                <canvas id="chartCuotas" height="180"></canvas>
            </div>
            <div class="lg:col-span-2 card-premium p-5 border border-slate-100">
                <h3 class="font-bold text-[color:var(--color-primary)]">Ingresos vs Gastos ({{ $ejercicioActual }})</h3>
                <canvas id="chartEconomia" height="120"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const cuotasCtx = document.getElementById('chartCuotas');
            if (cuotasCtx) {
                new Chart(cuotasCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Al corriente', 'Pendientes', 'Devueltos'],
                        datasets: [{
                            data: [{{ $cuotas['al_corriente'] }}, {{ $cuotas['pendientes'] }}, {{ $cuotas['devueltos'] }}],
                            backgroundColor: ['#0F172A', '#C6A16A', '#B45309']
                        }]
                    }
                });
            }
            const ecoCtx = document.getElementById('chartEconomia');
            if (ecoCtx) {
                new Chart(ecoCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($labelsMeses),
                        datasets: [
                            { label: 'Ingresos', data: @json($ingresosMes), backgroundColor: '#0F172A' },
                            { label: 'Gastos', data: @json($gastosMes), backgroundColor: '#DC2626' }
                        ]
                    },
                    options: { responsive: true, scales: { y: { beginAtZero: true } } }
                });
            }
        </script>
    @endpush
</x-app-layout>
