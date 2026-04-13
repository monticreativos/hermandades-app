<x-app-layout>
    <x-slot name="header">
        <button type="button" onclick="document.getElementById('modal-cuadrilla').showModal()" class="btn-accent text-xs uppercase tracking-wider">Nueva cuadrilla</button>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Gestión de Cuadrillas</h1>
        <p class="text-sm text-slate-600">Libreta digital del capataz: igualá, ensayos, relevos y avisos.</p>

        @if (session('status'))
            <div class="rounded-xl bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('status') }}</div>
        @endif

        <form method="get" class="flex items-end gap-2">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Año</label>
                <input type="number" name="año" value="{{ $año }}" class="input-premium w-32" />
            </div>
            <button class="btn-soft border border-slate-200 px-4 py-2 rounded-xl text-sm">Ver</button>
        </form>

        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($cuadrillas as $c)
                <article class="card-premium p-5 border-t-2 border-t-[color:var(--color-accent)]">
                    <p class="text-xs uppercase font-bold text-[color:var(--color-accent)]">{{ strtoupper($c->paso) }} · {{ $c->año }}</p>
                    <h2 class="text-lg font-bold text-[color:var(--color-primary)] mt-1">{{ $c->nombre }}</h2>
                    <p class="text-xs text-slate-500 mt-1">Capataz: {{ $c->capataz?->name ?? '—' }}</p>
                    <p class="text-xs text-slate-500">Costaleros: {{ $c->costaleros_count }}</p>
                    <div class="grid grid-cols-2 gap-2 mt-4 text-xs">
                        <a href="{{ route('cuadrillas.iguala', $c) }}" class="btn-soft border border-slate-200 text-center py-2 rounded-xl">Igualá</a>
                        <a href="{{ route('cuadrillas.ensayos', $c) }}" class="btn-soft border border-slate-200 text-center py-2 rounded-xl">Ensayos</a>
                        <a href="{{ route('cuadrillas.relevos', $c) }}" class="btn-soft border border-slate-200 text-center py-2 rounded-xl">Relevos</a>
                        <a href="{{ route('cuadrillas.avisos', $c) }}" class="btn-soft border border-slate-200 text-center py-2 rounded-xl">Avisos</a>
                    </div>
                </article>
            @empty
                <p class="text-slate-500">Sin cuadrillas para el año indicado.</p>
            @endforelse
        </div>
    </div>

    <dialog id="modal-cuadrilla" class="rounded-xl p-0 w-full max-w-lg backdrop:bg-slate-900/40">
        <form method="post" action="{{ route('cuadrillas.store') }}" class="p-6 space-y-4">
            @csrf
            <h3 class="font-bold text-[color:var(--color-primary)]">Nueva cuadrilla</h3>
            <div class="grid sm:grid-cols-2 gap-3">
                <input class="input-premium" name="año" type="number" value="{{ $año }}" required />
                <select class="input-premium" name="paso" required>
                    <option value="cristo">Cristo</option>
                    <option value="virgen">Virgen</option>
                </select>
                <input class="input-premium sm:col-span-2" name="nombre" placeholder="Nombre cuadrilla" required />
                <input class="input-premium" name="numero_trabajaderas" type="number" value="8" min="1" required />
                <input class="input-premium" name="puestos_por_trabajadera" type="number" value="4" min="2" required />
                <textarea class="input-premium sm:col-span-2" name="notas" rows="2" placeholder="Notas"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="this.closest('dialog').close()" class="btn-soft border border-slate-200 px-4 py-2 rounded-xl text-sm">Cancelar</button>
                <button class="btn-accent text-xs uppercase tracking-wider px-4 py-2">Guardar</button>
            </div>
        </form>
    </dialog>
</x-app-layout>
