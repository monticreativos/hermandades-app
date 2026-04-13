<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del Hermano') — {{ config('app.name', 'GestaHer') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-[color:var(--color-primary)] text-slate-100 antialiased">
    @php
        $configuracionHermandad = \App\Models\ConfiguracionHermandad::query()->first();
        $escudoHeader = $configuracionHermandad?->escudo_path;
        $escudoHeaderUrl = $escudoHeader
            ? (str_starts_with($escudoHeader, 'http') ? $escudoHeader : \Illuminate\Support\Facades\Storage::url($escudoHeader))
            : null;
        $nombreHermandad = $configuracionHermandad?->nombre_corto
            ?: $configuracionHermandad?->nombre_hermandad
            ?: config('app.name', 'Hermandad');
    @endphp

    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none opacity-[0.07]" aria-hidden="true">
        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-[color:var(--color-accent)] blur-3xl"></div>
        <div class="absolute bottom-0 -left-16 h-64 w-64 rounded-full bg-slate-400 blur-3xl"></div>
    </div>

    <div class="relative z-10 min-h-dvh flex flex-col safe-area-pb">
        <header class="shrink-0 px-4 pt-[max(1rem,env(safe-area-inset-top))] pb-2 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                @if ($escudoHeaderUrl)
                    <img src="{{ $escudoHeaderUrl }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover border-2 border-[color:var(--color-accent)]/50 shadow-lg" />
                @endif
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-[0.25em] text-[color:var(--color-accent)]/90 font-bold">Hermandad</p>
                    <p class="font-bold text-sm text-white truncate">{{ $nombreHermandad }}</p>
                </div>
            </div>
            @auth('portal')
                <form method="POST" action="{{ route('portal.logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-300 border border-slate-500 hover:border-[color:var(--color-accent)]/60 hover:text-white transition">
                        Cerrar sesión
                    </button>
                </form>
            @endauth
        </header>

        <main class="flex-1 flex flex-col px-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] pt-2">
            @if (session('status'))
                <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border border-rose-500/40 bg-rose-950/40 px-4 py-3 text-sm text-rose-100">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
