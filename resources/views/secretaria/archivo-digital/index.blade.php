<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.archivo-digital.create') }}" class="btn-accent text-xs uppercase tracking-wider">Subir documento</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            <h1 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Archivo digital (explorador)</h1>
            <p class="text-sm text-slate-600">Reglas, actas, inventario artístico y boletines. Los documentos «Público para hermanos» aparecen en el portal (Mis documentos).</p>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-900 text-sm border border-emerald-200">{{ session('status') }}</div>
            @endif

            <div x-data="{ open: false }" class="card-premium p-4 border-t-2 border-t-[color:var(--color-accent)]">
                <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-left">
                    <span class="text-sm font-bold uppercase tracking-wide text-slate-600">Filtros</span>
                    <span class="text-slate-400 text-xs" x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                </button>
                <form x-show="open" x-cloak method="GET" class="mt-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar título…" class="input-premium sm:col-span-2" />
                    <select name="categoria" class="input-premium">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $k => $label)
                            <option value="{{ $k }}" @selected(request('categoria') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="nivel" class="input-premium">
                        <option value="">Todos los niveles</option>
                        @foreach ($niveles as $k => $label)
                            <option value="{{ $k }}" @selected(request('nivel') === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="sm:col-span-4 flex gap-2">
                        <button type="submit" class="btn-accent text-sm">Aplicar</button>
                        <a href="{{ route('secretaria.archivo-digital.index') }}" class="btn-soft text-sm">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500 border-b border-slate-200">
                                <th class="px-4 py-3 text-left">Título</th>
                                <th class="px-4 py-3 text-left">Categoría</th>
                                <th class="px-4 py-3 text-left">Acceso</th>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Resumen IA</th>
                                <th class="px-4 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documentos as $doc)
                                <tr class="border-b border-slate-100 hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-[color:var(--color-primary)]">{{ $doc->titulo }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $categorias[$doc->categoria] ?? $doc->categoria }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $niveles[$doc->nivel_acceso] ?? $doc->nivel_acceso }}</td>
                                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $doc->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-slate-500 text-xs max-w-xs">{{ \Illuminate\Support\Str::limit($doc->resumen_ia ?: 'Sin resumen', 90) }}</td>
                                    <td class="px-4 py-3 text-right space-x-1">
                                        <form action="{{ route('secretaria.archivo-digital.resumir', $doc) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex h-9 px-3 items-center justify-center rounded-full border border-[color:var(--color-accent)] text-[color:var(--color-primary)] text-xs" title="Resumir con IA">IA</button>
                                        </form>
                                        <a href="{{ route('secretaria.archivo-digital.descargar', $doc) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:border-[color:var(--color-accent)]" title="Descargar">↓</a>
                                        <form action="{{ route('secretaria.archivo-digital.destroy', $doc) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este documento del archivo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50" title="Eliminar">×</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No hay documentos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden divide-y divide-slate-100">
                    @forelse ($documentos as $doc)
                        <div class="p-4">
                            <p class="font-semibold text-[color:var(--color-primary)]">{{ $doc->titulo }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $categorias[$doc->categoria] ?? $doc->categoria }} · {{ $doc->created_at->format('d/m/Y') }}</p>
                            <div class="mt-3 flex gap-2">
                                <form action="{{ route('secretaria.archivo-digital.resumir', $doc) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-soft text-xs">Resumir IA</button>
                                </form>
                                <a href="{{ route('secretaria.archivo-digital.descargar', $doc) }}" class="btn-soft text-xs">Descargar</a>
                                <form action="{{ route('secretaria.archivo-digital.destroy', $doc) }}" method="POST" onsubmit="return confirm('¿Eliminar?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 font-semibold">Eliminar</button>
                                </form>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($doc->resumen_ia ?: 'Sin resumen IA', 160) }}</p>
                        </div>
                    @empty
                        <p class="p-6 text-center text-slate-500">No hay documentos.</p>
                    @endforelse
                </div>
            </div>
            <div class="px-2">{{ $documentos->links() }}</div>
        </div>
    </div>
</x-app-layout>
