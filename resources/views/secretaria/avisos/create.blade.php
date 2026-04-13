<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.avisos.index') }}" class="btn-soft text-xs uppercase tracking-wider">Historial</a>
    </x-slot>

    <div class="py-8" x-data="{ alcance: @js(old('alcance', \App\Models\Aviso::ALCANCE_MASIVO)) }">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-3xl space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Nuevo aviso</h1>

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                    @foreach ($errors->all() as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('secretaria.avisos.store') }}" class="card-premium p-6 sm:p-8 border-t-2 border-t-[color:var(--color-accent)] space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Título</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" class="input-premium w-full" required maxlength="255" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Mensaje</label>
                    <textarea name="cuerpo" rows="8" class="input-premium w-full" required maxlength="10000" placeholder="Texto que verá el hermano en el portal (y opcionalmente por email).">{{ old('cuerpo') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-2">Alcance</label>
                    <div class="space-y-2">
                        @foreach ([\App\Models\Aviso::ALCANCE_MASIVO => 'Masivo (filtros)', \App\Models\Aviso::ALCANCE_INDIVIDUAL => 'Un hermano', \App\Models\Aviso::ALCANCE_SELECTIVO => 'Selección manual'] as $valor => $etiqueta)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="alcance" value="{{ $valor }}" x-model="alcance" class="text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" @checked(old('alcance', \App\Models\Aviso::ALCANCE_MASIVO) === $valor) />
                                <span class="text-sm font-medium text-slate-800">{{ $etiqueta }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="alcance === 'Masivo'" x-cloak class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase text-slate-500">Filtros masivos</p>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="solo_alta" value="1" class="rounded border-slate-300" @checked(old('solo_alta', true)) />
                        Solo hermanos en <strong>Alta</strong>
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="solo_portal" value="1" class="rounded border-slate-300" @checked(old('solo_portal')) />
                        Solo con <strong>cuenta de portal</strong> activa
                    </label>
                </div>

                <div x-show="alcance === 'Individual'" x-cloak>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Hermano</label>
                    <select name="hermano_id" class="input-premium w-full">
                        <option value="">— Elija —</option>
                        @foreach ($hermanos as $h)
                            <option value="{{ $h->id }}" @selected((string) old('hermano_id') === (string) $h->id)>
                                N.º {{ $h->numero_hermano }} — {{ $h->nombre }} {{ $h->apellidos }} ({{ $h->estado }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-show="alcance === 'Selectivo'" x-cloak>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Hermanos (Ctrl/Cmd + clic)</label>
                    <select name="hermano_ids[]" multiple size="12" class="input-premium w-full font-mono text-xs">
                        @foreach ($hermanos as $h)
                            <option value="{{ $h->id }}" @selected(collect(old('hermano_ids', []))->contains($h->id))>
                                {{ $h->numero_hermano }} · {{ $h->nombre }} {{ $h->apellidos }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label class="flex items-center gap-2 text-sm cursor-pointer border-t border-slate-100 pt-4">
                    <input type="checkbox" name="notificar_email" value="1" class="rounded border-slate-300" @checked(old('notificar_email')) />
                    Enviar también por <strong>correo electrónico</strong> (si el hermano tiene email válido en ficha)
                </label>

                <input type="hidden" name="visible_tablon" value="0" />
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="visible_tablon" value="1" class="rounded border-slate-300" @checked(old('visible_tablon', true)) />
                    Mostrar en el <strong>tablón del inicio</strong> del portal (noticias / avisos)
                </label>
                <input type="hidden" name="urgente" value="0" />
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="urgente" value="1" class="rounded border-slate-300" @checked(old('urgente')) />
                    Marcar como <strong>urgente</strong> (banner en portal y notificación del navegador si el hermano lo permite)
                </label>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="btn-accent">Publicar y enviar</button>
                    <a href="{{ route('secretaria.avisos.index') }}" class="btn-soft">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
