<div
    x-data="{
        query: '',
        results: [],
        open: false,
        mobileOpen: false,
        loading: false,
        debounceTimer: null,
        async search() {
            if (this.query.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }
            this.loading = true;
            const response = await fetch(`{{ route('search.global') }}?q=${encodeURIComponent(this.query)}`);
            const data = await response.json();
            this.results = data.results ?? [];
            this.open = true;
            this.loading = false;
        },
        onInput() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.search(), 220);
        },
        groupedResults() {
            return this.results.reduce((acc, item) => {
                acc[item.category] = acc[item.category] || [];
                acc[item.category].push(item);
                return acc;
            }, {});
        }
    }"
    x-on:keydown.window="(($event.metaKey || $event.ctrlKey) && $event.key.toLowerCase() === 'k') ? ($event.preventDefault(), mobileOpen = true, setTimeout(() => $refs.searchInput?.focus(), 60)) : null"
    class="w-full relative"
>
    <div class="hidden md:block relative">
        <input
            x-ref="searchInput"
            x-model="query"
            @input="onInput"
            @focus="if (query.length > 1) open = true"
            @click.outside="open = false"
            type="text"
            placeholder="Buscar en todo el sistema... (Ctrl/Cmd + K)"
            class="w-full h-11 ps-11 pe-4 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-900 placeholder:text-slate-400 focus:border-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]"
        >
        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
        </svg>

        <div x-show="open" x-transition class="absolute z-50 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl overflow-hidden" style="display:none;">
            <div class="px-4 py-2 border-b border-slate-100 text-xs text-slate-500" x-show="loading">Buscando...</div>
            <template x-for="(items, category) in groupedResults()" :key="category">
                <div class="border-b border-slate-100 last:border-b-0">
                    <div class="px-4 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-500" x-text="category"></div>
                    <template x-for="item in items" :key="item.url + item.title">
                        <a :href="item.url" class="block px-4 py-2 hover:bg-slate-50">
                            <div class="text-sm font-medium text-slate-900" x-text="item.title"></div>
                            <div class="text-xs text-slate-500" x-text="item.subtitle"></div>
                        </a>
                    </template>
                </div>
            </template>
            <div x-show="!loading && results.length === 0" class="px-4 py-3 text-sm text-slate-500">Sin resultados.</div>
        </div>
    </div>

    <div class="md:hidden">
        <button type="button" @click="mobileOpen = true; setTimeout(() => $refs.mobileSearchInput?.focus(), 80)" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-300 bg-white text-slate-700">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="11" cy="11" r="7"></circle>
                <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
            </svg>
        </button>

        <div x-show="mobileOpen" x-transition class="fixed inset-0 z-[60] bg-slate-950/60 p-4" style="display:none;">
            <div class="card-premium p-4">
                <div class="flex items-center gap-2">
                    <input
                        x-ref="mobileSearchInput"
                        x-model="query"
                        @input="onInput"
                        type="text"
                        placeholder="Buscar en todo el sistema..."
                        class="input-premium"
                    >
                    <button type="button" @click="mobileOpen = false; open = false" class="btn-soft">Cerrar</button>
                </div>

                <div class="mt-3 max-h-[70vh] overflow-auto rounded-xl border border-slate-200">
                    <template x-for="(items, category) in groupedResults()" :key="category + '-mobile'">
                        <div class="border-b border-slate-100 last:border-b-0">
                            <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-slate-500" x-text="category"></div>
                            <template x-for="item in items" :key="item.url + item.title + '-mobile'">
                                <a :href="item.url" class="block px-3 py-2 hover:bg-slate-50">
                                    <div class="text-sm font-medium text-slate-900" x-text="item.title"></div>
                                    <div class="text-xs text-slate-500" x-text="item.subtitle"></div>
                                </a>
                            </template>
                        </div>
                    </template>
                    <div x-show="results.length === 0" class="px-3 py-3 text-sm text-slate-500">Sin resultados.</div>
                </div>
            </div>
        </div>
    </div>
</div>
