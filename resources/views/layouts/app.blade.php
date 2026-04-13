<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'GestaHerSevilla') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased bg-[color:var(--color-bg)] text-[color:var(--color-text)]">
        @php
            $configuracionHermandad = \App\Models\ConfiguracionHermandad::query()->first();
            $escudoSidebar = $configuracionHermandad?->escudo_path;
            $escudoSidebarUrl = $escudoSidebar
                ? (str_starts_with($escudoSidebar, 'http') ? $escudoSidebar : \Illuminate\Support\Facades\Storage::url($escudoSidebar))
                : null;
            $nombreSidebar = $configuracionHermandad?->nombre_corto
                ?: $configuracionHermandad?->nombre_hermandad
                ?: 'GestaHerSevilla';
            $inicialesHermandad = collect(explode(' ', trim((string) $nombreSidebar)))
                ->filter()
                ->take(2)
                ->map(fn ($parte) => strtoupper(substr($parte, 0, 1)))
                ->implode('');
        @endphp

        <div x-data="{ moreOpen: false }" class="min-h-screen bg-[color:var(--color-bg)]">
            <!-- Desktop layout -->
            <div class="hidden md:flex">
                <aside class="w-72 fixed inset-y-0 bg-[color:var(--color-primary)] border-r border-slate-800">
                    <div class="h-full flex flex-col">
                        <div class="px-4 py-6 flex items-center justify-between border-b border-slate-700">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                                @if ($escudoSidebarUrl)
                                    <img src="{{ $escudoSidebarUrl }}" alt="Escudo Hermandad" class="h-10 w-10 rounded-full object-cover border border-slate-600">
                                @else
                                    <div class="h-10 w-10 rounded-full border border-slate-500 bg-slate-800 text-slate-100 text-xs font-bold flex items-center justify-center">
                                        {{ $inicialesHermandad ?: 'GH' }}
                                    </div>
                                @endif
                                <div class="leading-tight">
                                    <div class="text-sm font-bold text-slate-100">{{ $nombreSidebar }}</div>
                                    <div class="text-xs text-slate-300">Panel Administrativo</div>
                                </div>
                            </a>
                        </div>

                        <nav class="px-3 py-5 flex-1 overflow-y-auto">
                            <ul class="space-y-1">
                                <li>
                                    <a href="{{ route('hermanos.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-100 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('hermanos.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                        Hermanos
                                    </a>
                                </li>
                                @if (Auth::user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']))
                                    @php
                                        $solicitudesPortalPendientes = \App\Models\SolicitudCambioDatos::query()
                                            ->where('estado', \App\Models\SolicitudCambioDatos::ESTADO_PENDIENTE)
                                            ->count();
                                    @endphp
                                    <li>
                                        <a href="{{ route('secretaria.solicitudes-cambio.index') }}" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-100 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.solicitudes-cambio.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            <span>Solicitudes portal</span>
                                            @if ($solicitudesPortalPendientes > 0)
                                                <span class="shrink-0 min-w-[1.25rem] text-center rounded-full bg-amber-500 text-[10px] font-bold text-slate-900 px-1.5 py-0.5">{{ $solicitudesPortalPendientes }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.avisos.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.avisos.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Avisos hermanos
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.comunicados-masivos.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.comunicados-masivos.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Comunicados (email)
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.archivo-digital.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.archivo-digital.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Archivo digital
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.registro.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.registro.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Registro entrada/salida
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.plantillas.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.plantillas.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Plantillas oficiales
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.relaciones.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.relaciones.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Invitados y protocolo
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.directorio.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.directorio.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Directorio contactos
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('secretaria.firmas-conformidad.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('secretaria.firmas-conformidad.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Firmas conformidad
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()?->hasPermissionTo('contabilidad.gestion'))
                                    <li>
                                        <a href="{{ route('economia.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('economia.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Economía
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()?->hasPermissionTo('tienda.gestion'))
                                    <li>
                                        <a href="{{ route('tienda.tpv') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('tienda.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Tienda / TPV
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a href="{{ route('salida.papeletas.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('salida.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                        Est. Penitencia
                                    </a>
                                </li>
                                @if (Auth::user()?->hasPermissionTo('cuadrillas.gestion'))
                                    <li>
                                        <a href="{{ route('cuadrillas.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('cuadrillas.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Cuadrillas
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']))
                                    <li>
                                        <a href="{{ route('informes.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('informes.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Informes
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 border-transparent">
                                            Informes
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()?->hasPermissionTo('patrimonio.gestion'))
                                    <li>
                                        <a href="{{ route('patrimonio.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('patrimonio.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Patrimonio
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 border-transparent">
                                        Perfil
                                    </a>
                                </li>
                                @if (Auth::user()?->hasAnyRole(['Administrador Hermandad', 'SuperAdmin']))
                                    <li>
                                        <a href="{{ route('ajustes.index') }}" class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium text-slate-200 hover:bg-slate-700/70 border-l-2 {{ request()->routeIs('ajustes.*') ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)]' : 'border-transparent' }}">
                                            Ajustes
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </nav>

                        <div class="p-4 border-t border-slate-700">
                            <div class="rounded-xl bg-slate-800/70 p-3 border border-slate-700">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-[color:var(--color-accent)] text-white text-sm font-bold flex items-center justify-center">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-slate-100 truncate">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-slate-300 truncate">{{ Auth::user()->email }}</div>
                                    </div>
                                </div>

                                <div class="mt-3 flex gap-2">
                                    <a href="{{ route('profile.edit') }}" class="flex-1 text-center text-xs px-3 py-2 rounded-xl border border-slate-600 text-slate-100 hover:bg-slate-700">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-600 text-slate-100 hover:bg-slate-700">
                                            Salir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="md:ml-72 flex-1 min-w-0">
                    @isset($header)
                        <header class="bg-white shadow-sm border-b border-slate-200">
                            <div class="w-full py-3 px-4 sm:px-6 lg:px-8">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <x-search-bar />
                                    </div>
                                    <div class="shrink-0">
                                        {{ $header }}
                                    </div>
                                </div>
                            </div>
                        </header>
                    @endisset

                    <main class="p-4 sm:p-6 lg:p-8">
                        {{ $slot }}
                    </main>
                </div>
            </div>

            <!-- Mobile layout -->
            <div class="md:hidden">
                @isset($header)
                    <header class="bg-white shadow-sm">
                        <div class="w-full py-3 px-4">
                            <div class="flex items-center justify-between gap-2">
                                <x-search-bar />
                                <div class="shrink-0">
                                    {{ $header }}
                                </div>
                            </div>
                        </div>
                    </header>
                @endisset

                <main class="pb-20">
                    {{ $slot }}
                </main>

                <!-- Bottom Navigation -->
                <nav class="fixed bottom-0 left-0 right-0 bg-[color:var(--color-primary)] border-t border-slate-700 z-40">
                    <div class="grid grid-cols-5">
                        <a href="{{ route('dashboard') }}" class="py-3 flex flex-col items-center gap-1 text-xs font-medium text-slate-200 hover:bg-slate-800">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z" />
                                <path d="M9 22V12h6v10" />
                            </svg>
                            Inicio
                        </a>

                        <a href="{{ route('hermanos.index') }}" class="py-3 flex flex-col items-center gap-1 text-xs font-medium text-slate-200 hover:bg-slate-800 {{ request()->routeIs('hermanos.*') ? 'bg-slate-800/80' : '' }}">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="8.5" cy="7" r="4" />
                                <path d="M20 8v6" />
                                <path d="M23 11h-6" />
                            </svg>
                            Hermanos
                        </a>

                        @if (Auth::user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']))
                            <a href="{{ route('informes.index') }}" class="py-3 flex flex-col items-center gap-1 text-xs font-medium text-slate-200 hover:bg-slate-800 {{ request()->routeIs('informes.*') ? 'bg-slate-800/80' : '' }}">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="8" y1="13" x2="16" y2="13" />
                                    <line x1="8" y1="17" x2="14" y2="17" />
                                </svg>
                                Informes
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="py-3 flex flex-col items-center gap-1 text-xs font-medium text-slate-200 hover:bg-slate-800">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="8" y1="13" x2="16" y2="13" />
                                    <line x1="8" y1="17" x2="14" y2="17" />
                                </svg>
                                Informes
                            </a>
                        @endif

                        @if (Auth::user()?->hasPermissionTo('contabilidad.gestion'))
                            <a href="{{ route('economia.libro-diario.index') }}" class="py-3 flex flex-col items-center gap-1 text-xs font-medium text-slate-200 hover:bg-slate-800 {{ request()->routeIs('economia.*') ? 'bg-slate-800/80' : '' }}">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 1v22" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                Economía
                            </a>
                        @else
                            <span class="py-3 flex flex-col items-center gap-1 text-xs font-medium text-slate-500 cursor-not-allowed opacity-60">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 1v22" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                                Economía
                            </span>
                        @endif

                        <button @click="moreOpen = true" type="button" class="py-3 flex flex-col items-center gap-1 text-xs font-medium text-slate-200 hover:bg-slate-800">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14" />
                                <path d="M5 12h14" />
                            </svg>
                            Más
                        </button>
                    </div>
                </nav>

                <!-- Drawer "Más" -->
                <div
                    x-show="moreOpen"
                    x-transition.opacity
                    class="fixed inset-0 z-50"
                    style="display: none;"
                >
                    <div class="absolute inset-0 bg-black/30" @click="moreOpen = false"></div>

                    <aside
                        class="absolute top-0 right-0 h-full w-80 bg-white border-l border-gray-200 shadow-xl"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="translate-x-4 opacity-0"
                        x-transition:enter-end="translate-x-0 opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="translate-x-0 opacity-100"
                        x-transition:leave-end="translate-x-4 opacity-0"
                        @keydown.escape.window="moreOpen = false"
                    >
                        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Más opciones</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->name }}</div>
                            </div>
                            <button @click="moreOpen = false" class="px-3 py-2 rounded-md border border-gray-200 text-gray-700 text-sm hover:bg-gray-50" type="button">
                                Cerrar
                            </button>
                        </div>

                        <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100%-72px)]">
                            @if (Auth::user()?->hasPermissionTo('patrimonio.gestion'))
                                <a href="{{ route('patrimonio.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Patrimonio
                                </a>
                            @endif
                            @if (Auth::user()?->hasPermissionTo('contabilidad.gestion'))
                                <a href="{{ route('economia.dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Contabilidad
                                </a>
                            @endif
                            @if (Auth::user()?->hasPermissionTo('tienda.gestion'))
                                <a href="{{ route('tienda.tpv') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Tienda / TPV
                                </a>
                            @endif
                            @if (Auth::user()?->hasPermissionTo('cuadrillas.gestion'))
                                <a href="{{ route('cuadrillas.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cuadrillas
                                </a>
                            @endif
                            @if (Auth::user()?->hasAnyRole(['Secretaría', 'Administrador Hermandad', 'SuperAdmin']))
                                <a href="{{ route('informes.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Informes y censo
                                </a>
                                <a href="{{ route('secretaria.solicitudes-cambio.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Solicitudes portal (cambio datos)
                                </a>
                                <a href="{{ route('secretaria.avisos.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Avisos a hermanos
                                </a>
                                <a href="{{ route('secretaria.comunicados-masivos.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Comunicados por email (masivos)
                                </a>
                                <a href="{{ route('secretaria.archivo-digital.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Archivo digital
                                </a>
                                <a href="{{ route('secretaria.registro.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Registro entrada/salida
                                </a>
                                <a href="{{ route('secretaria.plantillas.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Plantillas oficiales
                                </a>
                                <a href="{{ route('secretaria.relaciones.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Invitados y protocolo
                                </a>
                                <a href="{{ route('secretaria.directorio.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Directorio contactos
                                </a>
                                <a href="{{ route('secretaria.firmas-conformidad.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Firmas de conformidad
                                </a>
                            @endif
                            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Inventario
                            </a>
                            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Perfil
                            </a>
                            @if (Auth::user()?->hasAnyRole(['Administrador Hermandad', 'SuperAdmin']))
                                <a href="{{ route('ajustes.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Ajustes
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="pt-2">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cerrar sesión
                                </button>
                            </form>
                        </nav>
                    </aside>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
