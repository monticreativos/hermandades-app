@extends('layouts.portal')

@section('title', 'Mi perfil')

@section('content')
    @php
        $iban = $hermano->iban;
        $ibanMascarado = $iban && strlen($iban) > 4
            ? str_repeat('·', max(0, strlen($iban) - 4)).substr($iban, -4)
            : ($iban ?: '—');
    @endphp

    <div class="space-y-5">
        <div class="portal-card">
            <h1 class="portal-heading">Mis datos</h1>
            <p class="portal-sub">Los cambios deben ser aprobados por secretaría. No se aplican al instante.</p>

            @if ($solicitudPendiente)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950">
                    Tiene una <strong>solicitud de cambio</strong> pendiente (enviada el {{ $solicitudPendiente->created_at->format('d/m/Y H:i') }}).
                </div>
            @endif

            <dl class="mt-6 space-y-4 text-sm">
                <div class="border-b border-slate-100 pb-3">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Nombre</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $hermano->nombreCompleto() }}</dd>
                </div>
                <div class="border-b border-slate-100 pb-3">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Dirección</dt>
                    <dd class="mt-1 text-slate-800">{{ $hermano->direccion ?: '—' }}</dd>
                    <dd class="text-slate-600">{{ $hermano->codigo_postal }} {{ $hermano->localidad }}{{ $hermano->provincia ? ', '.$hermano->provincia : '' }}</dd>
                </div>
                <div class="border-b border-slate-100 pb-3">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Teléfono</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $hermano->telefono ?: '—' }}</dd>
                </div>
                <div class="border-b border-slate-100 pb-3">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Correo</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $hermano->email ?: '—' }}</dd>
                    <dd class="mt-1 text-xs text-slate-500">Acceso portal: {{ $cuenta->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">IBAN (enmascarado)</dt>
                    <dd class="mt-1 font-mono text-sm tracking-tight text-slate-900">{{ $ibanMascarado }}</dd>
                    @if ($hermano->banco)
                        <dd class="mt-0.5 text-xs text-slate-600">{{ $hermano->banco->nombre }}</dd>
                    @endif
                </div>
            </dl>

            @if (! $solicitudPendiente)
                <a href="{{ route('portal.perfil.solicitud.create') }}" class="portal-btn-accent mt-8">Solicitar cambio de datos</a>
            @endif
        </div>
    </div>
@endsection
