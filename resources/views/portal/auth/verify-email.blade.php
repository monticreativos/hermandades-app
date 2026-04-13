@extends('layouts.portal')

@section('title', 'Verificar correo')

@section('content')
    <div class="portal-card text-center">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full border-2 border-[color:var(--color-accent)]/40 bg-[color:var(--color-accent)]/10 text-[color:var(--color-primary)]">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        </div>
        <h1 class="portal-heading">Confirme su correo</h1>
        <p class="portal-sub">
            Hemos enviado un enlace a <strong class="text-[color:var(--color-primary)]">{{ auth('portal')->user()->email }}</strong>. Debe abrirlo para completar la verificación (obligatoria).
        </p>
        <p class="mt-3 text-xs text-slate-500">Si no ve el mensaje, revise la carpeta de spam.</p>

        <form method="POST" action="{{ route('portal.verification.send') }}" class="mt-8">
            @csrf
            <button type="submit" class="portal-btn-outline">
                Reenviar enlace de verificación
            </button>
        </form>

        <form method="POST" action="{{ route('portal.logout') }}" class="mt-5">
            @csrf
            <button type="submit" class="text-xs font-medium text-slate-500 hover:text-[color:var(--color-primary)] underline underline-offset-2">Cerrar sesión</button>
        </form>
    </div>
@endsection
