<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-[color:var(--color-primary)]">Análisis de deuda</h2>
    </x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        @include('economia.partials.subnav')

        @if (session('status'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">{{ session('error') }}</div>
        @endif

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 mb-6">
            <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-2">Morosidad (subcuenta 430 / 431)</h3>
            <p class="text-sm text-slate-600 mb-4">
                Listado de hermanos con <strong>saldo deudor</strong> en su cuenta auxiliar personal. La antigüedad se estima desde el inicio del período de saldo positivo actual.
            </p>
            <form method="GET" action="{{ route('economia.analisis-deuda.index') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Filtro antigüedad</label>
                    <select name="antiguedad" class="input-premium">
                        <option value="" @selected(! $filtroAntiguedad)>Todos con saldo deudor</option>
                        <option value="1y" @selected($filtroAntiguedad === '1y')>Deuda &gt; 1 año</option>
                        <option value="3y" @selected($filtroAntiguedad === '3y')>Deuda &gt; 3 años</option>
                    </select>
                </div>
                <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Aplicar</button>
            </form>
        </div>

        @if ($filas->isEmpty())
            <p class="text-sm text-slate-600 text-center py-12">No hay hermanos que cumplan el criterio.</p>
        @else
            <form method="POST" action="{{ route('economia.analisis-deuda.reclamacion-masiva') }}" class="space-y-4" onsubmit="return confirm('¿Enviar recordatorio por correo con extracto PDF a los seleccionados?');">
                @csrf
                @if ($filtroAntiguedad)
                    <input type="hidden" name="antiguedad" value="{{ $filtroAntiguedad }}">
                @endif

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Reclamación masiva (email + extracto)</button>
                    <span class="text-xs text-slate-500 self-center">Máx. 40 por envío. Requiere email válido en la ficha.</span>
                </div>

                <div class="hidden md:block card-premium overflow-hidden border-t-2 border-t-[color:var(--color-accent)]">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-600">
                            <tr>
                                <th class="px-3 py-3 w-10"></th>
                                <th class="px-3 py-3">Hermano</th>
                                <th class="px-3 py-3">Subcuenta</th>
                                <th class="px-3 py-3 text-right">Saldo deudor</th>
                                <th class="px-3 py-3">Inicio deuda</th>
                                <th class="px-3 py-3 text-right">Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filas as $f)
                                @php $h = $f['hermano']; @endphp
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2">
                                        <input type="checkbox" name="hermano_ids[]" value="{{ $h->id }}" class="rounded border-slate-300" @disabled(! filter_var(trim((string) $h->email), FILTER_VALIDATE_EMAIL)) />
                                    </td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('hermanos.show', $h) }}" class="font-semibold text-[color:var(--color-primary)] hover:underline">N.º {{ $h->numero_hermano }} — {{ $h->nombreCompleto() }}</a>
                                        <div class="text-xs text-slate-500">{{ $h->email ?: 'Sin email' }}</div>
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $f['cuenta_codigo'] ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right font-mono tabular-nums font-semibold text-rose-800">{{ number_format($f['saldo'], 2, ',', '.') }} €</td>
                                    <td class="px-3 py-2 text-slate-600">{{ optional($f['fecha_inicio_deuda'])->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ $f['dias_mora'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden space-y-3">
                    @foreach ($filas as $f)
                        @php $h = $f['hermano']; @endphp
                        <div class="card-premium p-4 border border-slate-200 border-t-2 border-t-[color:var(--color-accent)]">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="hermano_ids[]" value="{{ $h->id }}" class="mt-1 rounded border-slate-300" @disabled(! filter_var(trim((string) $h->email), FILTER_VALIDATE_EMAIL)) />
                                <span>
                                    <span class="font-semibold text-[color:var(--color-primary)]">N.º {{ $h->numero_hermano }}</span>
                                    <span class="block text-sm">{{ $h->nombreCompleto() }}</span>
                                    <span class="text-xs text-slate-500">{{ $h->email ?: 'Sin email' }}</span>
                                </span>
                            </label>
                            <p class="mt-2 text-sm font-mono">Saldo: <strong class="text-rose-800">{{ number_format($f['saldo'], 2, ',', '.') }} €</strong></p>
                            <p class="text-xs text-slate-600">Subcuenta {{ $f['cuenta_codigo'] ?? '—' }} · Mora {{ $f['dias_mora'] ?? '—' }} días</p>
                        </div>
                    @endforeach
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
