<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h1 class="text-xl font-bold text-[color:var(--color-primary)] tracking-tight">Acceso administración</h1>
    <p class="mt-1 text-sm text-slate-600">Introduzca sus credenciales de usuario del panel.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="mt-0" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="mt-0" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2 pt-1">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" name="remember">
            <label for="remember_me" class="text-sm text-slate-600">Recordarme en este equipo</label>
        </div>

        <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-center text-sm font-medium text-[color:var(--color-accent)] hover:underline sm:text-left" href="{{ route('password.request') }}">
                    ¿Ha olvidado su contraseña?
                </a>
            @endif

            <x-primary-button class="w-full sm:w-auto sm:min-w-[8rem]">
                Entrar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
