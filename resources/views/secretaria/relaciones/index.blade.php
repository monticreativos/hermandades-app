<x-app-layout>
    <x-slot name="header"><span class="text-xs uppercase tracking-wider text-slate-500">Secretaría · Relaciones institucionales</span></x-slot>
    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Invitados y protocolo institucional</h1>
            @if (session('status'))<div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>@endif

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                <form method="POST" action="{{ route('secretaria.relaciones.entidades.store') }}" class="card-premium p-6 space-y-3 border-t-2 border-t-[color:var(--color-accent)]">
                    @csrf
                    <h2 class="font-semibold text-[color:var(--color-primary)]">Entidad externa</h2>
                    <input name="nombre" class="input-premium w-full" placeholder="Nombre" required />
                    <input name="tipo" class="input-premium w-full" placeholder="Tipo (Hermandad, Ayuntamiento, Consejo...)" required />
                    <input name="contacto" class="input-premium w-full" placeholder="Contacto" />
                    <button class="btn-accent">Guardar entidad</button>
                </form>

                <form method="POST" action="{{ route('secretaria.relaciones.actos.store') }}" class="card-premium p-6 space-y-3">
                    @csrf
                    <h2 class="font-semibold text-[color:var(--color-primary)]">Acto protocolario</h2>
                    <input name="titulo" class="input-premium w-full" placeholder="Título acto" required />
                    <input type="date" name="fecha" class="input-premium w-full" required />
                    <input name="lugar" class="input-premium w-full" placeholder="Lugar" />
                    <button class="btn-soft">Crear acto</button>
                </form>
            </div>

            <form method="POST" action="{{ route('secretaria.relaciones.invitaciones.store') }}" class="card-premium p-6 grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <select name="acto_id" class="input-premium" required>@foreach($actos as $a)<option value="{{ $a->id }}">{{ $a->titulo }} ({{ $a->fecha?->format('d/m/Y') }})</option>@endforeach</select>
                <select name="contacto_externo_id" class="input-premium"><option value="">Contacto externo</option>@foreach($contactosExternos as $c)<option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->categoria }})</option>@endforeach</select>
                <select name="hermano_id" class="input-premium"><option value="">Hermano</option>@foreach($hermanos as $h)<option value="{{ $h->id }}">#{{ $h->numero_hermano }} {{ $h->apellidos }}, {{ $h->nombre }}</option>@endforeach</select>
                <input name="nombre_invitado" class="input-premium" placeholder="Nombre invitado" required />
                <select name="estado_confirmacion" class="input-premium"><option value="pendiente">Pendiente</option><option value="confirmado">Confirmado</option><option value="declinado">Declinado</option></select>
                <input type="number" min="1" name="fila" class="input-premium" placeholder="Fila" />
                <input type="number" min="1" name="banco" class="input-premium" placeholder="Banco" />
                <input type="number" min="1" name="orden_protocolo" class="input-premium" placeholder="Orden protocolo" />
                <div><button class="btn-accent">Añadir invitación</button></div>
            </form>

            <form method="POST" action="{{ route('secretaria.relaciones.invitaciones.categoria') }}" class="card-premium p-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <select name="acto_id" class="input-premium" required>@foreach($actos as $a)<option value="{{ $a->id }}">{{ $a->titulo }} ({{ $a->fecha?->format('d/m/Y') }})</option>@endforeach</select>
                <select name="categoria" class="input-premium" required>
                    <option value="">Categoría de contactos</option>
                    @foreach($contactosExternos->pluck('categoria')->filter()->unique()->values() as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                </select>
                <div><button class="btn-soft">Añadir categoría masivamente</button></div>
            </form>

            <div class="card-premium p-4 space-y-3">
                @forelse($actos as $acto)
                    <div class="border border-slate-200 rounded-xl p-4">
                        <p class="font-semibold text-[color:var(--color-primary)]">{{ $acto->titulo }} · {{ $acto->fecha?->format('d/m/Y') }}</p>
                        <ul class="mt-2 text-sm text-slate-700">
                            @forelse($acto->invitaciones as $inv)
                                <li>{{ $inv->nombre_invitado }} — {{ $inv->estado_confirmacion }} · F{{ $inv->fila ?? '-' }}/B{{ $inv->banco ?? '-' }}/O{{ $inv->orden_protocolo ?? '-' }} @if($inv->categoria_fuente) · {{ $inv->categoria_fuente }} @endif</li>
                            @empty
                                <li class="text-slate-400">Sin invitados.</li>
                            @endforelse
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Sin actos cargados.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
