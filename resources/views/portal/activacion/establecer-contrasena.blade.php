@extends('layouts.portal')

@section('title', 'Activar acceso')

@section('content')
    <div class="portal-card">
        <div class="mb-2 inline-flex rounded-full border border-[color:var(--color-accent)]/40 bg-[color:var(--color-accent)]/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[color:var(--color-primary)]">
            Primera vez
        </div>
        <h1 class="portal-heading mt-2">Bienvenido al portal</h1>
        <p class="portal-sub">Establezca una contraseña segura para <strong class="text-[color:var(--color-primary)]">{{ $email }}</strong>. Después deberá verificar el correo electrónico.</p>
        <p class="mt-3 text-xs text-slate-500">Si prefiere usar el código de 6 dígitos del mismo email, puede <a href="{{ route('portal.activacion.codigo') }}" class="portal-link font-semibold">activar por código</a>.</p>

        <form method="POST" action="{{ route('portal.activar.store') }}" class="mt-8 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />
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
    </div>
@endsection
