@php
    use App\Models\VentaTienda;
    $labelMetodo = static fn (string $m): string => match ($m) {
        VentaTienda::METODO_EFECTIVO => 'Efectivo',
        VentaTienda::METODO_TARJETA => 'Tarjeta',
        VentaTienda::METODO_BIZUM => 'Bizum',
        default => $m,
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.panel') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Panel</a>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Ventas del día</h1>
            <p class="text-sm text-slate-600 mt-1">Listado detallado y totales por cajero y forma de pago.</p>
        </div>

        <form method="get" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Fecha</label>
                <input type="date" name="fecha" value="{{ $fecha }}" class="input-premium text-sm" onchange="this.form.submit()" />
            </div>
        </form>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="card-premium p-4 rounded-xl border border-slate-100">
                <p class="text-xs font-bold uppercase text-slate-500">Total día</p>
                <p class="text-2xl font-bold tabular-nums text-[color:var(--color-accent)] mt-1">{{ number_format($totalDia, 2, ',', '.') }} €</p>
                <p class="text-xs text-slate-500 mt-1">{{ $ventas->count() }} ticket(s)</p>
            </div>
            @foreach ($totalesMetodo as $metodo => $imp)
                <div class="card-premium p-4 rounded-xl border border-slate-100">
                    <p class="text-xs font-bold uppercase text-slate-500">{{ $labelMetodo($metodo) }}</p>
                    <p class="text-xl font-bold tabular-nums text-[color:var(--color-primary)] mt-1">{{ number_format($imp, 2, ',', '.') }} €</p>
                </div>
            @endforeach
        </div>

        @if ($porVendedor->isNotEmpty())
            <div class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)] rounded-xl">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)] mb-3">Por cajero</h2>
                <ul class="space-y-2 text-sm">
                    @foreach ($porVendedor as $uid => $grupo)
                        @php
                            $nombre = $uid ? ($users[$uid] ?? 'Usuario #'.$uid) : 'Portal / sin cajero';
                        @endphp
                        <li class="flex justify-between border-b border-slate-100 pb-2">
                            <span class="text-slate-700">{{ $nombre }}</span>
                            <span class="font-bold tabular-nums">{{ number_format($grupo->sum(fn (VentaTienda $v) => (float) $v->importe_total), 2, ',', '.') }} € <span class="text-slate-400 font-normal">({{ $grupo->count() }})</span></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="hidden lg:block card-premium overflow-hidden rounded-xl border border-slate-100">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Hora</th>
                        <th class="px-3 py-2">Folio</th>
                        <th class="px-3 py-2">Cajero</th>
                        <th class="px-3 py-2">Cliente</th>
                        <th class="px-3 py-2">Pago</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 w-24">Ticket</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ventas as $v)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-3 py-2 tabular-nums text-slate-600">{{ $v->created_at->format('H:i') }}</td>
                            <td class="px-3 py-2 font-mono text-xs">{{ $v->folio }}</td>
                            <td class="px-3 py-2">{{ $v->user?->name ?? '—' }}</td>
                            <td class="px-3 py-2">
                                @if ($v->venta_anonima)
                                    Público
                                @elseif ($v->hermano)
                                    n.º {{ $v->hermano->numero_hermano }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $labelMetodo($v->metodo_pago) }}</td>
                            <td class="px-3 py-2 text-right font-bold tabular-nums">{{ number_format((float) $v->importe_total, 2, ',', '.') }} €</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('tienda.ventas.ticket', $v) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-8 text-center text-slate-500">Sin ventas este día.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="lg:hidden space-y-3">
            @forelse ($ventas as $v)
                <article class="card-premium p-4 rounded-xl border border-slate-100">
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <p class="font-mono text-xs text-slate-500">{{ $v->folio }}</p>
                            <p class="font-bold text-lg tabular-nums text-[color:var(--color-primary)]">{{ number_format((float) $v->importe_total, 2, ',', '.') }} €</p>
                        </div>
                        <span class="text-xs text-slate-500">{{ $v->created_at->format('H:i') }}</span>
                    </div>
                    <p class="text-xs text-slate-600 mt-2">{{ $labelMetodo($v->metodo_pago) }} · {{ $v->user?->name ?? '—' }}</p>
                    <a href="{{ route('tienda.ventas.ticket', $v) }}" target="_blank" rel="noopener" class="inline-block mt-3 text-xs font-bold text-[color:var(--color-accent)]">Ticket PDF</a>
                </article>
            @empty
                <p class="text-center text-slate-500 py-6">Sin ventas.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
