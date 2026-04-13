<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('cuadrillas.index', ['año' => $cuadrilla->año]) }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Cuadrillas</a>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Avisos de Cuadrilla — {{ $cuadrilla->nombre }}</h1>
        @if (session('status'))<div class="rounded-xl bg-emerald-50 text-emerald-900 text-sm px-4 py-3">{{ session('status') }}</div>@endif

        <form method="post" action="{{ route('cuadrillas.avisos.store', $cuadrilla) }}" class="card-premium p-5 border-t-2 border-t-[color:var(--color-accent)] space-y-2">
            @csrf
            <input class="input-premium w-full" name="titulo" placeholder="Ej: Cambio de hora de ensayo" required>
            <textarea class="input-premium w-full" rows="4" name="mensaje" placeholder="Mensaje al equipo de la cuadrilla" required></textarea>
            <button class="btn-accent text-xs uppercase tracking-wider px-4 py-2">Enviar aviso</button>
        </form>

        <div class="space-y-3">
            @forelse($avisos as $a)
                <article class="card-premium p-4 border border-slate-100">
                    <p class="font-bold text-[color:var(--color-primary)]">{{ $a->titulo }}</p>
                    <p class="text-sm text-slate-700 mt-1 whitespace-pre-line">{{ $a->mensaje }}</p>
                    <p class="text-xs text-slate-500 mt-2">{{ $a->enviado_en?->format('d/m/Y H:i') }} · {{ $a->user?->name ?? 'Sistema' }}</p>
                </article>
            @empty
                <p class="text-slate-500">Sin avisos enviados.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
