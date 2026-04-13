@php
    $link = 'flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium border transition';
    $active = 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white';
    $idle = 'border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-200';
@endphp

<nav class="mb-6 flex flex-wrap gap-2" aria-label="Secciones de Estación de Penitencia">
    <a href="{{ route('salida.papeletas.index') }}" class="{{ $link }} {{ request()->routeIs('salida.papeletas.*') ? $active : $idle }}">
        Papeletas de sitio
    </a>
    <a href="{{ route('salida.cortejo.index') }}" class="{{ $link }} {{ request()->routeIs('salida.cortejo.*') ? $active : $idle }}">
        Cortejo
    </a>
    <a href="{{ route('salida.tunicas.index') }}" class="{{ $link }} {{ request()->routeIs('salida.tunicas.*') ? $active : $idle }}">
        Túnicas
    </a>
    <a href="{{ route('salida.configuracion.index') }}" class="{{ $link }} {{ request()->routeIs('salida.configuracion.*') ? $active : $idle }}">
        Configuración
    </a>
</nav>
