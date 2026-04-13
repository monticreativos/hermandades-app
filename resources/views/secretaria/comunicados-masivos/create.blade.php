<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.comunicados-masivos.index') }}" class="btn-soft text-xs uppercase tracking-wider">Historial</a>
    </x-slot>

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.0.10/dist/trix.css">
        <script src="https://cdn.jsdelivr.net/npm/trix@2.0.10/dist/trix.umd.min.js" defer></script>
    @endpush

    <div class="py-8" x-data="{ filtro: @js(old('filtro_envio', \App\Models\ComunicadoMasivo::FILTRO_TODOS)) }">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-3xl space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Redactor de comunicados</h1>
            <p class="text-sm text-slate-600">El envío se procesa en segundo plano mediante colas de Laravel. Se registra en la ficha de cada hermano y se detecta la apertura del correo (pixel de seguimiento).</p>

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                    @foreach ($errors->all() as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('secretaria.comunicados-masivos.store') }}" class="card-premium p-6 sm:p-8 border-t-2 border-t-[color:var(--color-accent)] space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Asunto del correo</label>
                    <input type="text" name="asunto" value="{{ old('asunto') }}" class="input-premium w-full" required maxlength="255" />
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Cuerpo (texto enriquecido)</label>
                    <input id="cuerpo_html_input" type="hidden" name="cuerpo_html" value="{{ old('cuerpo_html') }}" />
                    <trix-editor input="cuerpo_html_input" class="trix-content rounded-xl border border-slate-300 bg-white min-h-[220px]"></trix-editor>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-2">Filtro de destinatarios</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro_envio" value="{{ \App\Models\ComunicadoMasivo::FILTRO_TODOS }}" x-model="filtro" class="text-[color:var(--color-accent)]" @checked(old('filtro_envio', \App\Models\ComunicadoMasivo::FILTRO_TODOS) === \App\Models\ComunicadoMasivo::FILTRO_TODOS) />
                            <span class="text-sm font-medium text-slate-800">Todos los hermanos (en alta, con email válido)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro_envio" value="{{ \App\Models\ComunicadoMasivo::FILTRO_CON_DEUDA }}" x-model="filtro" class="text-[color:var(--color-accent)]" @checked(old('filtro_envio') === \App\Models\ComunicadoMasivo::FILTRO_CON_DEUDA) />
                            <span class="text-sm font-medium text-slate-800">Solo con deuda (contable, cuota pendiente o lotería)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro_envio" value="{{ \App\Models\ComunicadoMasivo::FILTRO_TRAMO_COFRADIA }}" x-model="filtro" class="text-[color:var(--color-accent)]" @checked(old('filtro_envio') === \App\Models\ComunicadoMasivo::FILTRO_TRAMO_COFRADIA) />
                            <span class="text-sm font-medium text-slate-800">Solo un tramo de la cofradía (papeleta histórica)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro_envio" value="{{ \App\Models\ComunicadoMasivo::FILTRO_SOLO_COSTALEROS }}" x-model="filtro" class="text-[color:var(--color-accent)]" @checked(old('filtro_envio') === \App\Models\ComunicadoMasivo::FILTRO_SOLO_COSTALEROS) />
                            <span class="text-sm font-medium text-slate-800">Solo costaleros (con papeleta de costalero)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro_envio" value="{{ \App\Models\ComunicadoMasivo::FILTRO_CONTACTOS_EXTERNOS }}" x-model="filtro" class="text-[color:var(--color-accent)]" @checked(old('filtro_envio') === \App\Models\ComunicadoMasivo::FILTRO_CONTACTOS_EXTERNOS) />
                            <span class="text-sm font-medium text-slate-800">Contactos externos (por categoría)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro_envio" value="{{ \App\Models\ComunicadoMasivo::FILTRO_AUDIENCIA_MIXTA }}" x-model="filtro" class="text-[color:var(--color-accent)]" @checked(old('filtro_envio') === \App\Models\ComunicadoMasivo::FILTRO_AUDIENCIA_MIXTA) />
                            <span class="text-sm font-medium text-slate-800">Audiencia mixta (grupos + destinatarios individuales)</span>
                        </label>
                    </div>
                </div>

                <div x-show="filtro === '{{ \App\Models\ComunicadoMasivo::FILTRO_TRAMO_COFRADIA }}'" x-cloak class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Texto del tramo (coincidencia exacta)</label>
                    <input type="text" name="filtro_tramo_valor" value="{{ old('filtro_tramo_valor') }}" class="input-premium w-full" placeholder="Ej: 1, 2, Cristo, Virgen…" />
                </div>
                <div x-show="filtro === '{{ \App\Models\ComunicadoMasivo::FILTRO_CONTACTOS_EXTERNOS }}'" x-cloak class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <label class="block text-xs font-semibold uppercase text-slate-600 mb-1">Categoría contactos externos</label>
                    <select name="filtro_contacto_categoria" class="input-premium w-full">
                        <option value="">Todas</option>
                        @foreach(($categoriasContactos ?? []) as $cat)<option value="{{ $cat }}" @selected(old('filtro_contacto_categoria')===$cat)>{{ $cat }}</option>@endforeach
                    </select>
                </div>
                <div x-show="filtro === '{{ \App\Models\ComunicadoMasivo::FILTRO_AUDIENCIA_MIXTA }}'" x-cloak class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                    <p class="text-xs font-semibold uppercase text-slate-600">Grupos</p>
                    <label class="flex items-center gap-2"><input type="checkbox" name="audiencia_mixta[]" value="hermanos_todos"> Hermanos en alta con email</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="audiencia_mixta[]" value="hermanos_con_deuda"> Hermanos con deuda</label>
                    @foreach(($categoriasContactos ?? []) as $cat)
                        <label class="flex items-center gap-2"><input type="checkbox" name="audiencia_mixta[]" value="contactos_categoria:{{ $cat }}"> Contactos: {{ $cat }}</label>
                    @endforeach
                    <p class="text-xs font-semibold uppercase text-slate-600 pt-2">Destinatarios individuales</p>
                    <select name="destinatarios_individuales[]" multiple class="input-premium w-full min-h-28">
                        @foreach(($destinatariosSugeridos['hermanos'] ?? []) as $h)
                            <option value="h:{{ $h->id }}">Hermano · {{ $h->apellidos }}, {{ $h->nombre }}</option>
                        @endforeach
                        @foreach(($destinatariosSugeridos['contactos'] ?? []) as $c)
                            <option value="c:{{ $c->id }}">Contacto · {{ $c->nombre }} ({{ $c->categoria }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="btn-accent">Encolar envío masivo</button>
                    <a href="{{ route('secretaria.comunicados-masivos.index') }}" class="btn-soft">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
