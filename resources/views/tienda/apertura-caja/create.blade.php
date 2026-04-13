<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.panel') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Panel</a>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Apertura de caja (tienda)</h1>
            <p class="text-sm text-slate-600 mt-1">Registre el efectivo inicial en cajón antes de abrir el TPV. El cierre comparará conteo físico con saldo inicial + ventas en efectivo del día.</p>
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

        @if ($aperturaExistente)
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                Apertura ya registrada: <span class="font-bold tabular-nums">{{ number_format((float) $aperturaExistente->saldo_inicial_efectivo, 2, ',', '.') }} €</span> por {{ $aperturaExistente->user?->name ?? '—' }}.
                Volver a guardar actualiza el registro.
            </div>
        @endif

        <form method="post" action="{{ route('tienda.apertura-caja.store') }}" class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)] rounded-xl space-y-4">
            @csrf
            <input type="hidden" name="fecha" value="{{ $fecha }}" />
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Saldo inicial en efectivo (conteo al abrir)</label>
                <input type="number" name="saldo_inicial_efectivo" value="{{ old('saldo_inicial_efectivo', $aperturaExistente->saldo_inicial_efectivo ?? '0') }}" step="0.01" min="0" required class="input-premium w-full tabular-nums text-lg" />
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Notas</label>
                <textarea name="notas" rows="2" maxlength="2000" class="input-premium w-full text-sm" placeholder="Opcional">{{ old('notas', $aperturaExistente->notas ?? '') }}</textarea>
            </div>
            @if ($errors->any())
                <div class="text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            <button type="submit" class="btn-accent uppercase tracking-wider text-xs px-6 py-3 w-full sm:w-auto">Registrar apertura</button>
        </form>

        <p class="text-sm text-slate-500">
            <a href="{{ route('tienda.cierre-caja.create', ['fecha' => $fecha]) }}" class="text-[color:var(--color-accent)] font-semibold hover:underline">Ir al cierre del mismo día</a>
        </p>
    </div>
</x-app-layout>
