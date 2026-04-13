<x-app-layout>
    <x-slot name="header"></x-slot>

    <div
        class="py-8 w-full px-2 sm:px-4 lg:px-6"
        x-data="{
            q: '{{ request('q') }}',
            tipo: '{{ request('tipo') }}',
            grupo: '{{ request('grupo') }}',
            expandidos: {},
            toggleGrupo(g) { this.expandidos[g] = !this.expandidos[g] },
            todosAbiertos: true,
            toggleTodos() {
                this.todosAbiertos = !this.todosAbiertos;
                document.querySelectorAll('[data-grupo-body]').forEach(el => {
                    const g = el.dataset.grupoBody;
                    this.expandidos[g] = this.todosAbiertos;
                });
            }
        }"
        x-init="@foreach($cuentasAgrupadas->keys() as $g) expandidos['{{ $g }}'] = true; @endforeach"
    >
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Plan General Contable</h2>
            <p class="text-sm text-slate-600 mt-1">Adaptado a Hermandades y Cofradías — PGC Entidades Diocesanas (CEE 2016)</p>
        </div>

        @include('economia.partials.subnav')

        {{-- Resumen --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            @php
                $badgeMap = [
                    'Activo' => 'bg-blue-50 text-blue-800 border-blue-200',
                    'Pasivo' => 'bg-rose-50 text-rose-800 border-rose-200',
                    'Patrimonio' => 'bg-violet-50 text-violet-800 border-violet-200',
                    'Ingreso' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                    'Gasto' => 'bg-amber-50 text-amber-800 border-amber-200',
                ];
            @endphp
            <div class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)] text-center">
                <div class="text-2xl font-bold text-[color:var(--color-primary)]">{{ $totalCuentas }}</div>
                <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Total cuentas</div>
            </div>
            @php
                $borderTopMap = [
                    'Activo' => 'border-t-blue-400',
                    'Pasivo' => 'border-t-rose-400',
                    'Patrimonio' => 'border-t-violet-400',
                    'Ingreso' => 'border-t-emerald-400',
                    'Gasto' => 'border-t-amber-400',
                ];
            @endphp
            @foreach ($totalesTipo as $tipo => $total)
                <div class="card-premium p-4 text-center border-t-2 {{ $borderTopMap[$tipo] ?? 'border-t-slate-300' }}">
                    <div class="text-2xl font-bold {{ explode(' ', $badgeMap[$tipo] ?? '')[1] ?? 'text-slate-800' }}">{{ $total }}</div>
                    <div class="text-xs uppercase tracking-wide mt-1 text-slate-500">{{ $tipo }}</div>
                </div>
            @endforeach
        </div>

        {{-- Filtros --}}
        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <form method="GET" action="{{ route('economia.plan-contable.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700">Buscar cuenta</label>
                    <input type="search" name="q" value="{{ request('q') }}" x-model="q" class="input-premium w-full" placeholder="Código o nombre…">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Tipo</label>
                    <select name="tipo" class="input-premium w-full" x-model="tipo">
                        <option value="">Todos</option>
                        <option value="Activo">Activo</option>
                        <option value="Pasivo">Pasivo</option>
                        <option value="Patrimonio">Patrimonio</option>
                        <option value="Ingreso">Ingreso</option>
                        <option value="Gasto">Gasto</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700">Grupo</label>
                    <select name="grupo" class="input-premium w-full" x-model="grupo">
                        <option value="">Todos</option>
                        @foreach ($gruposNombres as $num => $nom)
                            <option value="{{ $num }}" @selected(request('grupo') === (string)$num)>G{{ $num }} — {{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-4 flex gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Filtrar</button>
                    <a href="{{ route('economia.plan-contable.index') }}" class="btn-soft text-xs">Limpiar</a>
                    <button type="button" @click="toggleTodos()" class="btn-soft text-xs ml-auto" x-text="todosAbiertos ? 'Colapsar todos' : 'Expandir todos'"></button>
                </div>
            </form>
        </div>

        {{-- Tabla por grupo --}}
        @forelse ($cuentasAgrupadas as $numGrupo => $cuentasGrupo)
            @php
                $nombreGrupo = $gruposNombres[$numGrupo] ?? 'Grupo '.$numGrupo;
                $coloresGrupo = [
                    '1' => 'from-violet-600 to-violet-500',
                    '2' => 'from-blue-600 to-blue-500',
                    '3' => 'from-cyan-600 to-cyan-500',
                    '4' => 'from-teal-600 to-teal-500',
                    '5' => 'from-sky-600 to-sky-500',
                    '6' => 'from-amber-600 to-amber-500',
                    '7' => 'from-emerald-600 to-emerald-500',
                ];
                $gradiente = $coloresGrupo[$numGrupo] ?? 'from-slate-600 to-slate-500';
            @endphp
            <div class="card-premium overflow-hidden mb-5">
                {{-- Cabecera grupo --}}
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-6 py-4 bg-gradient-to-r {{ $gradiente }} text-white cursor-pointer select-none group"
                    @click="toggleGrupo('{{ $numGrupo }}')"
                >
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/20 text-lg font-black">{{ $numGrupo }}</span>
                        <div class="text-left">
                            <span class="font-bold text-base">{{ $nombreGrupo }}</span>
                            <span class="block text-xs text-white/70">{{ $cuentasGrupo->count() }} cuentas</span>
                        </div>
                    </div>
                    <svg class="w-5 h-5 transition-transform duration-200" :class="expandidos['{{ $numGrupo }}'] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                {{-- Contenido --}}
                <div
                    x-show="expandidos['{{ $numGrupo }}']"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    x-cloak
                    data-grupo-body="{{ $numGrupo }}"
                >
                    {{-- Escritorio --}}
                    <div class="hidden md:block">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                                    <th class="px-6 py-3 text-left w-32">Código</th>
                                    <th class="px-6 py-3 text-left">Nombre</th>
                                    <th class="px-6 py-3 text-left w-28">Tipo</th>
                                    <th class="px-6 py-3 text-center w-20">Nivel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cuentasGrupo as $cuenta)
                                    @php
                                        $len = strlen($cuenta->codigo);
                                        $isGrupo = $len <= 2;
                                        $isSubgrupo = $len === 3;
                                        $indent = match(true) {
                                            $len <= 2 => 'pl-6',
                                            $len === 3 => 'pl-10',
                                            $len === 4 => 'pl-14',
                                            default => 'pl-18',
                                        };
                                        $fontWeight = $isGrupo ? 'font-bold text-[color:var(--color-primary)]' : ($isSubgrupo ? 'font-semibold text-slate-800' : 'text-slate-700');
                                        $bgRow = $isGrupo ? 'bg-slate-50/60' : '';
                                        $tipoBadge = match($cuenta->tipo) {
                                            'Activo' => 'bg-blue-100 text-blue-800',
                                            'Pasivo' => 'bg-rose-100 text-rose-800',
                                            'Patrimonio' => 'bg-violet-100 text-violet-800',
                                            'Ingreso' => 'bg-emerald-100 text-emerald-800',
                                            'Gasto' => 'bg-amber-100 text-amber-800',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/80 {{ $bgRow }}">
                                        <td class="px-6 py-2.5 font-mono text-xs {{ $indent }} whitespace-nowrap {{ $fontWeight }}">
                                            {{ $cuenta->codigo }}
                                        </td>
                                        <td class="px-6 py-2.5 {{ $fontWeight }}">
                                            {{ $cuenta->nombre }}
                                        </td>
                                        <td class="px-6 py-2.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $tipoBadge }}">
                                                {{ $cuenta->tipo }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-2.5 text-center">
                                            @if ($isGrupo)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-[10px] font-bold text-slate-600">SG</span>
                                            @elseif ($isSubgrupo)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-[10px] font-bold text-slate-500">Cta</span>
                                            @else
                                                <span class="text-xs text-slate-400">Sub</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Móvil --}}
                    <div class="md:hidden divide-y divide-slate-100">
                        @foreach ($cuentasGrupo as $cuenta)
                            @php
                                $len = strlen($cuenta->codigo);
                                $isGrupo = $len <= 2;
                                $isSubgrupo = $len === 3;
                                $indent = match(true) {
                                    $len <= 2 => 'ml-0',
                                    $len === 3 => 'ml-3',
                                    $len === 4 => 'ml-6',
                                    default => 'ml-9',
                                };
                                $fontWeight = $isGrupo ? 'font-bold text-[color:var(--color-primary)]' : ($isSubgrupo ? 'font-semibold text-slate-800' : 'text-slate-700');
                                $bgRow = $isGrupo ? 'bg-slate-50/60' : '';
                                $tipoBadge = match($cuenta->tipo) {
                                    'Activo' => 'bg-blue-100 text-blue-800',
                                    'Pasivo' => 'bg-rose-100 text-rose-800',
                                    'Patrimonio' => 'bg-violet-100 text-violet-800',
                                    'Ingreso' => 'bg-emerald-100 text-emerald-800',
                                    'Gasto' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <div class="px-4 py-3 {{ $bgRow }} {{ $indent }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <span class="font-mono text-xs {{ $fontWeight }}">{{ $cuenta->codigo }}</span>
                                        <span class="block text-sm {{ $fontWeight }} mt-0.5">{{ $cuenta->nombre }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 {{ $tipoBadge }}">
                                        {{ $cuenta->tipo }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="card-premium p-12 text-center">
                <p class="text-slate-500">No se encontraron cuentas con los filtros seleccionados.</p>
                <a href="{{ route('economia.plan-contable.index') }}" class="btn-accent text-xs mt-4 inline-block">Ver plan completo</a>
            </div>
        @endforelse
    </div>
</x-app-layout>
