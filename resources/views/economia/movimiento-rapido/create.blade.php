<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('economia.libro-diario.index') }}" class="btn-soft text-xs uppercase tracking-wider">Libro diario</a>
    </x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6 max-w-3xl">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Registrar movimiento</h2>
            <p class="mt-1 text-sm text-slate-600">Asistente sin números de cuenta: el sistema genera el asiento en partida doble según el PGC.</p>
        </div>

        @include('economia.partials.subnav')

        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">{{ session('error') }}</div>
        @endif

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6 space-y-4 text-sm text-slate-700">
            <p class="font-semibold text-[color:var(--color-primary)]">Recordatorio legal</p>
            <ul class="list-disc pl-5 space-y-1 text-slate-600">
                <li>Las <strong>cuotas de socios</strong> y los <strong>donativos</strong> en entidades sin ánimo de lucro suelen tratarse como <strong>operaciones no sujetas o exentas de IVA</strong> (no repercuten IVA en el sentido comercial habitual).</li>
                <li>La <strong>desgravación fiscal del donante</strong> (Ley 49/2002 de mecenazgo) exige requisitos en la entidad y en la comunicación a Hacienda (<strong>modelo 182</strong>). Este módulo le ayuda a listar importes por NIF; <strong>contraste siempre con su asesor fiscal</strong> y el diseño oficial de la AEAT.</li>
                <li>El <strong>IVA soportado</strong> en gastos debe controlarse aun cuando, en muchas hermandades, la deducción sea nula o limitada (afecta a la información del Impuesto de Sociedades si procede).</li>
            </ul>
        </div>

        <div
            class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 sm:p-8"
            x-data="{
                cat: @js(old('categoria', '')),
                esGasto() { return this.cat && this.cat.startsWith('gasto_'); },
                esPagoProveedor() { return this.cat === 'pago_proveedor'; },
                esGastoConIva() { return this.esGasto() && !this.esPagoProveedor(); },
                esDonativo() { return this.cat === 'ingreso_donativo'; },
                muestraHermano() { return this.cat === 'ingreso_cuota' || this.cat === 'ingreso_donativo'; },
                muestraBloqueProveedor() { return this.esGasto() || this.esPagoProveedor(); },
            }"
        >
            <form method="POST" action="{{ route('economia.movimiento-rapido.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Categoría</label>
                    <select name="categoria" x-model="cat" required class="input-premium w-full">
                        <option value="">— Elija —</option>
                        <optgroup label="Ingresos (sin IVA en este asistente)">
                            @foreach ($categoriasIngreso as $c)
                                <option value="{{ $c->value }}" @selected(old('categoria') === $c->value)>{{ $c->etiqueta() }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Gastos">
                            @foreach ($categoriasGasto as $c)
                                <option value="{{ $c->value }}" @selected(old('categoria') === $c->value)>{{ $c->etiqueta() }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Liquidación con proveedor">
                            @foreach ($categoriasLiquidacion as $c)
                                <option value="{{ $c->value }}" @selected(old('categoria') === $c->value)>{{ $c->etiqueta() }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    @error('categoria')
                        <p class="mt-1 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="portal-label block text-xs font-bold uppercase text-slate-500 mb-1">Fecha</label>
                        <input type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" required class="input-premium w-full" />
                        @error('fecha')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Importe total (€)</label>
                        <input type="number" name="importe" value="{{ old('importe') }}" step="0.01" min="0.01" required class="input-premium w-full" />
                        <p class="mt-1 text-xs text-slate-500" x-show="esGastoConIva()">Si desglosa IVA, este importe debe coincidir con base + IVA.</p>
                        @error('importe')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Entrada / salida de fondos</label>
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="metodo_tesoreria" value="banco" class="text-[color:var(--color-accent)]" @checked(old('metodo_tesoreria', 'banco') === 'banco') />
                            <span>Banco (Bizum, transferencia, TPV bancario…)</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="metodo_tesoreria" value="caja" class="text-[color:var(--color-accent)]" @checked(old('metodo_tesoreria') === 'caja') />
                            <span>Caja (efectivo)</span>
                        </label>
                    </div>
                    @error('metodo_tesoreria')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div x-show="muestraHermano()" x-cloak>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Hermano (donante / cotizante)</label>
                    <select name="hermano_id" class="input-premium w-full" :required="cat === 'ingreso_cuota' || (cat === 'ingreso_donativo' && $refs.apt && $refs.apt.checked)">
                        <option value="">— Seleccione —</option>
                        @foreach ($hermanos as $h)
                            <option value="{{ $h->id }}" @selected((string) old('hermano_id') === (string) $h->id)>
                                N.º {{ $h->numero_hermano }} — {{ $h->apellidos }}, {{ $h->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('hermano_id')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div x-show="esDonativo()" x-cloak class="rounded-xl border border-[color:var(--color-accent)]/40 bg-[color:var(--color-accent)]/5 p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="hidden" name="apt_modelo_182" value="0" />
                        <input type="checkbox" name="apt_modelo_182" value="1" x-ref="apt" class="mt-1 rounded border-slate-300 text-[color:var(--color-accent)]" @checked(old('apt_modelo_182')) />
                        <span>
                            <strong class="text-[color:var(--color-primary)]">Apto para desgravación fiscal</strong> (Ley de Mecenazgo / modelo 182).
                            Requiere donante identificado con <strong>DNI/NIE y domicilio fiscal completo</strong> en la ficha del hermano.
                        </span>
                    </label>
                    @error('apt_modelo_182')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <div x-show="esGastoConIva()" x-cloak class="space-y-3 border-t border-slate-200 pt-5">
                    <p class="text-xs font-bold uppercase text-slate-500">IVA en la factura (opcional)</p>
                    <p class="text-xs text-slate-600">Si la factura desglosa IVA, indique base e IVA soportado; el sistema contabilizará en cuenta 472 y generará total en banco/caja.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Base imponible (€)</label>
                            <input type="number" name="base_imponible" value="{{ old('base_imponible') }}" step="0.01" min="0" class="input-premium w-full" />
                            @error('base_imponible')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Cuota IVA soportado (€)</label>
                            <input type="number" name="cuota_iva" value="{{ old('cuota_iva') }}" step="0.01" min="0" class="input-premium w-full" />
                            @error('cuota_iva')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div x-show="muestraBloqueProveedor()" x-cloak class="space-y-3 border-t border-slate-200 pt-5">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Proveedor registrado (subcuenta 410.XXXXXX; histórico 400.XXXXXX)</label>
                        <select name="proveedor_id" class="input-premium w-full" :required="esPagoProveedor()">
                            <option value="">— Sin seleccionar (solo para gastos sin acreedor formal) —</option>
                            @foreach ($proveedores as $pv)
                                <option value="{{ $pv->id }}" @selected((string) old('proveedor_id') === (string) $pv->id)>
                                    {{ $pv->etiquetaListado() }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1" x-show="esGasto() && !esPagoProveedor()">Factura: Debe gasto / Haber subcuenta del proveedor (deuda). Use «Liquidación con proveedor» para registrar el pago desde banco/caja.</p>
                        <p class="text-[10px] text-slate-500 mt-1" x-show="esPagoProveedor()" x-cloak>Contabilidad estándar: <strong>Debe</strong> subcuenta del proveedor (baja la deuda) / <strong>Haber</strong> banco o caja.</p>
                        @error('proveedor_id')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="esGasto() && !esPagoProveedor()">
                        <label class="block text-xs text-slate-600 mb-1">Proveedor (texto libre, p. ej. ticket sin alta)</label>
                        <input type="text" name="proveedor_texto" value="{{ old('proveedor_texto') }}" class="input-premium w-full" maxlength="200" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Adjunto (PDF o imagen, opcional)</label>
                        <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.webp" class="block w-full text-sm text-slate-600" />
                        @error('archivo')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-500 mb-1">Nota interna (opcional)</label>
                    <input type="text" name="glosa" value="{{ old('glosa') }}" maxlength="500" class="input-premium w-full" placeholder="Se añadirá a la glosa del asiento" />
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="btn-accent uppercase tracking-wider text-xs">Registrar y generar asiento</button>
                    <a href="{{ route('economia.libro-diario.index') }}" class="btn-soft text-xs uppercase tracking-wider">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
