<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('tienda.tpv') }}" class="btn-accent text-xs uppercase tracking-wider">TPV</a>
            <a href="{{ route('tienda.productos.create') }}" class="btn-soft text-xs uppercase tracking-wider border border-slate-200">Nuevo producto</a>
        </div>
    </x-slot>

    <div class="py-6 space-y-6 max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Catálogo de tienda</h1>
                <p class="text-sm text-slate-600 mt-1">Precios TTC, stock y SKU para el lector de códigos.</p>
            </div>
            <a href="{{ route('tienda.panel') }}" class="text-sm text-slate-500 hover:text-[color:var(--color-primary)]">← Panel tienda</a>
        </div>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl bg-rose-50 text-rose-900 text-sm px-4 py-3">{{ session('error') }}</div>
        @endif

        <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden" x-data="{ filtrosAbiertos: false }">
            <div class="p-4 sm:p-6 border-b border-slate-100 flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-[color:var(--color-primary)]">Filtros</h2>
                <button type="button" @click="filtrosAbiertos = !filtrosAbiertos" class="btn-soft text-xs sm:hidden shrink-0">
                    <span x-text="filtrosAbiertos ? 'Ocultar' : 'Mostrar'"></span>
                </button>
            </div>
            <div class="p-4 sm:p-6" :class="filtrosAbiertos ? '' : 'max-sm:hidden'">
                <form method="get" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o SKU…" class="input-premium sm:col-span-2" />
                    <select name="categoria" class="input-premium">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $val => $label)
                            <option value="{{ $val }}" @selected(request('categoria') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="solo_bajo_minimo" value="1" @checked(request()->boolean('solo_bajo_minimo')) class="rounded border-slate-300" />
                        Solo bajo mínimo
                    </label>
                    <div class="sm:col-span-2 lg:col-span-4 flex gap-2">
                        <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Filtrar</button>
                        <a href="{{ route('tienda.productos.index') }}" class="btn-soft text-xs border border-slate-200 px-4 py-2 rounded-xl">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-premium border border-slate-100 rounded-xl p-4 sm:p-6 space-y-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-[color:var(--color-primary)]">Categorías editables</h2>
                <p class="text-xs text-slate-500">Puede crear, renombrar o eliminar categorías.</p>
            </div>
            <form method="post" action="{{ route('tienda.categorias.store') }}" class="flex flex-wrap gap-2">
                @csrf
                <input type="text" name="nombre" maxlength="80" required class="input-premium flex-1 min-w-52" placeholder="Nueva categoría (ej: Cerería)" />
                <button type="submit" class="btn-accent text-xs uppercase tracking-wider">Añadir</button>
            </form>
            <div class="grid sm:grid-cols-2 gap-3">
                @forelse ($categoriasTienda as $categoria)
                    <div class="rounded-xl border border-slate-100 p-3 space-y-2">
                    <form method="post" action="{{ route('tienda.categorias.update', $categoria) }}" class="space-y-2">
                            @csrf
                            @method('PUT')
                        <div class="flex gap-2">
                            <input type="text" name="nombre" value="{{ $categoria->nombre }}" required maxlength="80" class="input-premium flex-1" />
                            <button type="submit" class="btn-soft text-xs border border-slate-200 px-3">Guardar</button>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="text-xs text-slate-600 flex items-center gap-2">
                                <input type="hidden" name="activa" value="0" />
                                <input type="checkbox" name="activa" value="1" @checked($categoria->activa) class="rounded border-slate-300" />
                                Activa
                            </label>
                        </div>
                    </form>
                    <form method="post" action="{{ route('tienda.categorias.destroy', $categoria) }}" onsubmit="return confirm('¿Eliminar categoría?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-rose-600">Eliminar</button>
                    </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No hay categorías todavía.</p>
                @endforelse
            </div>
        </div>

        {{-- Escritorio: tabla --}}
        <div class="hidden md:block card-premium overflow-hidden rounded-xl border border-slate-100">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Categoría</th>
                        <th class="px-4 py-3 text-right">PVP</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3 text-center w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productos as $p)
                        <tr class="hover:bg-slate-50/80 {{ $p->bajoMinimo() ? 'bg-amber-50/40' : '' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($p->urlImagen())
                                        <img src="{{ $p->urlImagen() }}" alt="" class="h-10 w-10 rounded-lg object-cover border border-slate-100" />
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-400">{{ strtoupper(substr($p->nombre, 0, 1)) }}</div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-[color:var(--color-primary)]">{{ $p->nombre }}</p>
                                        @if ($p->sku)
                                            <p class="text-xs text-slate-500 font-mono">{{ $p->sku }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $p->categoria }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium">{{ number_format((float) $p->precio_venta, 2, ',', '.') }} €</td>
                            <td class="px-4 py-3 text-right tabular-nums {{ $p->bajoMinimo() ? 'text-amber-800 font-semibold' : '' }}">{{ $p->stock_actual }} <span class="text-slate-400 text-xs">/ mín. {{ $p->stock_minimo }}</span></td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('tienda.productos.edit', $p) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:border-[color:var(--color-accent)]/50 hover:text-[color:var(--color-primary)]" title="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </a>
                                    <form method="post" action="{{ route('tienda.productos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar este producto?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-rose-600 hover:bg-rose-50" title="Eliminar">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay productos. Cree el primero desde «Nuevo producto».</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Móvil: tarjetas --}}
        <div class="md:hidden space-y-3">
            @forelse ($productos as $p)
                <article class="card-premium rounded-xl p-4 border {{ $p->bajoMinimo() ? 'border-amber-200' : 'border-slate-100' }}">
                    <div class="flex gap-3">
                        @if ($p->urlImagen())
                            <img src="{{ $p->urlImagen() }}" alt="" class="h-16 w-16 rounded-xl object-cover shrink-0 border border-slate-100" />
                        @else
                            <div class="h-16 w-16 rounded-xl bg-slate-100 shrink-0 flex items-center justify-center font-bold text-slate-400">{{ strtoupper(substr($p->nombre, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-[color:var(--color-primary)] leading-tight">{{ $p->nombre }}</h3>
                            <p class="text-xs text-slate-500 mt-1">{{ $p->categoria }} @if($p->sku) · <span class="font-mono">{{ $p->sku }}</span> @endif</p>
                            <p class="text-lg font-bold text-[color:var(--color-accent)] tabular-nums mt-2">{{ number_format((float) $p->precio_venta, 2, ',', '.') }} €</p>
                            <p class="text-xs {{ $p->bajoMinimo() ? 'text-amber-800 font-semibold' : 'text-slate-600' }}">Stock {{ $p->stock_actual }} (mín. {{ $p->stock_minimo }})</p>
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        <a href="{{ route('tienda.productos.edit', $p) }}" class="btn-soft text-xs px-4 py-2 border border-slate-200 rounded-xl">Editar</a>
                        <form method="post" action="{{ route('tienda.productos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 px-3 py-2">Eliminar</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="text-center text-slate-500 py-8">Sin productos.</p>
            @endforelse
        </div>

        <div class="pb-8">
            {{ $productos->links() }}
        </div>
    </div>
</x-app-layout>
