<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Lotería y rifas</h2>
            <p class="text-sm text-slate-600 mt-1">Sorteos de Navidad, Niño y reparto de tacos entre hermanos.</p>
        </div>

        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-base font-bold text-[color:var(--color-primary)] mb-4">Nuevo sorteo</h3>
                <form method="POST" action="{{ route('economia.loterias.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Sorteo</label>
                        <input type="text" name="sorteo" value="{{ old('sorteo') }}" required maxlength="120" class="input-premium w-full" placeholder="Navidad 2025">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Número</label>
                        <input type="text" name="numero" value="{{ old('numero') }}" required maxlength="80" class="input-premium w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Serie / fracción</label>
                        <input type="text" name="serie_fraccion" value="{{ old('serie_fraccion') }}" maxlength="120" class="input-premium w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Total participaciones</label>
                        <input type="number" name="total_participaciones" value="{{ old('total_participaciones', 0) }}" min="0" required class="input-premium w-full font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Precio por participación (€)</label>
                        <input type="number" name="precio_participacion" value="{{ old('precio_participacion') }}" step="0.01" min="0" required class="input-premium w-full font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Donativo total (€)</label>
                        <input type="number" name="donativo" value="{{ old('donativo', 0) }}" step="0.01" min="0" class="input-premium w-full font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Observaciones</label>
                        <textarea name="observaciones" rows="2" maxlength="2000" class="input-premium w-full">{{ old('observaciones') }}</textarea>
                    </div>
                    <button type="submit" class="btn-accent w-full text-xs uppercase tracking-wider">Guardar sorteo</button>
                </form>
            </div>

            <div class="lg:col-span-2 card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-[color:var(--color-primary)]">Sorteos registrados</h3>
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                            <tr>
                                <th class="px-4 py-3">Sorteo</th>
                                <th class="px-4 py-3">Número</th>
                                <th class="px-4 py-3">Particip.</th>
                                <th class="px-4 py-3">Precio</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($loterias as $l)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-2 font-medium">{{ $l->sorteo }}</td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $l->numero }} @if($l->serie_fraccion)<span class="text-slate-500">· {{ $l->serie_fraccion }}</span>@endif</td>
                                    <td class="px-4 py-2">{{ $l->total_participaciones }} <span class="text-slate-400">({{ (int) ($l->asignaciones_sum_participaciones ?? 0) }} asign.)</span></td>
                                    <td class="px-4 py-2 font-mono">{{ number_format((float) $l->precio_participacion, 2, ',', '.') }} €</td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('economia.loterias.show', $l) }}" class="btn-soft text-xs">Gestionar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">Aún no hay sorteos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden divide-y divide-slate-100 p-2">
                    @forelse ($loterias as $l)
                        <a href="{{ route('economia.loterias.show', $l) }}" class="block p-3 rounded-xl hover:bg-slate-50">
                            <div class="font-semibold text-[color:var(--color-primary)]">{{ $l->sorteo }}</div>
                            <div class="text-xs font-mono text-slate-600">{{ $l->numero }}</div>
                        </a>
                    @empty
                        <p class="p-4 text-sm text-slate-500">Sin sorteos.</p>
                    @endforelse
                </div>
                <div class="p-4">{{ $loterias->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
