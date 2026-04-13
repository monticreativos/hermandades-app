@extends('layouts.portal')

@section('title', 'Recuperar acceso')

@section('content')
    <div class="portal-card">
        <h1 class="portal-heading">Recuperar contraseña</h1>
        <p class="portal-sub">Le enviaremos un <strong class="text-[color:var(--color-primary)]">código de 6 dígitos</strong> a su correo (válido 10 minutos).</p>

        <form method="POST" action="{{ route('portal.recuperar.email') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="portal-label">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="portal-input" />
                @error('email')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="portal-btn-primary">
                Enviar código
            </button>
        </form>

        <p class="mt-8 border-t border-slate-200 pt-6 text-center">
            <a href="{{ route('portal.login') }}" class="portal-link-muted font-semibold">← Volver al acceso</a>
        </p>
    </div>
@endsection
