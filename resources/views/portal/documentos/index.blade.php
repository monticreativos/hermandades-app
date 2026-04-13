@extends('layouts.portal')

@section('title', 'Mis documentos')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-[color:var(--color-primary)]">Mis documentos</h1>
            <p class="text-sm text-slate-600 mt-1">Papeletas históricas, certificados para Hacienda y boletines oficiales.</p>
        </div>

        @if (session('status'))
            <div class="portal-alert-success">{{ session('status') }}</div>
        @endif

        @if ($firmasPendientes->isNotEmpty())
            <section class="portal-card border-l-4 border-amber-400">
                <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-amber-900">Firma pendiente</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($firmasPendientes as $sol)
                        <li>
                            <a href="{{ route('portal.firmas.show', $sol) }}" class="text-sm font-semibold text-[color:var(--color-primary)] hover:text-[color:var(--color-accent)] underline-offset-2 hover:underline">{{ $sol->titulo }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="portal-card">
            <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500 border-b border-slate-100 pb-3">Certificado de cuotas (Hacienda)</h2>
            <p class="text-sm text-slate-600 mt-3">Mismo documento que genera secretaría desde su ficha.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($añosCertificado as $y)
                    <a href="{{ route('portal.documentos.certificado-hacienda', ['año' => $y]) }}" target="_blank" rel="noopener" class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-[color:var(--color-primary)] hover:border-[color:var(--color-accent)]">{{ $y }}</a>
                @endforeach
            </div>
        </section>

        <section class="portal-card">
            <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500 border-b border-slate-100 pb-3">Papeletas de sitio (histórico)</h2>
            @if ($papeletasHistoricas->isEmpty())
                <p class="text-sm text-slate-600 mt-3">No constan papeletas emitidas en años anteriores.</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($papeletasHistoricas as $pap)
                        <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50/80 p-3">
                            <div>
                                <p class="text-sm font-bold text-[color:var(--color-primary)]">Salida {{ $pap->ejercicio?->año ?? '—' }}</p>
                                <p class="text-xs text-slate-600">{{ $pap->puesto }} @if ($pap->tramo) · Tramo {{ $pap->tramo }} @endif</p>
                            </div>
                            <a href="{{ route('portal.papeletas.pdf', $pap) }}" target="_blank" rel="noopener" class="portal-btn-accent text-center text-xs py-2">PDF</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="portal-card">
            <h2 class="text-xs font-bold uppercase tracking-[0.15em] text-slate-500 border-b border-slate-100 pb-3">Boletín de la Hermandad</h2>
            @if ($boletines->isEmpty())
                <p class="text-sm text-slate-600 mt-3">Cuando secretaría publique boletines en PDF, aparecerán aquí.</p>
            @else
                <ul class="mt-4 space-y-2">
                    @foreach ($boletines as $b)
                        <li>
                            <a href="{{ route('portal.documentos.archivo.descargar', $b) }}" class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-[color:var(--color-primary)] hover:border-[color:var(--color-accent)]/50">
                                <span>{{ $b->titulo }}</span>
                                <span class="text-[color:var(--color-accent)] text-xs">Descargar</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
