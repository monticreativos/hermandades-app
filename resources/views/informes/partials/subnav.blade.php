@php
    $link = 'flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium border transition';
    $active = 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white';
    $idle = 'border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-200';
@endphp

<nav class="mb-6 flex flex-wrap gap-2" aria-label="Secciones de informes">
    <a href="{{ route('informes.index') }}" class="{{ $link }} {{ request()->routeIs('informes.index') ? $active : $idle }}">
        Inicio informes
    </a>
    <a href="{{ route('informes.censo.index') }}" class="{{ $link }} {{ request()->routeIs('informes.censo.*') ? $active : $idle }}">
        Censo electoral
    </a>
    <a href="{{ route('informes.etiquetas.index') }}" class="{{ $link }} {{ request()->routeIs('informes.etiquetas.*') ? $active : $idle }}">
        Etiquetas y mailing
    </a>
    <a href="{{ route('informes.estadisticas.index') }}" class="{{ $link }} {{ request()->routeIs('informes.estadisticas.*') ? $active : $idle }}">
        Estadísticas
    </a>
    <a href="{{ route('informes.listados.index') }}" class="{{ $link }} {{ request()->routeIs('informes.listados.*') ? $active : $idle }}">
        Listados Excel
    </a>
</nav>
