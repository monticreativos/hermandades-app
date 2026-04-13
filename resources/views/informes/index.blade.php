<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Informes y secretaría</h2>
            <p class="text-sm text-slate-600 mt-1">Censo electoral, estadísticas de la cofradía, listados a medida y comunicaciones</p>
        </div>

        @include('informes.partials.subnav')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <a href="{{ route('informes.estadisticas.index') }}" class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 hover:shadow-md transition block group">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] group-hover:text-[color:var(--color-accent)]">Estadísticas y análisis</h3>
                <p class="text-sm text-slate-600 mt-2">Pirámide de edades, altas y bajas, concentración por código postal y KPIs de voto y morosidad (lotería).</p>
                <span class="inline-block mt-4 text-xs font-bold uppercase tracking-wider text-[color:var(--color-accent)]">Abrir →</span>
            </a>
            <a href="{{ route('informes.listados.index') }}" class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 hover:shadow-md transition block group">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] group-hover:text-[color:var(--color-accent)]">Listados a medida (Excel)</h3>
                <p class="text-sm text-slate-600 mt-2">Elija columnas y filtro de estado; descargue un .xlsx para la Junta de Gobierno o auditorías.</p>
                <span class="inline-block mt-4 text-xs font-bold uppercase tracking-wider text-[color:var(--color-accent)]">Abrir →</span>
            </a>
            <a href="{{ route('informes.censo.index') }}" class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 hover:shadow-md transition block group">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] group-hover:text-[color:var(--color-accent)]">Censo de votantes</h3>
                <p class="text-sm text-slate-600 mt-2">Listado según edad, antigüedad y estado de alta; exportación oficial en PDF con DNI protegido (RGPD).</p>
                <span class="inline-block mt-4 text-xs font-bold uppercase tracking-wider text-[color:var(--color-accent)]">Abrir →</span>
            </a>
            <a href="{{ route('informes.etiquetas.index') }}" class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 hover:shadow-md transition block group">
                <h3 class="text-lg font-bold text-[color:var(--color-primary)] group-hover:text-[color:var(--color-accent)]">Etiquetas postales</h3>
                <p class="text-sm text-slate-600 mt-2">Rejilla A4 (3×7), cabezas de familia por domicilio, filtro por código postal y CSV para email marketing.</p>
                <span class="inline-block mt-4 text-xs font-bold uppercase tracking-wider text-[color:var(--color-accent)]">Abrir →</span>
            </a>
        </div>
    </div>
</x-app-layout>
