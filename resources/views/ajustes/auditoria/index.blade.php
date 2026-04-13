<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('ajustes.index') }}" class="btn-soft text-xs">Volver a Ajustes</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Auditoría técnica</h2>
                <p class="text-sm text-slate-600 mt-1">Logins, peticiones HTTP (admin y portal), IP, agente y datos de negocio enlazados.</p>
            </div>

            <div class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-4">
                <form method="GET" action="{{ route('ajustes.auditoria.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Canal</label>
                        <select name="canal" class="input-premium w-full">
                            <option value="">Todos</option>
                            @foreach (['admin', 'portal', 'portal_invitado'] as $c)
                                <option value="{{ $c }}" @selected(request('canal') === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Evento (contiene)</label>
                        <input type="text" name="evento" value="{{ request('evento') }}" class="input-premium w-full" placeholder="ej. login_ok" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Buscar (descripción, IP, ruta, email)</label>
                        <input type="text" name="q" value="{{ request('q') }}" class="input-premium w-full" />
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button type="submit" class="btn-primary text-sm">Filtrar</button>
                        <a href="{{ route('ajustes.auditoria.index') }}" class="btn-soft text-sm">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="hidden lg:block overflow-x-auto card-premium border-t-2 border-t-[color:var(--color-accent)]">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 font-bold uppercase text-slate-500 border-b border-slate-200">
                            <th class="px-3 py-2 text-left w-36">Fecha</th>
                            <th class="px-3 py-2 text-left">Canal / Evento</th>
                            <th class="px-3 py-2 text-left">Usuario / Portal</th>
                            <th class="px-3 py-2 text-left">IP</th>
                            <th class="px-3 py-2 text-left">Petición</th>
                            <th class="px-3 py-2 text-left">Descripción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/80 align-top">
                                <td class="px-3 py-2 whitespace-nowrap text-slate-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-3 py-2">
                                    <span class="font-mono text-[10px] text-slate-500">{{ $log->canal }}</span>
                                    <br />
                                    <span class="font-semibold text-slate-800">{{ $log->evento }}</span>
                                    @if ($log->codigo_http)
                                        <span class="text-slate-400">· {{ $log->codigo_http }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if ($log->user)
                                        <span class="font-medium text-slate-800">{{ $log->user->name }}</span>
                                        <br /><span class="text-slate-500">{{ $log->user->email }}</span>
                                    @elseif ($log->portalCuenta)
                                        <span class="text-slate-800">Portal #{{ $log->portalCuenta->id }}</span>
                                        <br /><span class="text-slate-500">{{ $log->portalCuenta->email }}</span>
                                        @if ($log->hermano)
                                            <br /><span class="text-slate-600">Hermano n.º {{ $log->hermano->numero_hermano }}</span>
                                        @endif
                                    @elseif ($log->email_intento)
                                        <span class="text-amber-800">Intento: {{ $log->email_intento }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-2 font-mono text-[10px] text-slate-700">{{ $log->ip_address ?? '—' }}</td>
                                <td class="px-3 py-2 font-mono text-[10px] break-all text-slate-600">
                                    {{ $log->metodo_http }} {{ \Illuminate\Support\Str::limit($log->path ?? '—', 80) }}
                                    @if ($log->ruta)
                                        <br /><span class="text-slate-400">{{ $log->ruta }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700 max-w-md">
                                    {{ \Illuminate\Support\Str::limit($log->descripcion ?? '—', 240) }}
                                    @if ($log->payload)
                                        <details class="mt-1">
                                            <summary class="cursor-pointer text-[color:var(--color-accent)] font-semibold">Payload</summary>
                                            <pre class="mt-1 p-2 bg-slate-50 rounded text-[10px] overflow-x-auto max-h-40">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">Sin registros con estos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="lg:hidden space-y-3">
                @forelse ($logs as $log)
                    <article class="card-premium p-4 text-xs space-y-2">
                        <p class="text-slate-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                        <p><span class="font-mono">{{ $log->canal }}</span> · <strong>{{ $log->evento }}</strong></p>
                        <p class="font-mono text-[10px] text-slate-600">{{ $log->ip_address }} · {{ $log->metodo_http }} {{ $log->path }}</p>
                        <p class="text-slate-800">{{ \Illuminate\Support\Str::limit($log->descripcion ?? '—', 300) }}</p>
                        @if ($log->payload)
                            <details>
                                <summary class="text-[color:var(--color-accent)] font-semibold">Payload JSON</summary>
                                <pre class="mt-1 text-[10px] bg-slate-50 p-2 rounded overflow-x-auto">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        @endif
                    </article>
                @empty
                    <p class="text-center text-slate-500 py-8">Sin registros.</p>
                @endforelse
            </div>

            <div class="pb-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
