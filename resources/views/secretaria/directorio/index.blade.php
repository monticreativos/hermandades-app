<x-app-layout>
    <x-slot name="header"><span class="text-xs uppercase tracking-wider text-slate-500">Secretaría · Directorio de contactos</span></x-slot>
    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Directorio Centralizado</h1>
            @if (session('status'))<div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>@endif

            <form method="POST" action="{{ route('secretaria.directorio.store') }}" class="card-premium p-5 grid grid-cols-1 md:grid-cols-3 gap-3 border-t-2 border-t-[color:var(--color-accent)]">
                @csrf
                <input name="nombre" class="input-premium" placeholder="Nombre contacto" required>
                <input name="entidad_institucion" class="input-premium" placeholder="Entidad / Institución">
                <input name="cargo" class="input-premium" placeholder="Cargo">
                <input name="email" class="input-premium" placeholder="Email">
                <input name="telefono" class="input-premium" placeholder="Teléfono">
                <input name="direccion" class="input-premium" placeholder="Dirección">
                <select name="categoria" class="input-premium">
                    @foreach($categorias as $cat)<option>{{ $cat }}</option>@endforeach
                </select>
                <input name="tags" class="input-premium md:col-span-2" placeholder="Tags separados por coma (prensa, boletín, protocolo)">
                <div class="md:col-span-3"><button class="btn-accent">Guardar contacto</button></div>
            </form>

            <div class="card-premium overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-3 text-left">Nombre</th><th class="px-4 py-3 text-left">Categoría</th><th class="px-4 py-3 text-left">Entidad</th><th class="px-4 py-3 text-left">Contacto</th><th class="px-4 py-3 text-left">Tags</th></tr></thead>
                        <tbody>
                            @forelse($contactos as $c)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-3 font-semibold text-[color:var(--color-primary)]">{{ $c->nombre }}</td>
                                    <td class="px-4 py-3">{{ $c->categoria }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $c->entidad_institucion ?: '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $c->email ?: '-' }} @if($c->telefono) · {{ $c->telefono }} @endif</td>
                                    <td class="px-4 py-3 text-xs">@foreach($c->tags as $t)<span class="px-2 py-1 rounded-full bg-slate-100 mr-1">{{ $t->nombre }}</span>@endforeach</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Sin contactos externos aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $contactos->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
