@extends('layouts.portal')

@section('title', 'Tienda — Click & Collect')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-[color:var(--color-primary)]">Tienda de la Hermandad</h1>
            <p class="text-sm text-slate-600 mt-1">Reserve para recoger en la Casa Hermandad o pague por Bizum desde el móvil.</p>
        </div>

        @if ($carrito->isNotEmpty())
            <section class="portal-card border-t-2 border-t-[color:var(--color-accent)]">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">Su carrito</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($carrito as $linea)
                        <li class="flex items-center justify-between gap-2 text-sm border-b border-slate-100 pb-2">
                            <span class="min-w-0 font-medium text-slate-800">{{ $linea->producto->nombre }} × {{ $linea->cantidad }}</span>
                            <span class="shrink-0 font-bold tabular-nums text-[color:var(--color-accent)]">{{ number_format($linea->subtotal, 2, ',', '.') }} €</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-lg font-bold text-[color:var(--color-primary)] tabular-nums">Total {{ number_format($totalCarrito, 2, ',', '.') }} €</p>
                <div class="mt-4 flex flex-col gap-2">
                    <form method="post" action="{{ route('portal.tienda.reservar') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-[color:var(--color-primary)] text-white text-sm font-bold py-3 px-4 hover:opacity-95">
                            Reservar y recoger en la Casa Hermandad
                        </button>
                    </form>
                    <form method="post" action="{{ route('portal.tienda.bizum') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border-2 border-[color:var(--color-accent)] text-[color:var(--color-primary)] text-sm font-bold py-3 px-4 bg-[color:var(--color-accent)]/10">
                            Registrar pago por Bizum
                        </button>
                    </form>
                    <p class="text-[11px] text-slate-500 text-center">El Bizum queda registrado en contabilidad como cobro tienda; confirme la transferencia en su app.</p>
                    <div class="flex gap-2 justify-center">
                        <form method="post" action="{{ route('portal.tienda.carrito.vaciar') }}">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 underline">Vaciar carrito</button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        @foreach ($categorias as $clave => $etiqueta)
            @php
                $grupo = $productos->where('categoria', $clave);
            @endphp
            @continue($grupo->isEmpty())
            <section>
                <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-accent)] mb-3">{{ $etiqueta }}</h2>
                <div class="space-y-3">
                    @foreach ($grupo as $p)
                        <article class="portal-card flex gap-3 items-center">
                            <div class="h-16 w-16 rounded-xl bg-slate-100 shrink-0 overflow-hidden flex items-center justify-center border border-slate-100">
                                @if ($p->urlImagen())
                                    <img src="{{ $p->urlImagen() }}" alt="" class="h-full w-full object-cover" />
                                @else
                                    <span class="text-lg font-bold text-slate-300">{{ strtoupper(substr($p->nombre, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-[color:var(--color-primary)] leading-tight text-sm">{{ $p->nombre }}</h3>
                                <p class="text-base font-bold text-[color:var(--color-accent)] tabular-nums mt-1">{{ number_format((float) $p->precio_venta, 2, ',', '.') }} €</p>
                                <p class="text-[11px] text-slate-500">Stock {{ $p->stock_actual }}</p>
                            </div>
                            <form method="post" action="{{ route('portal.tienda.carrito.agregar', $p) }}" class="shrink-0">
                                @csrf
                                <input type="hidden" name="cantidad" value="1" />
                                <button type="submit" class="rounded-full h-11 w-11 flex items-center justify-center bg-[color:var(--color-accent)]/20 text-[color:var(--color-primary)] font-bold text-xl border border-[color:var(--color-accent)]/40 hover:bg-[color:var(--color-accent)]/30" title="Añadir">+</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if ($productos->isEmpty())
            <p class="text-center text-slate-500 py-8">No hay artículos disponibles en este momento.</p>
        @endif
    </div>
@endsection
