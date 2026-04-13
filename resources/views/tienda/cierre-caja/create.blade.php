<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.panel') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Panel</a>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Cierre de caja (tienda)</h1>
            <p class="text-sm text-slate-600 mt-1">Descuadre de caja: efectivo esperado (apertura + ventas en efectivo) frente al conteo físico al cerrar.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        <form method="get" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Fecha</label>
                <input type="date" name="fecha" value="{{ $fecha }}" class="input-premium text-sm" onchange="this.form.submit()" />
            </div>
        </form>

        @if (! $apertura)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                No hay <strong>apertura de caja</strong> registrada para esta fecha. Se asume saldo inicial 0 €. <a href="{{ route('tienda.apertura-caja.create', ['fecha' => $fecha]) }}" class="font-semibold underline">Registrar apertura</a>
            </div>
        @endif

        @if ($cierreExistente)
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                Cierre ya registrado: conteo {{ number_format((float) $cierreExistente->conteo_efectivo_fisico, 2, ',', '.') }} €, descuadre {{ number_format((float) $cierreExistente->diferencia_efectivo, 2, ',', '.') }} €. Guardar de nuevo actualiza el registro.
            </div>
        @endif

        <div class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)] rounded-xl space-y-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">Teórico del sistema</h2>
            @php
                $ef = $totales[\App\Models\VentaTienda::METODO_EFECTIVO] ?? 0;
                $tj = $totales[\App\Models\VentaTienda::METODO_TARJETA] ?? 0;
                $bz = $totales[\App\Models\VentaTienda::METODO_BIZUM] ?? 0;
            @endphp
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-600">Efectivo (caja 570)</dt><dd class="font-bold tabular-nums">{{ number_format($ef, 2, ',', '.') }} €</dd></div>
                <div class="flex justify-between"><dt class="text-slate-600">Tarjeta (572)</dt><dd class="font-bold tabular-nums">{{ number_format($tj, 2, ',', '.') }} €</dd></div>
                <div class="flex justify-between"><dt class="text-slate-600">Bizum (572)</dt><dd class="font-bold tabular-nums">{{ number_format($bz, 2, ',', '.') }} €</dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-[color:var(--color-primary)]">
                    <dt>Total día</dt>
                    <dd class="tabular-nums">{{ number_format($ef + $tj + $bz, 2, ',', '.') }} €</dd>
                </div>
            </dl>
        </div>

        <div class="card-premium p-6 rounded-xl border border-slate-100 space-y-2 text-sm">
            <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">Efectivo en caja (cierre)</h2>
            <div class="flex justify-between"><span class="text-slate-600">Saldo inicial (apertura)</span><span class="font-bold tabular-nums">{{ number_format($saldoInicialEfectivo, 2, ',', '.') }} €</span></div>
            <div class="flex justify-between"><span class="text-slate-600">+ Ventas en efectivo (día)</span><span class="font-bold tabular-nums">{{ number_format($ef, 2, ',', '.') }} €</span></div>
            <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-[color:var(--color-primary)]">
                <span>= Efectivo esperado al cerrar</span>
                <span class="tabular-nums">{{ number_format($efectivoEsperadoCierre, 2, ',', '.') }} €</span>
            </div>
        </div>

        <form method="post" action="{{ route('tienda.cierre-caja.store') }}" class="card-premium p-6 rounded-xl border border-slate-100 space-y-4">
            @csrf
            <input type="hidden" name="fecha" value="{{ $fecha }}" />
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Conteo físico de efectivo (mayordomo)</label>
                <input type="number" name="conteo_efectivo_fisico" value="{{ old('conteo_efectivo_fisico', $cierreExistente->conteo_efectivo_fisico ?? $efectivoEsperadoCierre) }}" step="0.01" min="0" required class="input-premium w-full tabular-nums text-lg" />
                <p class="text-xs text-slate-500 mt-1">Compare con el efectivo esperado: {{ number_format($efectivoEsperadoCierre, 2, ',', '.') }} € (inicial + ventas efectivo).</p>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Notas</label>
                <textarea name="notas" rows="3" maxlength="2000" class="input-premium w-full text-sm" placeholder="Incidencias, sobrantes, etc.">{{ old('notas', $cierreExistente->notas ?? '') }}</textarea>
            </div>
            @if ($errors->any())
                <div class="text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            <button type="submit" class="btn-accent uppercase tracking-wider text-xs px-6 py-3 w-full sm:w-auto">Registrar cierre</button>
        </form>
    </div>
</x-app-layout>
