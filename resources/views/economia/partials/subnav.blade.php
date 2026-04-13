@php
    $link = 'flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium border transition';
    $active = 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white';
    $idle = 'border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-200';
@endphp

<nav class="mb-6 flex flex-wrap gap-2" aria-label="Secciones de economía">
    <a href="{{ route('economia.libro-diario.index') }}" class="{{ $link }} {{ request()->routeIs('economia.libro-diario.*') || request()->routeIs('economia.asientos.*') ? $active : $idle }}">
        Libro diario
    </a>
    <a href="{{ route('economia.movimiento-rapido.create') }}" class="{{ $link }} {{ request()->routeIs('economia.movimiento-rapido.*') ? $active : $idle }}">
        Registrar movimiento
    </a>
    @can('contabilidad.gestion')
        <a href="{{ route('economia.cuotas.index') }}" class="{{ $link }} {{ request()->routeIs('economia.cuotas.*') ? $active : $idle }}">
            Generación de cuotas
        </a>
        <a href="{{ route('economia.remesas.index') }}" class="{{ $link }} {{ request()->routeIs('economia.remesas.*') ? $active : $idle }}">
            Remesas SEPA
        </a>
        <a href="{{ route('economia.informes.libro-mayor') }}" class="{{ $link }} {{ request()->routeIs('economia.informes.libro-mayor') ? $active : $idle }}">
            Libro mayor
        </a>
        <a href="{{ route('economia.informes.balance') }}" class="{{ $link }} {{ request()->routeIs('economia.informes.balance') ? $active : $idle }}">
            Balance sumas y saldos
        </a>
        <a href="{{ route('economia.informes.iva-soportado') }}" class="{{ $link }} {{ request()->routeIs('economia.informes.iva-soportado') ? $active : $idle }}">
            IVA
        </a>
        <a href="{{ route('economia.informes.is-auxiliar') }}" class="{{ $link }} {{ request()->routeIs('economia.informes.is-auxiliar') ? $active : $idle }}">
            Auxiliar IS
        </a>
        <a href="{{ route('economia.informes.modelo-182') }}" class="{{ $link }} {{ request()->routeIs('economia.informes.modelo-182*') ? $active : $idle }}">
            Modelo 182
        </a>
        <a href="{{ route('economia.facturas.index') }}" class="{{ $link }} {{ request()->routeIs('economia.facturas.index') || request()->routeIs('economia.documentos-gasto.*') ? $active : $idle }}">
            Facturas
        </a>
        <a href="{{ route('economia.facturas.galeria') }}" class="{{ $link }} {{ request()->routeIs('economia.facturas.galeria') ? $active : $idle }}">
            Galería adjuntos
        </a>
        <a href="{{ route('economia.loterias.index') }}" class="{{ $link }} {{ request()->routeIs('economia.loterias.*') ? $active : $idle }}">
            Lotería
        </a>
        <a href="{{ route('economia.plan-contable.index') }}" class="{{ $link }} {{ request()->routeIs('economia.plan-contable.*') ? $active : $idle }}">
            Plan contable
        </a>
        <a href="{{ route('economia.analisis-deuda.index') }}" class="{{ $link }} {{ request()->routeIs('economia.analisis-deuda.*') ? $active : $idle }}">
            Análisis de deuda
        </a>
        <a href="{{ route('economia.tesoreria.arqueo-mensual') }}" class="{{ $link }} {{ request()->routeIs('economia.tesoreria.*') ? $active : $idle }}">
            Arqueo mensual
        </a>
        <a href="{{ route('economia.informes.historial') }}" class="{{ $link }} {{ request()->routeIs('economia.informes.historial*') ? $active : $idle }}">
            Historial informes
        </a>
    @endcan
</nav>
