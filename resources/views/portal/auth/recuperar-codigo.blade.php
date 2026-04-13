@extends('layouts.portal')

@section('title', 'Introducir código')

@section('content')
    <div class="portal-card">
        <h1 class="portal-heading">Código de verificación</h1>
        <p class="portal-sub">Correo: <strong class="text-[color:var(--color-primary)]">{{ $email }}</strong></p>

        <form method="POST" action="{{ route('portal.recuperar.restablecer') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="codigo" class="portal-label">Código (6 dígitos)</label>
                <input id="codigo" name="codigo" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="one-time-code"
                    class="portal-input text-center font-mono text-2xl tracking-[0.45em]" placeholder="······" />
                @error('codigo')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="portal-label">Nueva contraseña</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="portal-input" />
                <p class="mt-1.5 text-xs text-slate-500">Mínimo 10 caracteres, mayúsculas, minúsculas y números.</p>
                @error('password')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="portal-label">Confirmar contraseña</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="portal-input" />
            </div>
            <button type="submit" class="portal-btn-primary">
                Guardar nueva contraseña
            </button>
        </form>

        <p class="mt-8 border-t border-slate-200 pt-6 text-center">
            <a href="{{ route('portal.recuperar.request') }}" class="portal-link">Solicitar otro código</a>
        </p>
    </div>
@endsection
