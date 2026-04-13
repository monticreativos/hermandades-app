@php
    $tpvCfg = [
        'productos' => route('tienda.api.productos'),
        'hermanos' => route('tienda.api.hermanos'),
        'checkout' => route('tienda.api.checkout'),
        'checkoutPedido' => route('tienda.api.checkout-pedido'),
        'pedido' => url('/tienda/api/pedido'),
    ];
    $cats = \App\Models\ProductoTienda::categorias();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('tienda.panel') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">Panel</a>
            <a href="{{ route('tienda.productos.index') }}" class="btn-accent text-xs uppercase tracking-wider">Catálogo</a>
        </div>
    </x-slot>

    <div
        class="max-w-[1600px] mx-auto -m-4 sm:-m-6 lg:-m-8 p-3 sm:p-4 lg:p-6 min-h-[calc(100vh-8rem)]"
        x-data="tiendaTpv(@js($tpvCfg), @js($cats))"
        x-init="init()"
    >
        <div class="lg:flex lg:gap-4 lg:items-start">
            {{-- Zona principal: búsqueda + grid --}}
            <div class="flex-1 min-w-0 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <input
                        type="text"
                        x-ref="searchInput"
                        x-model="q"
                        @input.debounce.280ms="buscar()"
                        @keydown.enter.prevent="onEnterBusqueda()"
                        placeholder="Nombre o escanear código (Enter)…"
                        class="input-premium flex-1 text-base min-h-[48px]"
                        autocomplete="off"
                    />
                    <button type="button" @click="buscar()" class="btn-accent min-h-[48px] px-6 shrink-0">Buscar</button>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <button
                        type="button"
                        @click="categoria = ''; buscar()"
                        :class="categoria === '' ? 'ring-2 ring-[color:var(--color-accent)]' : ''"
                        class="rounded-full px-3 py-1.5 text-xs font-bold border border-slate-200 bg-white hover:bg-slate-50"
                    >Todas</button>
                    <template x-for="[key, label] in Object.entries(categorias || {})" :key="key">
                        <button
                            type="button"
                            @click="categoria = key; buscar()"
                            :class="categoria === key ? 'ring-2 ring-[color:var(--color-accent)]' : ''"
                            class="rounded-full px-3 py-1.5 text-xs font-bold border border-slate-200 bg-white hover:bg-slate-50"
                            x-text="label"
                        ></button>
                    </template>
                </div>

                <div x-show="loading" class="text-sm text-slate-500">Cargando…</div>
                <div x-show="errorMsg" x-cloak class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900" x-text="errorMsg"></div>
                <div x-show="okMsg" x-cloak class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900" x-text="okMsg"></div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-3">
                    <template x-for="p in productos" :key="p.id">
                        <button
                            type="button"
                            @click="addToCart(p)"
                            :disabled="p.stock_actual < 1"
                            class="card-premium text-left p-2 sm:p-3 border border-slate-100 hover:border-[color:var(--color-accent)]/50 hover:shadow-md transition active:scale-[0.98] disabled:opacity-40 disabled:pointer-events-none rounded-xl bg-white"
                        >
                            <div class="aspect-square rounded-lg bg-slate-100 overflow-hidden mb-2 flex items-center justify-center">
                                <template x-if="p.imagen_url">
                                    <img :src="p.imagen_url" :alt="p.nombre" class="w-full h-full object-cover" loading="lazy" />
                                </template>
                                <template x-if="!p.imagen_url">
                                    <span class="text-2xl font-bold text-slate-300" x-text="(p.nombre || '?').charAt(0)"></span>
                                </template>
                            </div>
                            <p class="text-xs sm:text-sm font-bold text-[color:var(--color-primary)] line-clamp-2 leading-tight" x-text="p.nombre"></p>
                            <p class="mt-1 text-sm sm:text-base font-bold tabular-nums text-[color:var(--color-accent)]" x-text="formatMoney(p.precio_venta)"></p>
                            <p class="text-[10px] text-slate-500 mt-0.5">Stock <span x-text="p.stock_actual"></span></p>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Carrito lateral / panel --}}
            <aside class="mt-4 lg:mt-0 w-full lg:w-[22rem] xl:w-[24rem] shrink-0 card-premium border-t-2 border-t-[color:var(--color-accent)] rounded-xl bg-white shadow-sm p-4 space-y-4 lg:sticky lg:top-4 max-h-[calc(100vh-6rem)] flex flex-col">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[color:var(--color-primary)]">Carrito</h2>

                <div class="space-y-2 overflow-y-auto flex-1 min-h-[120px] pr-1">
                    <template x-if="cartLines.length === 0">
                        <p class="text-sm text-slate-500">Pulse un producto para añadirlo.</p>
                    </template>
                    <template x-for="line in cartLines" :key="line.id">
                        <div class="flex gap-2 items-center border border-slate-100 rounded-lg p-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-[color:var(--color-primary)] truncate" x-text="line.nombre"></p>
                                <p class="text-[10px] text-slate-500 tabular-nums" x-text="formatMoney(line.precio_venta)+' × '+line.qty"></p>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" @click="decLine(line.id)" class="h-8 w-8 rounded-full border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold">−</button>
                                <span class="w-6 text-center text-sm font-bold tabular-nums" x-text="line.qty"></span>
                                <button type="button" @click="incLine(line.id)" class="h-8 w-8 rounded-full border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold">+</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-3">
                    <p class="text-lg font-bold text-[color:var(--color-primary)] tabular-nums">Total <span x-text="formatMoney(cartTotal)"></span></p>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" x-model="ventaAnonima" class="rounded border-slate-300 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" />
                            <span>Venta anónima (público general)</span>
                        </label>
                        <div x-show="!ventaAnonima" x-cloak class="space-y-1">
                            <input
                                type="text"
                                x-model="hermanoQ"
                                @input.debounce.300ms="buscarHermanos()"
                                placeholder="Buscar hermano (nombre, DNI, n.º)…"
                                class="input-premium w-full text-sm"
                            />
                            <select x-model.number="hermanoId" class="input-premium w-full text-sm">
                                <option :value="null">— Seleccionar —</option>
                                <template x-for="h in hermanosOpts" :key="h.id">
                                    <option :value="h.id" x-text="h.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="rounded-lg bg-slate-50 border border-slate-100 p-2 space-y-2">
                        <p class="text-[10px] font-bold uppercase text-slate-500">Reserva portal (UUID)</p>
                        <div class="flex gap-1">
                            <input type="text" x-model="pedidoUuid" placeholder="Pegar código…" class="input-premium flex-1 text-xs font-mono" />
                            <button type="button" @click="cargarPedido()" class="btn-soft text-xs px-2 shrink-0">Cargar</button>
                        </div>
                        <template x-if="pedidoPreview">
                            <div class="text-xs text-slate-700 space-y-1">
                                <p><span class="font-semibold">Estado:</span> <span x-text="pedidoPreview.estado"></span></p>
                                <p><span class="font-semibold">Total:</span> <span x-text="formatMoney(pedidoPreview.total_ttc)"></span></p>
                                <p x-show="pedidoPreview.hermano"><span class="font-semibold">Hermano:</span> <span x-text="pedidoPreview.hermano ? ('N.º '+pedidoPreview.hermano.numero_hermano+' — '+pedidoPreview.hermano.nombre) : ''"></span></p>
                                <div class="flex flex-wrap gap-1 pt-1">
                                    <button type="button" @click="checkoutPedido('efectivo')" class="btn-accent text-[10px] py-1.5 px-2">Efectivo</button>
                                    <button type="button" @click="checkoutPedido('tarjeta')" class="btn-soft text-[10px] py-1.5 px-2">Tarjeta</button>
                                    <button type="button" @click="checkoutPedido('bizum')" class="btn-soft text-[10px] py-1.5 px-2">Bizum</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="checkout('efectivo')" :disabled="cartLines.length === 0" class="btn-accent text-[10px] sm:text-xs py-3 disabled:opacity-40">Efectivo</button>
                        <button type="button" @click="checkout('tarjeta')" :disabled="cartLines.length === 0" class="btn-soft text-[10px] sm:text-xs py-3 border border-slate-200 disabled:opacity-40">Tarjeta</button>
                        <button type="button" @click="checkout('bizum')" :disabled="cartLines.length === 0" class="btn-soft text-[10px] sm:text-xs py-3 border border-slate-200 disabled:opacity-40">Bizum</button>
                    </div>
                    <button type="button" @click="clearCart()" class="w-full text-xs text-slate-500 hover:text-rose-600 py-1">Vaciar carrito</button>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('tiendaTpv', (urls, categorias) => ({
                urls,
                categorias,
                q: '',
                categoria: '',
                productos: [],
                loading: false,
                errorMsg: '',
                okMsg: '',
                cart: {},
                ventaAnonima: true,
                hermanoId: null,
                hermanoQ: '',
                hermanosOpts: [],
                pedidoUuid: '',
                pedidoPreview: null,
                csrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                },
                formatMoney(n) {
                    return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(Number(n) || 0);
                },
                get cartLines() {
                    const lines = [];
                    for (const id of Object.keys(this.cart)) {
                        const qty = this.cart[id];
                        if (qty < 1) continue;
                        const p = this.productos.find((x) => String(x.id) === String(id));
                        if (p) lines.push({ id: p.id, nombre: p.nombre, precio_venta: p.precio_venta, qty, stock: p.stock_actual });
                    }
                    return lines;
                },
                get cartTotal() {
                    return this.cartLines.reduce((s, l) => s + l.precio_venta * l.qty, 0);
                },
                init() {
                    this.buscar();
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                },
                async buscar() {
                    this.loading = true;
                    this.errorMsg = '';
                    try {
                        const u = new URL(this.urls.productos);
                        if (this.q.trim()) u.searchParams.set('q', this.q.trim());
                        if (this.categoria) u.searchParams.set('categoria', this.categoria);
                        const r = await fetch(u.toString(), { headers: { Accept: 'application/json' } });
                        const j = await r.json();
                        this.productos = j.productos || [];
                    } catch (e) {
                        this.errorMsg = 'No se pudo cargar el catálogo.';
                    } finally {
                        this.loading = false;
                    }
                },
                async onEnterBusqueda() {
                    const s = this.q.trim();
                    if (!s) return;
                    try {
                        const u = new URL(this.urls.productos);
                        u.searchParams.set('sku', s);
                        const r = await fetch(u.toString(), { headers: { Accept: 'application/json' } });
                        const j = await r.json();
                        const list = j.productos || [];
                        if (list.length === 1) {
                            const p = list[0];
                            if (!this.productos.some((x) => String(x.id) === String(p.id))) {
                                this.productos = [p, ...this.productos];
                            }
                            this.addToCart(p);
                            this.q = '';
                            return;
                        }
                    } catch (_) {}
                    this.buscar();
                },
                addToCart(p) {
                    if (!p || p.stock_actual < 1) return;
                    const id = String(p.id);
                    const next = (this.cart[id] || 0) + 1;
                    if (next > p.stock_actual) return;
                    this.cart = { ...this.cart, [id]: next };
                    this.okMsg = '';
                },
                incLine(id) {
                    const p = this.productos.find((x) => String(x.id) === String(id));
                    if (!p) return;
                    const cur = this.cart[String(id)] || 0;
                    if (cur >= p.stock_actual) return;
                    this.cart = { ...this.cart, [String(id)]: cur + 1 };
                },
                decLine(id) {
                    const cur = (this.cart[String(id)] || 0) - 1;
                    const next = { ...this.cart };
                    if (cur < 1) delete next[String(id)];
                    else next[String(id)] = cur;
                    this.cart = next;
                },
                clearCart() {
                    this.cart = {};
                    this.okMsg = '';
                    this.errorMsg = '';
                },
                async buscarHermanos() {
                    if (this.hermanoQ.trim().length < 2) {
                        this.hermanosOpts = [];
                        return;
                    }
                    const u = new URL(this.urls.hermanos);
                    u.searchParams.set('q', this.hermanoQ.trim());
                    const r = await fetch(u.toString(), { headers: { Accept: 'application/json' } });
                    const j = await r.json();
                    this.hermanosOpts = j.hermanos || [];
                    if (this.hermanosOpts.length === 1) this.hermanoId = this.hermanosOpts[0].id;
                },
                itemsPayload() {
                    return this.cartLines.map((l) => ({ producto_id: l.id, cantidad: l.qty }));
                },
                async checkout(metodo) {
                    this.errorMsg = '';
                    this.okMsg = '';
                    const items = this.itemsPayload();
                    if (!items.length) return;
                    const body = {
                        items,
                        metodo_pago: metodo,
                        venta_anonima: this.ventaAnonima,
                        hermano_id: this.ventaAnonima ? null : this.hermanoId,
                    };
                    try {
                        const r = await fetch(this.urls.checkout, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify(body),
                        });
                        const j = await r.json();
                        if (!r.ok) {
                            this.errorMsg = j.error || 'Error al cobrar.';
                            return;
                        }
                        this.okMsg = 'Venta ' + j.folio + ' registrada. Abriendo ticket…';
                        if (j.ticket_url) {
                            window.open(j.ticket_url, '_blank', 'noopener');
                        }
                        this.clearCart();
                        this.buscar();
                    } catch (e) {
                        this.errorMsg = 'Error de red.';
                    }
                },
                async cargarPedido() {
                    this.errorMsg = '';
                    this.pedidoPreview = null;
                    const uuid = this.pedidoUuid.trim();
                    if (!uuid) return;
                    try {
                        const r = await fetch(this.urls.pedido + '/' + encodeURIComponent(uuid), { headers: { Accept: 'application/json' } });
                        const j = await r.json();
                        if (!r.ok) {
                            this.errorMsg = j.error || 'Pedido no encontrado.';
                            return;
                        }
                        this.pedidoPreview = j.pedido;
                    } catch (e) {
                        this.errorMsg = 'Error al cargar el pedido.';
                    }
                },
                async checkoutPedido(metodo) {
                    this.errorMsg = '';
                    this.okMsg = '';
                    const uuid = (this.pedidoPreview && this.pedidoPreview.uuid) || this.pedidoUuid.trim();
                    if (!uuid) return;
                    try {
                        const r = await fetch(this.urls.checkoutPedido, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify({ pedido_uuid: uuid, metodo_pago: metodo }),
                        });
                        const j = await r.json();
                        if (!r.ok) {
                            this.errorMsg = j.error || 'No se pudo cobrar el pedido.';
                            return;
                        }
                        this.okMsg = 'Pedido cobrado: ' + j.folio + '. Abriendo ticket…';
                        if (j.ticket_url) {
                            window.open(j.ticket_url, '_blank', 'noopener');
                        }
                        this.pedidoPreview = null;
                        this.pedidoUuid = '';
                        this.buscar();
                    } catch (e) {
                        this.errorMsg = 'Error de red.';
                    }
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>
