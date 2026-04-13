<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('tienda.productos.index') }}" class="text-xs font-semibold uppercase tracking-wider text-slate-600 hover:text-[color:var(--color-primary)]">← Catálogo</a>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 space-y-6">
        <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Editar producto</h1>

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 text-red-800 text-sm px-4 py-3">Revise los campos marcados.</div>
        @endif

        <form method="post" action="{{ route('tienda.productos.update', $producto) }}" enctype="multipart/form-data" class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6 space-y-6">
            @csrf
            @method('PUT')
            @include('tienda.productos._form', ['producto' => $producto])
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-accent uppercase tracking-wider text-xs px-6">Actualizar</button>
                <a href="{{ route('tienda.productos.index') }}" class="btn-soft border border-slate-200 px-6 py-2 rounded-xl text-sm">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
