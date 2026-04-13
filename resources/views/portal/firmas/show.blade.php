@extends('layouts.portal')

@section('title', 'Firma de conformidad')

@section('content')
    <div class="space-y-5">
        <a href="{{ route('portal.documentos.index') }}" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline">← Mis documentos</a>
        <h1 class="text-xl font-bold text-[color:var(--color-primary)]">{{ $solicitud->titulo }}</h1>

        @if ($errors->any())
            <div class="portal-alert-error text-sm">
                @foreach ($errors->all() as $e)
                    <p>{{ $e }}</p>
                @endforeach
            </div>
        @endif

        <section class="portal-card">
            <div class="text-sm text-slate-700 whitespace-pre-wrap">{{ $solicitud->descripcion }}</div>
            @if ($solicitud->documentoArchivo)
                <a href="{{ route('portal.documentos.archivo.descargar', $solicitud->documentoArchivo) }}" class="mt-4 inline-flex text-sm font-semibold text-[color:var(--color-accent)] hover:underline">Ver documento adjunto</a>
            @endif
        </section>

        @if ($solicitud->estado === \App\Models\FirmaConformidadSolicitud::ESTADO_FIRMADO)
            <div class="portal-alert-success text-sm">
                Ya firmó el {{ $solicitud->firmado_en?->format('d/m/Y H:i') }}.
            </div>
        @else
            <form method="POST" action="{{ route('portal.firmas.firmar', $solicitud) }}" class="portal-card space-y-4">
                @csrf
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="acepto" value="1" class="mt-1 rounded border-slate-300 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" required />
                    <span class="text-sm text-slate-800"><strong>Acepto y firmo</strong> electrónicamente conforme a lo anterior. Entiendo que quedará registrada la fecha y dirección IP de este acceso.</span>
                </label>
                <button type="submit" class="portal-btn-primary w-full sm:w-auto">Registrar firma</button>
            </form>
        @endif
    </div>
@endsection
