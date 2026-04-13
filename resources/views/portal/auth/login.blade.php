@extends('layouts.portal')

@section('title', 'Acceso')

@section('content')
    <div class="portal-card">
        <h1 class="portal-heading">Acceso al portal</h1>
        <p class="portal-sub">Introduzca el correo con el que activó su cuenta cofrade.</p>

        <form method="POST" action="{{ route('portal.login') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="portal-label">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="portal-input" />
                @error('email')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="portal-label">Contraseña</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="portal-input" />
                @error('password')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" />
                Recordarme en este dispositivo
            </label>
            <button type="submit" class="portal-btn-primary">
                Entrar al portal
            </button>
        </form>

        <p class="mt-8 border-t border-slate-200 pt-6 text-center space-y-2">
            <span class="block"><a href="{{ route('portal.activacion.codigo') }}" class="portal-link">Tengo código de invitación (primera vez)</a></span>
            <span class="block"><a href="{{ route('portal.recuperar.request') }}" class="portal-link">Olvidé mi contraseña</a></span>
        </p>
    </div>
@endsection
