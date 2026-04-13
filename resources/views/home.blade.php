<x-guest-layout
    tagline="Plataforma de gestión"
    :pageTitle="(config('app.name', 'GestaHer')) . ' — Inicio'"
>
    <h1 class="text-2xl font-bold tracking-tight text-[color:var(--color-primary)]">
        {{ $nombreHermandad }}
    </h1>
    <p class="mt-2 text-sm leading-relaxed text-slate-600">
        Gestión integral de la hermandad: hermanos, economía, secretaría, salidas procesionales y más.
        Elija cómo desea acceder.
    </p>

    <div class="mt-8 flex flex-col gap-3">
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center justify-center rounded-lg bg-[color:var(--color-primary)] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-[color:var(--color-accent)] focus:ring-offset-2"
        >
            Acceder al panel de administración
        </a>
        <a
            href="{{ route('portal.login') }}"
            class="inline-flex items-center justify-center rounded-lg border-2 border-[color:var(--color-accent)]/50 bg-white px-4 py-3 text-sm font-semibold text-[color:var(--color-primary)] transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[color:var(--color-accent)] focus:ring-offset-2"
        >
            Portal del hermano
        </a>
    </div>

    <p class="mt-8 border-t border-slate-200 pt-6 text-xs text-slate-500">
        Junta de gobierno, secretaría y mayordomía: panel de administración. Hermanos: portal cofrade.
    </p>
</x-guest-layout>
