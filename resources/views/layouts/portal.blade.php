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
<body class="portal-shell pb-[5.75rem] sm:pb-28">
    @php
        $configuracionHermandad = \App\Models\ConfiguracionHermandad::query()->first();
        $escudoHeader = $configuracionHermandad?->escudo_path;
        $escudoHeaderUrl = $escudoHeader
            ? (str_starts_with($escudoHeader, 'http') ? $escudoHeader : \Illuminate\Support\Facades\Storage::url($escudoHeader))
            : null;
        $nombreHermandad = $configuracionHermandad?->nombre_corto
            ?: $configuracionHermandad?->nombre_hermandad
            ?: config('app.name', 'Hermandad');
        $inicialesHermandad = collect(explode(' ', trim((string) $nombreHermandad)))
            ->filter()
            ->take(2)
            ->map(fn ($p) => strtoupper(substr($p, 0, 1)))
            ->implode('');
    @endphp

    <header class="sticky top-0 z-50 border-b border-[color:var(--color-accent)]/35 bg-[color:var(--color-primary)] text-white shadow-md">
        <div class="max-w-lg mx-auto px-4 min-h-14 flex items-center justify-between gap-3 py-2">
            <a href="{{ auth('portal')->check() ? route('portal.inicio') : route('portal.login') }}" class="flex items-center gap-3 min-w-0 group">
                @if ($escudoHeaderUrl)
                    <img src="{{ $escudoHeaderUrl }}" alt="" class="h-10 w-10 shrink-0 rounded-full object-cover border-2 border-[color:var(--color-accent)]/40 shadow-sm" />
                @else
                    <div class="h-10 w-10 shrink-0 rounded-full border-2 border-[color:var(--color-accent)]/50 bg-slate-800 text-[11px] font-bold text-[color:var(--color-accent)] flex items-center justify-center">
                        {{ $inicialesHermandad ?: 'H' }}
                    </div>
                @endif
                <div class="min-w-0 text-left leading-tight">
                    <span class="block font-bold text-sm text-white truncate group-hover:text-[color:var(--color-accent)] transition">{{ $nombreHermandad }}</span>
                    <span class="block text-[10px] uppercase tracking-[0.2em] text-slate-400">Portal del hermano</span>
                </div>
            </a>
            @auth('portal')
                <form method="POST" action="{{ route('portal.logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="rounded-lg px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-300 border border-slate-600 hover:border-[color:var(--color-accent)]/60 hover:text-white transition">
                        Salir
                    </button>
                </form>
            @else
                <a href="{{ route('portal.login') }}" class="shrink-0 rounded-lg px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider bg-[color:var(--color-accent)]/15 text-[color:var(--color-accent)] border border-[color:var(--color-accent)]/40 hover:bg-[color:var(--color-accent)]/25 transition">
                    Entrar
                </a>
            @endauth
        </div>
    </header>

    <main class="max-w-lg mx-auto px-4 py-6 sm:py-8">
        @auth('portal')
            @if (($portalAvisosUrgentesSinLeer ?? 0) > 0)
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-center text-xs font-semibold text-rose-900 shadow-sm">
                    Hay avisos urgentes sin leer.
                    <a href="{{ route('portal.notificaciones.index') }}" class="underline decoration-[color:var(--color-accent)] underline-offset-2">Abrir avisos</a>
                </div>
            @endif
        @endauth
        @if (session('status'))
            <div class="portal-alert-success">
                @if (session('status') === 'verification-link-sent')
                    Se ha enviado un nuevo enlace de verificación a su correo.
                @else
                    {{ session('status') }}
                @endif
            </div>
        @endif
        @if (session('error'))
            <div class="portal-alert-error">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>

    @auth('portal')
        @php
            $navInicio = request()->routeIs('portal.inicio');
            $navTienda = request()->routeIs('portal.tienda.*');
            $navPagos = request()->routeIs('portal.pagos.*');
            $navPerfil = request()->routeIs('portal.perfil.*') || request()->routeIs('portal.papeleta.*') || request()->routeIs('portal.papeletas.*') || request()->routeIs('portal.cuadrilla.*');
            $navAvisos = request()->routeIs('portal.notificaciones.*') || request()->routeIs('portal.avisos-recibidos.*');
            $navDocs = request()->routeIs('portal.documentos.*') || request()->routeIs('portal.firmas.*');
        @endphp
        <nav class="fixed bottom-0 inset-x-0 z-50 border-t-2 border-[color:var(--color-accent)]/30 bg-white shadow-[0_-8px_30px_-12px_rgba(15,23,42,0.18)] pb-[max(0.35rem,env(safe-area-inset-bottom))] pt-1" aria-label="Navegación principal">
            <div class="mx-auto grid max-w-lg min-h-[3.75rem] w-full grid-cols-6 items-stretch px-0.5">
                <a href="{{ route('portal.inicio') }}" class="flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[8px] font-bold uppercase leading-tight tracking-tight sm:text-[9px] sm:tracking-wide {{ $navInicio ? 'text-[color:var(--color-primary)]' : 'text-slate-500 hover:text-slate-800' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border sm:h-10 sm:w-10 {{ $navInicio ? 'border-[color:var(--color-accent)]/60 bg-[color:var(--color-accent)]/15 text-[color:var(--color-primary)]' : 'border-transparent bg-slate-100 text-slate-600' }}">
                        <svg class="h-[1.15rem] w-[1.15rem] sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    </span>
                    <span class="max-w-[3.25rem] sm:max-w-[4rem] truncate text-center">Inicio</span>
                </a>
                <a href="{{ route('portal.tienda.index') }}" class="flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[8px] font-bold uppercase leading-tight tracking-tight sm:text-[9px] sm:tracking-wide {{ $navTienda ? 'text-[color:var(--color-primary)]' : 'text-slate-500 hover:text-slate-800' }}">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border sm:h-9 sm:w-9 {{ $navTienda ? 'border-[color:var(--color-accent)]/60 bg-[color:var(--color-accent)]/15 text-[color:var(--color-primary)]' : 'border-transparent bg-slate-100 text-slate-600' }}">
                        <svg class="h-[1.05rem] w-[1.05rem] sm:h-[1.15rem] sm:w-[1.15rem]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974a1.125 1.125 0 011.119 1.257zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                    </span>
                    <span class="max-w-[3.25rem] sm:max-w-[4rem] truncate text-center leading-tight">Tienda</span>
                </a>
                <a href="{{ route('portal.pagos.index') }}" class="flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[8px] font-bold uppercase leading-tight tracking-tight sm:text-[9px] sm:tracking-wide {{ $navPagos ? 'text-[color:var(--color-primary)]' : 'text-slate-500 hover:text-slate-800' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border sm:h-10 sm:w-10 {{ $navPagos ? 'border-[color:var(--color-accent)]/60 bg-[color:var(--color-accent)]/15 text-[color:var(--color-primary)]' : 'border-transparent bg-slate-100 text-slate-600' }}">
                        <svg class="h-[1.15rem] w-[1.15rem] sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 15h19.5m-16.5-5.25h6.75m-6.75 5.25h6.75m-6.75-10.5h13.5a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25H5.25a2.25 2.25 0 01-2.25-2.25v-10.5a2.25 2.25 0 012.25-2.25z"/></svg>
                    </span>
                    <span class="max-w-[3.25rem] sm:max-w-[4rem] truncate text-center">Pagos</span>
                </a>
                <a href="{{ route('portal.perfil.index') }}" class="flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 py-2 text-[8px] font-bold uppercase leading-tight tracking-tight sm:text-[9px] sm:tracking-wide {{ $navPerfil ? 'text-[color:var(--color-primary)]' : 'text-slate-500 hover:text-slate-800' }}">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border sm:h-10 sm:w-10 {{ $navPerfil ? 'border-[color:var(--color-accent)]/60 bg-[color:var(--color-accent)]/15 text-[color:var(--color-primary)]' : 'border-transparent bg-slate-100 text-slate-600' }}">
                        <svg class="h-[1.15rem] w-[1.15rem] sm:h-5 sm:w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                    </span>
                    <span class="max-w-[3.25rem] sm:max-w-[4rem] truncate text-center">Perfil</span>
                </a>
                <a href="{{ route('portal.notificaciones.index') }}" class="flex min-w-0 flex-col items-center justify-center gap-0.5 py-2 text-[7px] font-bold uppercase leading-tight tracking-tight sm:text-[8px] {{ $navAvisos ? 'text-[color:var(--color-primary)]' : 'text-slate-500 hover:text-slate-800' }}">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border sm:h-9 sm:w-9 {{ $navAvisos ? 'border-[color:var(--color-accent)]/60 bg-[color:var(--color-accent)]/15 text-[color:var(--color-primary)]' : 'border-transparent bg-slate-100 text-slate-600' }}">
                        <svg class="h-[1.05rem] w-[1.05rem] sm:h-[1.15rem] sm:w-[1.15rem]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.082A2.918 2.918 0 0118 14.75v-3.5a2.918 2.918 0 00-1.311-2.25 23.848 23.848 0 00-5.454-1.082M9.75 9.75v.75m0 0v.75m0-.75h.75m-.75 0H9m4.5 0h.008v.008H13.5V9.75zm0 3h.008v.008H13.5V12.75z"/></svg>
                    </span>
                    <span class="max-w-[3.5rem] truncate text-center leading-tight">Avisos</span>
                </a>
                <a href="{{ route('portal.documentos.index') }}" class="flex min-w-0 flex-col items-center justify-center gap-0.5 py-2 text-[8px] font-bold uppercase leading-tight tracking-tight sm:text-[9px] {{ $navDocs ? 'text-[color:var(--color-primary)]' : 'text-slate-500 hover:text-slate-800' }}">
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border sm:h-9 sm:w-9 {{ $navDocs ? 'border-[color:var(--color-accent)]/60 bg-[color:var(--color-accent)]/15 text-[color:var(--color-primary)]' : 'border-transparent bg-slate-100 text-slate-600' }}">
                        <svg class="h-[1.05rem] w-[1.05rem] sm:h-[1.15rem] sm:w-[1.15rem]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                    <span class="max-w-[3.5rem] truncate text-center leading-tight">Docs</span>
                </a>
            </div>
        </nav>
    @endauth

    @auth('portal')
        @if (($portalAvisosUrgentesSinLeer ?? 0) > 0)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (sessionStorage.getItem('ghPortalUrgentPush')) return;
                    sessionStorage.setItem('ghPortalUrgentPush', '1');
                    if (!('Notification' in window)) return;
                    if (Notification.permission === 'granted') {
                        new Notification('Aviso urgente — Hermandad', { body: 'Tiene comunicaciones urgentes pendientes de lectura.', tag: 'gh-urgente' });
                    } else if (Notification.permission !== 'denied') {
                        Notification.requestPermission().then(function (p) {
                            if (p === 'granted') {
                                new Notification('Aviso urgente — Hermandad', { body: 'Revise la sección Avisos del portal.', tag: 'gh-urgente' });
                            }
                        });
                    }
                });
            </script>
        @endif
    @endauth

    @stack('scripts')
</body>
</html>
