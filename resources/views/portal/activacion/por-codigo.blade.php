@extends('layouts.portal')

@section('title', 'Activar con código')

@section('content')
    <div class="portal-card">
        <div class="mb-2 inline-flex rounded-full border border-[color:var(--color-accent)]/40 bg-[color:var(--color-accent)]/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[color:var(--color-primary)]">
            Código de invitación
        </div>
        <h1 class="portal-heading mt-2">Active su acceso</h1>
        <p class="portal-sub">Introduzca el correo al que le llegó la invitación y el <strong>código de 6 dígitos</strong> del email. Luego cree su contraseña.</p>

        <form method="POST" action="{{ route('portal.activacion.codigo.store') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="portal-label">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus autocomplete="email" class="portal-input" inputmode="email" />
                @error('email')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="codigo" class="portal-label">Código (6 dígitos)</label>
                <input id="codigo" name="codigo" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" value="{{ old('codigo') }}" required autocomplete="one-time-code" class="portal-input text-center text-2xl tracking-[0.4em] font-mono font-bold" placeholder="000000" />
                @error('codigo')
                    <p class="mt-2 text-sm font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="portal-label">Contraseña</label>
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
            <button type="submit" class="portal-btn-accent">
                Activar y continuar
            </button>
        </form>

        <p class="mt-8 border-t border-slate-200 pt-6 text-center text-sm text-slate-600">
            ¿Tiene un enlace largo del correo? <a href="{{ route('portal.login') }}" class="portal-link">Volver al acceso</a>
        </p>
    </div>
@endsection
