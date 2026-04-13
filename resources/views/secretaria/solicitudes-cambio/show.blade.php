<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('secretaria.solicitudes-cambio.index') }}" class="btn-soft text-xs uppercase tracking-wider">Volver al listado</a>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 max-w-4xl space-y-6">
            <h1 class="text-2xl font-bold text-[color:var(--color-primary)]">Solicitud #{{ $solicitud->id }}</h1>

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                    @foreach ($errors->all() as $e)
                        <p>{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <div class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                <h2 class="text-sm font-bold uppercase text-slate-500">Hermano</h2>
                <p class="mt-2 text-lg font-bold text-[color:var(--color-primary)]">{{ $solicitud->hermano->nombreCompleto() }}</p>
                <p class="text-sm text-slate-600">N.º {{ $solicitud->hermano->numero_hermano }} · DNI {{ $solicitud->hermano->dni }}</p>
                <a href="{{ route('hermanos.show', $solicitud->hermano) }}" class="text-sm font-semibold text-[color:var(--color-accent)] hover:underline mt-2 inline-block">Abrir ficha en administración</a>
                @if (filled($solicitud->ip_solicitud) || filled($solicitud->user_agent))
                    <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-xs text-slate-600 space-y-1">
                        @if (filled($solicitud->ip_solicitud))
                            <p><span class="font-semibold text-slate-700">IP (portal):</span> {{ $solicitud->ip_solicitud }}</p>
                        @endif
                        @if (filled($solicitud->user_agent))
                            <p class="break-all"><span class="font-semibold text-slate-700">Dispositivo:</span> {{ $solicitud->user_agent }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                <h2 class="text-sm font-bold uppercase text-slate-500 mb-4">Cambios solicitados</h2>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                                <th class="px-3 py-2 text-left">Campo</th>
                                <th class="px-3 py-2 text-left">Valor actual</th>
                                <th class="px-3 py-2 text-left">Valor nuevo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solicitud->datos_solicitados ?? [] as $campo => $par)
                                @if (is_array($par))
                                    <tr class="border-t border-slate-100">
                                        <td class="px-3 py-2 font-semibold capitalize">{{ str_replace('_', ' ', $campo) }}</td>
                                        <td class="px-3 py-2 text-slate-600 break-all">{{ $par['antes'] ?? '—' }}</td>
                                        <td class="px-3 py-2 text-emerald-900 font-medium break-all">{{ $par['despues'] ?? '—' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($solicitud->estado !== \App\Models\SolicitudCambioDatos::ESTADO_PENDIENTE)
                <div class="card-premium p-6">
                    <p class="text-sm font-bold uppercase text-slate-500">Resolución</p>
                    <p class="mt-2 font-semibold">{{ $solicitud->estado }}</p>
                    @if ($solicitud->procesadoPor)
                        <p class="text-sm text-slate-600 mt-1">Por {{ $solicitud->procesadoPor->name }} el {{ $solicitud->procesado_en?->format('d/m/Y H:i') }}</p>
                    @endif
                    @if ($solicitud->motivo_rechazo)
                        <p class="mt-3 text-sm text-rose-800 border border-rose-200 bg-rose-50 rounded-lg p-3">{{ $solicitud->motivo_rechazo }}</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('secretaria.solicitudes-cambio.aprobar', $solicitud) }}" class="card-premium p-6 border-2 border-emerald-200 bg-emerald-50/30"
                          onsubmit="return confirm('¿Aprobar y aplicar los cambios en la ficha del hermano?');">
                        @csrf
                        <h3 class="font-bold text-emerald-900">Aprobar</h3>
                        <p class="text-sm text-slate-700 mt-2">Se actualizarán los campos en la tabla de hermanos. Si cambia el email, la cuenta del portal deberá verificar de nuevo el correo.</p>
                        <button type="submit" class="btn-accent w-full justify-center mt-4">Aprobar solicitud</button>
                    </form>
                    <div class="card-premium p-6 border-2 border-rose-200 bg-rose-50/20" x-data="{ open: false }">
                        <h3 class="font-bold text-rose-900">Rechazar</h3>
                        <p class="text-sm text-slate-700 mt-2">Indique el motivo; el hermano lo verá en su portal.</p>
                        <button type="button" @click="open = !open" class="btn-soft w-full justify-center mt-4">Mostrar formulario</button>
                        <form method="POST" action="{{ route('secretaria.solicitudes-cambio.rechazar', $solicitud) }}" class="mt-4 space-y-3" x-show="open" x-cloak>
                            @csrf
                            <textarea name="motivo_rechazo" rows="4" class="input-premium w-full" required placeholder="Motivo del rechazo (mín. 5 caracteres)">{{ old('motivo_rechazo') }}</textarea>
                            <button type="submit" class="w-full rounded-xl bg-rose-700 text-white py-2.5 text-sm font-semibold hover:bg-rose-800">Confirmar rechazo</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
