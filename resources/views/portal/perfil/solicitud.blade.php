@extends('layouts.portal')

@section('title', 'Solicitar cambio')

@section('content')
    <div class="portal-card">
        <h1 class="portal-heading">Solicitud de cambio</h1>
        <p class="portal-sub">Rellene solo los campos que desee modificar. Secretaría comparará con el valor actual y aprobará o rechazará.</p>

        <form method="POST" action="{{ route('portal.perfil.solicitud.store') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="direccion" class="portal-label">Nueva dirección</label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" class="portal-input" placeholder="Actual: {{ $hermano->direccion ?: '—' }}" />
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="codigo_postal" class="portal-label">Código postal</label>
                    <input type="text" name="codigo_postal" id="codigo_postal" value="{{ old('codigo_postal') }}" class="portal-input" />
                </div>
                <div>
                    <label for="localidad" class="portal-label">Localidad</label>
                    <input type="text" name="localidad" id="localidad" value="{{ old('localidad') }}" class="portal-input" />
                </div>
            </div>
            <div>
                <label for="provincia" class="portal-label">Provincia</label>
                <input type="text" name="provincia" id="provincia" value="{{ old('provincia') }}" class="portal-input" />
            </div>
            <div>
                <label for="telefono" class="portal-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="portal-input" placeholder="Actual: {{ $hermano->telefono ?: '—' }}" />
            </div>
            <div>
                <label for="email" class="portal-label">Correo electrónico (ficha)</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="portal-input" placeholder="Actual: {{ $hermano->email ?: '—' }}" />
            </div>
            <div>
                <label for="iban" class="portal-label">IBAN</label>
                <input type="text" name="iban" id="iban" value="{{ old('iban') }}" class="portal-input font-mono uppercase" placeholder="ES00…" autocomplete="off" />
                <p class="mt-1 text-xs text-slate-500">Sin espacios. Solo si necesita actualizar la cuenta de domiciliación.</p>
            </div>

            @if ($errors->any())
                <div class="portal-alert-error">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="portal-btn-primary">Enviar solicitud</button>
            <a href="{{ route('portal.perfil.index') }}" class="portal-btn-outline !flex">Cancelar</a>
        </form>
    </div>
@endsection
