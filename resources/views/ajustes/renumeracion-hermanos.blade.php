<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('ajustes.index') }}" class="btn-soft text-xs">Volver a Ajustes</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-xl space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-[color:var(--color-primary)]">Recalcular números de hermano</h2>
                <p class="text-sm text-slate-600 mt-2">Se reasignarán los números correlativos del 1 al N según la <strong>fecha de alta</strong> (más antiguos primero), eliminando huecos dejados por bajas o fichas históricas.</p>
            </div>

            @if (session('error'))
                <div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-xl border-2 border-rose-200 bg-rose-50 p-4 text-sm text-rose-950">
                <p class="font-bold uppercase text-xs tracking-wide mb-2">Acción irreversible</p>
                <p>Tras ejecutarla, deberá actualizar cualquier documento físico o archivo externo que cite el número de hermano. Asegúrese de tener copia de seguridad de la base de datos.</p>
            </div>

            <form method="POST" action="{{ route('ajustes.renumeracion.store') }}" class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-700">Escriba <span class="font-mono bg-slate-100 px-1 rounded">REORDENAR</span> para confirmar</label>
                    <input type="text" name="confirmacion" value="{{ old('confirmacion') }}" class="input-premium mt-1 font-mono" autocomplete="off" required placeholder="REORDENAR">
                    @error('confirmacion')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold bg-rose-700 text-white hover:bg-rose-800">
                        Ejecutar renumeración
                    </button>
                    <a href="{{ route('ajustes.index') }}" class="btn-soft">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
