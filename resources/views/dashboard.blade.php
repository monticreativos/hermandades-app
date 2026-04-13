<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-[color:var(--color-primary)]">{{ ($esDashboardGestion ?? true) ? 'Dashboard principal' : 'Mi panel' }}</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 space-y-6">
        @if (($esDashboardGestion ?? true))
            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4 sm:p-5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">¿Qué quieres hacer hoy?</label>
                <div x-data="{txt:'',loading:false,error:'',resultado:null}" class="space-y-2">
                    <div class="flex gap-2">
                        <input x-model="txt" type="text" class="input-premium flex-1" placeholder="Ej: Registrar una factura de luz, buscar al hermano 140..." />
                        <button
                            type="button"
                            class="btn-accent text-xs uppercase tracking-wider"
                            @click="
                                loading=true; error=''; resultado=null;
                                fetch('{{ route('economia.asientos.ia-generar') }}', {
                                    method:'POST',
                                    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                                    body: JSON.stringify({descripcion: txt, tratamiento_iva: 'auto'})
                                }).then(r=>r.json().then(d=>({ok:r.ok,d}))).then(({ok,d})=>{
                                    if(!ok) throw new Error(d?.errors?.descripcion?.[0] || 'No se pudo obtener sugerencia');
                                    resultado=d;
                                }).catch(e=>error=e.message).finally(()=>loading=false);
                            "
                        >
                            <span x-text="loading ? 'Pensando…' : 'Sugerir'"></span>
                        </button>
                    </div>
                    <p x-show="error" x-text="error" class="text-xs text-rose-700"></p>
                    <div x-show="resultado" x-cloak class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">
                        <p class="font-semibold text-[color:var(--color-primary)]">Sugerencia IA: <span x-text="resultado?.glosa"></span></p>
                        <p class="text-xs text-slate-600 mt-1">Use «Nuevo asiento» en Libro diario para revisar y guardar.</p>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="card-premium p-5 border-t-2 border-t-[color:var(--color-accent)]"><p class="text-xs uppercase text-slate-500">Hermanos (altas año)</p><p class="text-3xl font-bold text-[color:var(--color-primary)]">{{ $altasAnio }}</p><p class="text-xs mt-1 text-slate-500">Total {{ $totalHermanos }} · Bajas {{ $bajasAnio }}</p></div>
                <div class="card-premium p-5 border-t-2 border-t-slate-200"><p class="text-xs uppercase text-slate-500">Tesorería Banco (572)</p><p class="text-3xl font-bold text-[color:var(--color-primary)]">{{ number_format($saldoBanco572, 2, ',', '.') }} €</p><p class="text-xs mt-1 text-slate-500">Saldo libro mayor</p></div>
                <div class="card-premium p-5 border-t-2 border-t-slate-200"><p class="text-xs uppercase text-slate-500">Tesorería Caja (570)</p><p class="text-3xl font-bold text-[color:var(--color-primary)]">{{ number_format($saldoCaja570, 2, ',', '.') }} €</p><p class="text-xs mt-1 text-slate-500">Saldo libro mayor</p></div>
                <div class="card-premium p-5 border-t-2 border-t-slate-200"><p class="text-xs uppercase text-slate-500">Tienda mes</p><p class="text-3xl font-bold text-[color:var(--color-primary)]">{{ number_format($ventasMes, 2, ',', '.') }} €</p><p class="text-xs mt-1 text-rose-700">Recibos devueltos: {{ $recibosDevueltosPendientes }}</p></div>
            </div>

            <div class="grid lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 card-premium p-5 border border-slate-100">
                    <h3 class="font-bold text-[color:var(--color-primary)]">Ingresos vs Gastos (últimos 6 meses)</h3>
                    <canvas id="chartGestion" height="120"></canvas>
                </div>
                <div class="card-premium p-5 border border-slate-100">
                    <h3 class="font-bold text-[color:var(--color-primary)]">Próximo evento</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse($eventos as $ev)
                            <li class="border-b border-slate-100 pb-2"><p class="font-semibold">{{ $ev['titulo'] }}</p><p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($ev['fecha'])->format('d/m/Y') }} {{ $ev['meta'] ? '· '.$ev['meta'] : '' }}</p></li>
                        @empty
                            <li class="text-slate-500">Sin eventos próximos.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-4">
                <div class="card-premium p-5 border border-slate-100">
                    <h3 class="font-bold text-[color:var(--color-primary)]">Últimos 5 asientos</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($ultimosAsientos as $a)
                            <li class="rounded-lg border border-slate-200 px-3 py-2">
                                <p class="font-semibold">#{{ $a->numero_asiento }} / {{ $a->ejercicio?->año }}</p>
                                <p class="text-xs text-slate-500">{{ $a->fecha->format('d/m/Y') }} · {{ $a->glosa }}</p>
                            </li>
                        @empty
                            <li class="text-slate-500">Sin asientos.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-premium p-5 border border-slate-100">
                    <h3 class="font-bold text-[color:var(--color-primary)]">Últimas 5 altas de hermanos</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        @forelse ($ultimasAltas as $h)
                            <li class="rounded-lg border border-slate-200 px-3 py-2">
                                <p class="font-semibold">N.º {{ $h->numero_hermano }} · {{ $h->nombre }} {{ $h->apellidos }}</p>
                                <p class="text-xs text-slate-500">Alta: {{ optional($h->fecha_alta)->format('d/m/Y') ?? '—' }}</p>
                            </li>
                        @empty
                            <li class="text-slate-500">Sin altas recientes.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @else
            @if (! $hermano)
                <div class="card-premium p-6 border border-amber-200 bg-amber-50/60">
                    <p class="font-semibold text-amber-900">No se ha encontrado ficha de hermano asociada a este usuario web.</p>
                    <p class="text-sm text-amber-800 mt-1">Vincule el email del usuario con la ficha del hermano para mostrar su panel personal.</p>
                </div>
            @else
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="md:col-span-2 card-premium p-5 border-t-2 border-t-[color:var(--color-accent)]">
                        <h3 class="font-bold text-[color:var(--color-primary)]">{{ $hermano->nombre }} {{ $hermano->apellidos }}</h3>
                        <p class="text-sm text-slate-600 mt-1">N.º Hermano {{ $hermano->numero_hermano }} · Antigüedad {{ $antiguedad }} años</p>
                        <div class="mt-3">
                            @if ($cuotaOk)
                                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-800 px-3 py-1 text-xs font-semibold">Al corriente</span>
                            @else
                                <span class="inline-flex rounded-full bg-rose-100 text-rose-800 px-3 py-1 text-xs font-semibold">Tienes recibos pendientes</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-premium p-5 border border-slate-100">
                        <h3 class="font-bold text-[color:var(--color-primary)]">Próximo culto</h3>
                        @if ($proximoEvento)
                            <p class="text-sm mt-2">{{ $proximoEvento['titulo'] }}</p>
                            <p class="text-xs text-slate-500">{{ $proximoEvento['fecha'] }}</p>
                        @else
                            <p class="text-sm text-slate-500 mt-2">Sin evento próximo configurado.</p>
                        @endif
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-3">
                    <a href="{{ route('portal.papeleta.info') }}" class="card-premium p-4 text-center border border-slate-100 hover:border-[color:var(--color-accent)]/50"><p class="font-semibold text-[color:var(--color-primary)]">Descargar Papeleta</p></a>
                    <a href="{{ route('portal.tienda.index') }}" class="card-premium p-4 text-center border border-slate-100 hover:border-[color:var(--color-accent)]/50"><p class="font-semibold text-[color:var(--color-primary)]">Ir a la Tienda</p></a>
                    <a href="{{ route('profile.edit') }}" class="card-premium p-4 text-center border border-slate-100 hover:border-[color:var(--color-accent)]/50"><p class="font-semibold text-[color:var(--color-primary)]">Mis Datos</p></a>
                </div>
            @endif
        @endif
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @if (($esDashboardGestion ?? true))
            <script>
            const ctxGestion = document.getElementById('chartGestion');
            if (ctxGestion) {
                new Chart(ctxGestion, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            { label: 'Ingresos', data: @json($chartIngresos), borderColor: '#0F172A', backgroundColor: 'rgba(15,23,42,.12)', tension: .25, fill: true },
                            { label: 'Gastos', data: @json($chartGastos), borderColor: '#DC2626', backgroundColor: 'rgba(220,38,38,.1)', tension: .25, fill: true }
                        ]
                    },
                    options: { responsive: true }
                });
            }
        </script>
        @endif
    @endpush
</x-app-layout>
