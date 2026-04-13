@extends('layouts.portal')

@section('title', 'Papeleta de sitio')

@section('content')
    <div class="space-y-5">
        <div class="portal-card">
            <h1 class="portal-heading">Papeleta de sitio {{ $campaña->año }}</h1>
            <p class="portal-sub">La asignación de puesto y el cobro del donativo los gestiona secretaría en el panel administrativo.</p>
        </div>

        @if ($papeletaExistente)
            <div class="portal-card">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Su registro</h2>
                <p class="mt-3 text-base font-bold text-[color:var(--color-primary)]">Estado: {{ $papeletaExistente->estado }}</p>
                @if ($papeletaExistente->puesto)
                    <p class="mt-1 text-sm text-slate-700">Puesto: <span class="font-semibold">{{ $papeletaExistente->puesto }}</span></p>
                @endif
                @if ($papeletaExistente->estado === \App\Models\PapeletaSitio::ESTADO_EMITIDA)
                    <a href="{{ route('portal.papeletas.pdf', $papeletaExistente) }}" target="_blank" rel="noopener" class="portal-btn-accent mt-6">Descargar PDF</a>
                @elseif ($papeletaExistente->estado === \App\Models\PapeletaSitio::ESTADO_SOLICITADA)
                    <p class="mt-3 text-sm text-amber-800">Solicitud registrada. Espere a que secretaría confirme y emita la papeleta.</p>
                @endif
            </div>
        @else
            <div class="portal-card-muted text-left !text-sm">
                <p class="font-semibold text-[color:var(--color-primary)]">¿Cómo sigo?</p>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-slate-700">
                    <li>Pase por secretaría o contacte por los medios oficiales de la hermandad.</li>
                    <li>Secretaría creará o completará su papeleta en el sistema.</li>
                    <li>Tras el pago del donativo y la emisión, podrá descargar el PDF desde el inicio del portal.</li>
                </ol>
                @if ($configuracionHermandad?->telefono)
                    <p class="mt-4 text-slate-800">
                        <span class="font-semibold">Teléfono:</span>
                        <a href="tel:{{ preg_replace('/\s+/', '', $configuracionHermandad->telefono) }}" class="text-[color:var(--color-accent)] font-bold hover:underline">{{ $configuracionHermandad->telefono }}</a>
                    </p>
                @endif
            </div>
        @endif

        <a href="{{ route('portal.inicio') }}" class="portal-btn-outline !flex">Volver al inicio</a>
    </div>
@endsection
