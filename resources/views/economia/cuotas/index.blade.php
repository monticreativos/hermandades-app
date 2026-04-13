<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Generación de cuotas</h2>
            <p class="text-sm text-slate-600 mt-1">Asiento masivo de cuotas al cobro y exportación SEPA (pain.008).</p>
        </div>

        @include('economia.partials.subnav')

        <div class="mb-6 rounded-xl border border-slate-200 border-l-4 border-l-[color:var(--color-accent)] bg-slate-50/90 p-4 text-sm text-slate-700">
            <p class="font-semibold text-[color:var(--color-primary)] mb-1">Remesas con periodicidad flexible</p>
            <p class="mb-2">Para domiciliaciones por hermano con importes distintos (mensual / trimestral / semestral / anual) y conciliación automática con respuesta banco (camt.053 o CSV auxiliar), use <a href="{{ route('economia.remesas.index') }}" class="font-semibold text-[color:var(--color-accent)] hover:underline">Economía → Remesas SEPA</a>.</p>
            <p class="font-semibold text-[color:var(--color-primary)] mb-1">Vinculación con fichas de hermanos (asiento masivo clásico)</p>
            <p>Si la cuenta <strong>debe</strong> es de deudores cofrades (430/431), tras generar el asiento los hermanos en <strong>Alta</strong> quedan con cuota <strong>Pendiente</strong> para el ejercicio actual. Al registrar el cobro en banco con un asiento <strong>Debe 572 / Haber 430–431</strong> y el mismo formato de concepto (<span class="font-mono">Cuota — n.º …</span>), el estado pasa a <strong>Al corriente</strong>. Esto alimenta el KPI de morosidad y el certificado de Hacienda.</p>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-base font-bold text-[color:var(--color-primary)] mb-4">Registrar asiento de cuotas</h3>
                <p class="text-sm text-slate-600 mb-4">Genera un asiento con una línea al debe por hermano en su <strong>subcuenta auxiliar 430.XXXXXX</strong> (código fijo de por vida) y una contrapartida única al haber (ingreso). El cobro en banco enlaza por esa subcuenta y por el texto del concepto.</p>

                <form method="POST" action="{{ route('economia.cuotas.generar-asiento') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Grupo de hermanos</label>
                        <select name="grupo" class="input-premium w-full" required>
                            <option value="todos_alta" @selected(old('grupo') === 'todos_alta')>Solo en Alta</option>
                            <option value="todos" @selected(old('grupo') === 'todos')>Todos (Alta, Baja y Difunto)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Importe por hermano (€)</label>
                        <input type="number" name="importe" step="0.01" min="0.01" value="{{ old('importe', '50') }}" class="input-premium w-full font-mono" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Glosa del asiento</label>
                        <input type="text" name="glosa" value="{{ old('glosa', 'Cuotas ordinarias — remesa') }}" maxlength="500" class="input-premium w-full" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Cuenta haber (ingreso)</label>
                        <select name="cuenta_haber_id" class="input-premium w-full" required>
                            @foreach ($cuentas as $c)
                                <option value="{{ $c->id }}" @selected((string) old('cuenta_haber_id', $cuentaHaberDefault?->id) === (string) $c->id)>
                                    {{ $c->codigo }} — {{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('grupo')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('importe')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('glosa')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('cuenta_haber_id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                    <button type="submit" class="btn-accent w-full sm:w-auto uppercase tracking-wider text-xs">Generar asiento</button>
                </form>
            </div>

            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-base font-bold text-[color:var(--color-primary)] mb-4">Exportar Cuaderno 19 (XML SEPA)</h3>
                <p class="text-sm text-slate-600 mb-4">XML pain.008 para adeudo directo. Solo hermanos con IBAN válido. Revise mandatos e identificador de acreedor con su entidad.</p>

                @can('cuotas.gestion')
                    <form method="POST" action="{{ route('economia.cuotas.exportar-sepa') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Grupo</label>
                            <select name="grupo" class="input-premium w-full" required>
                                <option value="todos_alta">Solo en Alta</option>
                                <option value="todos">Todos</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Importe por adeudo (€)</label>
                            <input type="number" name="importe" step="0.01" min="0.01" value="50" class="input-premium w-full font-mono" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Concepto en extracto</label>
                            <input type="text" name="concepto" value="Cuota hermandad" maxlength="140" class="input-premium w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700">Fecha de cobro (opcional)</label>
                            <input type="date" name="fecha_cobro" class="input-premium w-full">
                        </div>
                        <button type="submit" class="btn-accent w-full sm:w-auto uppercase tracking-wider text-xs">Descargar XML</button>
                    </form>
                @else
                    <p class="text-sm text-slate-500">No tiene permiso para exportar remesas. Solicite el permiso de cuotas a un administrador.</p>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
