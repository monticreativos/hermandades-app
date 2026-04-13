<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GestaHer') }} — Acceso</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh font-sans antialiased bg-[color:var(--color-bg)] text-[color:var(--color-text)]">
    @php
        $configuracionHermandad = \App\Models\ConfiguracionHermandad::query()->first();
        $escudoGuest = $configuracionHermandad?->escudo_path;
        $escudoGuestUrl = $escudoGuest
            ? (str_starts_with($escudoGuest, 'http') ? $escudoGuest : \Illuminate\Support\Facades\Storage::url($escudoGuest))
            : null;
        $nombreGuest = $configuracionHermandad?->nombre_corto
            ?: $configuracionHermandad?->nombre_hermandad
            ?: config('app.name', 'GestaHer');
        $inicialesGuest = collect(explode(' ', trim((string) $nombreGuest)))
            ->filter()
            ->take(2)
            ->map(fn ($p) => strtoupper(substr($p, 0, 1)))
            ->implode('');
    @endphp

    <header class="border-b border-[color:var(--color-accent)]/35 bg-[color:var(--color-primary)] text-white shadow-md">
        <div class="mx-auto flex h-16 max-w-md items-center justify-center gap-3 px-4 sm:max-w-lg">
            @if ($escudoGuestUrl)
                <img src="{{ $escudoGuestUrl }}" alt="" class="h-11 w-11 rounded-full object-cover border-2 border-[color:var(--color-accent)]/40" />
            @else
                <div class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-[color:var(--color-accent)]/50 bg-slate-800 text-xs font-bold text-[color:var(--color-accent)]">
                    {{ $inicialesGuest ?: 'GH' }}
                </div>
            @endif
            <div class="min-w-0 text-left">
                <p class="truncate text-sm font-bold text-white">{{ $nombreGuest }}</p>
                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Panel administrativo</p>
            </div>
        </div>
    </header>

    <div class="flex min-h-[calc(100dvh-4rem)] flex-col justify-center px-4 py-10 sm:py-14">
        <div class="mx-auto w-full max-w-md">
            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </div>
            <p class="mt-6 text-center text-xs text-slate-500">
                ¿Es usted hermano? El acceso al portal cofrade es en
                <a href="{{ url('/portal/login') }}" class="font-semibold text-[color:var(--color-accent)] hover:underline">/portal/login</a>
            </p>
        </div>
    </div>
</body>
</html>
