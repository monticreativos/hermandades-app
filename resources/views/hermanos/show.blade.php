<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-bold text-xl text-[color:var(--color-primary)] leading-tight">
                Ficha de Hermano #{{ $hermano->numero_hermano }}
            </h2>
            <div class="flex flex-wrap items-center gap-2 justify-end">
                <div class="relative" x-data="{ certOpen: false }" @keydown.escape.window="certOpen = false">
                    <button
                        type="button"
                        @click="certOpen = !certOpen"
                        class="btn-accent text-xs uppercase tracking-wider"
                        :aria-expanded="certOpen ? 'true' : 'false'"
                    >
                        Generar certificado
                    </button>
                    <div
                        x-show="certOpen"
                        x-transition
                        @click.outside="certOpen = false"
                        x-cloak
                        class="absolute right-0 mt-2 w-64 rounded-xl border border-slate-200 bg-white shadow-lg z-50 py-2 text-left"
                    >
                        <a
                            href="{{ route('hermanos.certificados.pertenencia', $hermano) }}"
                            target="_blank"
                            rel="noopener"
                            class="block px-4 py-2 text-sm text-slate-800 hover:bg-slate-50"
                        >
                            Certificado de pertenencia
                        </a>
                        <p class="px-4 pt-1 text-[10px] uppercase font-semibold text-slate-400">Cuotas (Hacienda)</p>
                        @foreach (range(now()->year, max(now()->year - 2, 2000)) as $y)
                            <a
                                href="{{ route('hermanos.certificados.cuotas-hacienda', ['hermano' => $hermano, 'año' => $y]) }}"
                                target="_blank"
                                rel="noopener"
                                class="block px-4 py-1.5 text-sm text-slate-700 hover:bg-slate-50 pl-6"
                            >
                                Ejercicio {{ $y }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('hermanos.index') }}" class="btn-soft">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ tab: 'ficha' }">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            @if (session('status'))
                <div class="p-3 rounded-xl bg-blue-50 text-blue-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-3 rounded-xl bg-rose-50 text-rose-800 text-sm border border-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
                <button type="button" @click="tab = 'ficha'" class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                    :class="tab === 'ficha' ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white' : 'border-transparent text-slate-600 hover:bg-slate-50'">
                    Ficha general
                </button>
                <button type="button" @click="tab = 'salida'" class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                    :class="tab === 'salida' ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white' : 'border-transparent text-slate-600 hover:bg-slate-50'">
                    Estación de penitencia
                </button>
                @hasanyrole('Secretaría|Administrador Hermandad|SuperAdmin')
                    <button type="button" @click="tab = 'comunicacion'" class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                        :class="tab === 'comunicacion' ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white' : 'border-transparent text-slate-600 hover:bg-slate-50'">
                        Correos masivos
                    </button>
                @endhasanyrole
                @can('contabilidad.gestion')
                    <button type="button" @click="tab = 'economia'" class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                        :class="tab === 'economia' ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white' : 'border-transparent text-slate-600 hover:bg-slate-50'">
                        Situación económica
                    </button>
                    <button type="button" @click="tab = 'contable'" class="px-4 py-2 rounded-xl text-sm font-semibold border transition"
                        :class="tab === 'contable' ? 'bg-[color:var(--color-primary-soft)] border-[color:var(--color-accent)] text-white' : 'border-transparent text-slate-600 hover:bg-slate-50'">
                        Libro mayor / Extracto
                    </button>
                @endcan
            </div>

            <div x-show="tab === 'ficha'" x-cloak>
                <section class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <article class="card-premium p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase">Edad</p>
                        <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ $edad ? $edad.' años' : '-' }}</p>
                    </article>
                    <article class="card-premium p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase">Antigüedad</p>
                        <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ $antiguedad !== null ? $antiguedad.' años' : '-' }}</p>
                    </article>
                    <article class="card-premium p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase">Fecha Alta</p>
                        <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ optional($hermano->fecha_alta)->format('d/m/Y') ?: '-' }}</p>
                    </article>
                    <article class="card-premium p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase">Fecha Baja</p>
                        <p class="text-2xl font-bold text-[color:var(--color-primary)] mt-1">{{ optional($hermano->fecha_baja)->format('d/m/Y') ?: '-' }}</p>
                    </article>
                </section>

                <section class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)] mt-5">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <h3 class="text-xl font-bold text-[color:var(--color-primary)]">{{ $hermano->nombre }} {{ $hermano->apellidos }}</h3>
                            <p class="text-sm text-slate-600 mt-1">Numero de hermano: {{ $hermano->numero_hermano }}</p>
                        </div>
                        @php
                            $badge = match($hermano->estado) {
                                'Alta' => 'badge-estado-alta',
                                'Baja' => 'badge-estado-baja',
                                default => 'badge-estado-difunto'
                            };
                        @endphp
                        <span class="{{ $badge }}">{{ $hermano->estado }}</span>
                    </div>

                    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 text-sm">
                        <div class="card-premium p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Datos Personales</p>
                            <p><span class="font-semibold">DNI:</span> {{ $hermano->dni }}</p>
                            <p><span class="font-semibold">Sexo:</span> {{ $hermano->sexo ?: '-' }}</p>
                            <p><span class="font-semibold">Fecha Nacimiento:</span> {{ optional($hermano->fecha_nacimiento)->format('d/m/Y') ?: '-' }}</p>
                            <p><span class="font-semibold">Edad:</span> {{ $edad ? $edad.' años' : '-' }}</p>
                        </div>

                        <div class="card-premium p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Contacto</p>
                            <p><span class="font-semibold">Telefono:</span> {{ $hermano->telefono ?: '-' }}</p>
                            <p><span class="font-semibold">Email:</span> {{ $hermano->email ?: '-' }}</p>
                            <p><span class="font-semibold">Direccion:</span> {{ $hermano->direccion ?: '-' }}</p>
                            <p><span class="font-semibold">Localidad:</span> {{ $hermano->localidad ?: '-' }}</p>
                            <p><span class="font-semibold">Provincia:</span> {{ $hermano->provincia ?: '-' }}</p>
                            <p><span class="font-semibold">CP:</span> {{ $hermano->codigo_postal ?: '-' }}</p>
                        </div>

                        <div class="card-premium p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Datos Bancarios</p>
                            <p><span class="font-semibold">Banco:</span> {{ $hermano->banco?->nombre ?: '-' }}</p>
                            <p><span class="font-semibold">Sucursal:</span> {{ $hermano->sucursal ?: '-' }}</p>
                            <p><span class="font-semibold">IBAN:</span> {{ $hermano->iban ?: '-' }}</p>
                            <p><span class="font-semibold">Titular:</span> {{ $hermano->titular_cuenta ?: '-' }}</p>
                            <p><span class="font-semibold">Titular menor:</span> {{ $hermano->titular_cuenta_menor ?: '-' }}</p>
                        </div>

                        <div class="card-premium p-4">
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Datos Eclesiásticos</p>
                            <p><span class="font-semibold">Fecha alta:</span> {{ optional($hermano->fecha_alta)->format('d/m/Y') ?: '-' }}</p>
                            <p><span class="font-semibold">Fecha baja:</span> {{ optional($hermano->fecha_baja)->format('d/m/Y') ?: '-' }}</p>
                            <p><span class="font-semibold">Antigüedad:</span> {{ $antiguedad !== null ? $antiguedad.' años' : '-' }}</p>
                            <p><span class="font-semibold">Fecha bautismo:</span> {{ optional($hermano->fecha_bautismo)->format('d/m/Y') ?: '-' }}</p>
                            <p><span class="font-semibold">Parroquia:</span> {{ $hermano->parroquia_bautismo ?: '-' }}</p>
                        </div>

                        <div class="card-premium p-4 md:col-span-2">
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-2">Observaciones</p>
                            <p class="text-slate-700">{{ $hermano->observaciones ?: 'Sin observaciones registradas.' }}</p>
                        </div>
                    </div>
                </section>

                @hasanyrole('Secretaría|Administrador Hermandad|SuperAdmin')
                    <section class="card-premium p-6 mt-5 border-t-2 border-t-[color:var(--color-accent)]">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div>
                                <h4 class="font-bold text-[color:var(--color-primary)] mb-1 text-lg">Mi Familia y fiscalidad</h4>
                                <p class="text-sm text-slate-600">Unidad familiar, deducción fiscal (Modelo 182) y pago unificado.</p>
                            </div>
                            @if ($familia)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $familiaPendientes > 0 ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $familiaPendientes > 0 ? 'Alerta: cuotas familiares pendientes' : 'Familia al corriente' }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
                            <div class="xl:col-span-2 card-premium p-4">
                                <p class="text-xs font-semibold uppercase text-slate-500 mb-3">Árbol familiar (vista lineal)</p>
                                @if ($familiaMiembros->isEmpty())
                                    <p class="text-sm text-slate-500">Aún sin unidad familiar creada. Añade un familiar para iniciarla.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($familiaMiembros as $miembro)
                                            <div class="rounded-lg border border-slate-200 p-3 flex items-center justify-between gap-2">
                                                <div>
                                                    <p class="font-semibold text-[color:var(--color-primary)]">{{ $miembro->nombreCompleto() }} <span class="text-xs text-slate-500">· N.º {{ $miembro->numero_hermano }}</span></p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ $miembro->pivot?->parentesco ?? 'Miembro' }}
                                                        @if ($miembro->es_cabeza_familia) · Cabeza de familia @endif
                                                        @if ($miembro->beneficiario_fiscal_hermano_id) · Deducción a: #{{ $miembro->beneficiario_fiscal_hermano_id }} @endif
                                                    </p>
                                                </div>
                                                @if (in_array($miembro->estado_cuota, ['Pendiente', 'Impagada'], true))
                                                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-rose-100 text-rose-700">{{ $miembro->estado_cuota }}</span>
                                                @else
                                                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">Al corriente</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="card-premium p-4 space-y-4">
                                <form method="POST" action="{{ route('hermanos.familia.store', $hermano) }}" class="space-y-2">
                                    @csrf
                                    <p class="text-xs font-semibold uppercase text-slate-500">Añadir familiar</p>
                                    <select name="familiar_id" class="input-premium w-full" required>
                                        <option value="">Selecciona hermano…</option>
                                        @foreach($hermanosRelacionables as $cand)
                                            <option value="{{ $cand->id }}">#{{ $cand->numero_hermano }} · {{ $cand->apellidos }}, {{ $cand->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <select name="parentesco" class="input-premium w-full" required>
                                        <option>Padre</option><option>Madre</option><option>Hijo/a</option><option>Cónyuge</option><option>Tutor</option>
                                    </select>
                                    <button class="btn-soft text-xs">Añadir al grupo</button>
                                </form>

                                <form method="POST" action="{{ route('hermanos.familia.configurar', $hermano) }}" class="space-y-2 border-t border-slate-200 pt-3">
                                    @csrf
                                    <p class="text-xs font-semibold uppercase text-slate-500">Configuración fiscal y pagos</p>
                                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="es_cabeza_familia" value="1" @checked($hermano->es_cabeza_familia)> Cabeza de familia</label>
                                    <select name="beneficiario_fiscal_hermano_id" class="input-premium w-full">
                                        <option value="">Deducción: el propio hermano</option>
                                        @foreach($familiaMiembros as $miembro)
                                            <option value="{{ $miembro->id }}" @selected((int) $hermano->beneficiario_fiscal_hermano_id === (int) $miembro->id)>
                                                {{ $miembro->nombreCompleto() }} ({{ $miembro->dni }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="pago_unificado" value="1" @checked($familia?->pago_unificado)> Pago unificado familiar</label>
                                    <select name="pagador_hermano_id" class="input-premium w-full">
                                        <option value="">Selecciona pagador unificado</option>
                                        @foreach($familiaMiembros as $miembro)
                                            <option value="{{ $miembro->id }}" @selected((int) ($familia?->pagador_hermano_id ?? 0) === (int) $miembro->id)>
                                                {{ $miembro->nombreCompleto() }} · IBAN {{ $miembro->iban ?: 'sin IBAN' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn-accent text-xs">Guardar configuración familiar</button>
                                </form>
                            </div>
                        </div>
                    </section>
                @endhasanyrole

                @hasanyrole('Secretaría|Administrador Hermandad|SuperAdmin')
                    <section class="card-premium p-6 mt-5 border-t-2 border-t-[color:var(--color-accent)]">
                        <h4 class="font-bold text-[color:var(--color-primary)] mb-2 text-lg">Portal del hermano</h4>
                        <p class="text-sm text-slate-600 mb-4">
                            Active el acceso móvil enviando un enlace temporal (72 h) al correo del hermano. Tras activar, deberá verificar el email.
                        </p>
                        @php
                            $portal = $hermano->portalCuenta;
                            $portalActivo = $portal && filled($portal->password);
                            $portalPendiente = $portal && ! filled($portal->password);
                        @endphp
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($portalActivo)
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    Portal activado
                                </span>
                            @elseif ($portalPendiente)
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    Invitación pendiente (sin contraseña)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                    Sin invitación enviada
                                </span>
                            @endif
                            @if (! $portalActivo)
                                <form method="POST" action="{{ route('hermanos.portal.invitacion', $hermano) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-accent text-xs uppercase tracking-wider">
                                        {{ $portalPendiente ? 'Reenviar enlace de activación' : 'Enviar enlace de activación' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if ($portalActivo)
                            <p class="text-xs text-slate-500 mt-3">Si el hermano olvidó la contraseña, puede usar la recuperación por código en la pantalla de acceso del portal.</p>
                        @endif
                    </section>
                @endhasanyrole

                <section class="card-premium p-6 mt-5">
                    <h4 class="font-bold text-[color:var(--color-primary)] mb-3 text-lg">Documentación privada</h4>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('hermanos.documentos.descargar', ['hermano' => $hermano->id, 'tipo' => 'partida_bautismo']) }}"
                            class="btn-soft"
                        >
                            Descargar Partida de Bautismo
                        </a>
                        <a
                            href="{{ route('hermanos.documentos.descargar', ['hermano' => $hermano->id, 'tipo' => 'dni_escaneado']) }}"
                            class="btn-soft"
                        >
                            Descargar DNI Escaneado
                        </a>
                    </div>
                </section>
            </div>

            <div x-show="tab === 'salida'" x-cloak class="space-y-5">
                <section class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-[color:var(--color-primary)]">Papeletas de sitio (histórico)</h3>
                            <p class="text-sm text-slate-600 mt-1">Los registros se conservan por ejercicio; no se pierden al cambiar de año.</p>
                        </div>
                        <a href="{{ route('salida.papeletas.index') }}" class="btn-accent text-xs uppercase tracking-wider shrink-0">Ir a emisión</a>
                    </div>

                    @if ($papeletasSalida->isEmpty())
                        <p class="text-sm text-slate-500 py-6 text-center">No consta ninguna papeleta emitida para este hermano.</p>
                    @else
                        <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                                        <th class="px-4 py-3 text-left">Ejercicio</th>
                                        <th class="px-4 py-3 text-left">Puesto</th>
                                        <th class="px-4 py-3 text-left">Tramo</th>
                                        <th class="px-4 py-3 text-left">Insignia</th>
                                        <th class="px-4 py-3 text-right">Donativo</th>
                                        <th class="px-4 py-3 text-center">Estado</th>
                                        <th class="px-4 py-3 text-center">PDF</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($papeletasSalida as $pap)
                                        @php
                                            $estadoBadge = match($pap->estado) {
                                                'Emitida' => 'bg-emerald-100 text-emerald-800',
                                                'Solicitada' => 'bg-amber-100 text-amber-800',
                                                'Anulada' => 'bg-red-100 text-red-800',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp
                                        <tr class="border-t border-slate-100">
                                            <td class="px-4 py-2.5 font-semibold">{{ $pap->ejercicio?->año ?? '—' }}</td>
                                            <td class="px-4 py-2.5">{{ $pap->puesto }}</td>
                                            <td class="px-4 py-2.5 text-slate-600">{{ $pap->tramo ?: '—' }}</td>
                                            <td class="px-4 py-2.5 text-slate-600">{{ $pap->insignia?->nombre ?: '—' }}</td>
                                            <td class="px-4 py-2.5 text-right font-mono">{{ number_format((float) $pap->donativo_pagado, 2, ',', '.') }} €</td>
                                            <td class="px-4 py-2.5 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $estadoBadge }}">{{ $pap->estado }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 text-center">
                                                @if ($pap->estado !== 'Anulada')
                                                    <a href="{{ route('salida.papeletas.pdf', $pap) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-[color:var(--color-accent)] hover:underline">Imprimir</a>
                                                @else
                                                    <span class="text-xs text-slate-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="md:hidden space-y-3">
                            @foreach ($papeletasSalida as $pap)
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-bold text-[color:var(--color-primary)]">{{ $pap->ejercicio?->año ?? '—' }}</span>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ match($pap->estado) { 'Emitida' => 'bg-emerald-100 text-emerald-800', 'Solicitada' => 'bg-amber-100 text-amber-800', 'Anulada' => 'bg-red-100 text-red-800', default => 'bg-slate-100 text-slate-700' } }}">{{ $pap->estado }}</span>
                                    </div>
                                    <p class="text-sm mt-1">{{ $pap->puesto }} · Tramo {{ $pap->tramo ?: '—' }}</p>
                                    @if ($pap->insignia)
                                        <p class="text-xs text-slate-500">{{ $pap->insignia->nombre }}</p>
                                    @endif
                                    <p class="text-sm font-mono mt-2">{{ number_format((float) $pap->donativo_pagado, 2, ',', '.') }} €</p>
                                    @if ($pap->estado !== 'Anulada')
                                        <a href="{{ route('salida.papeletas.pdf', $pap) }}" target="_blank" rel="noopener" class="inline-block mt-2 text-xs font-semibold text-[color:var(--color-accent)]">PDF papeleta</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="card-premium p-6">
                    <h4 class="font-bold text-[color:var(--color-primary)] mb-3">Túnicas en su poder</h4>
                    @if ($tunicasAsignadas->isEmpty())
                        <p class="text-sm text-slate-500">No tiene túnicas de la hermandad asignadas actualmente.</p>
                    @else
                        <ul class="divide-y divide-slate-100 text-sm">
                            @foreach ($tunicasAsignadas as $t)
                                <li class="py-3 flex flex-wrap justify-between gap-2">
                                    <div>
                                        <span class="font-mono font-semibold">{{ $t->codigo }}</span>
                                        <span class="text-slate-600"> · Talla {{ $t->talla }}</span>
                                        <span class="block text-xs text-slate-500">{{ $t->estado }}</span>
                                    </div>
                                    @if ((float) $t->fianza > 0)
                                        <span class="font-mono text-xs">Fianza {{ number_format((float) $t->fianza, 2, ',', '.') }} €</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <a href="{{ route('salida.tunicas.index') }}" class="btn-soft text-xs mt-4 inline-block">Gestionar túnicas</a>
                </section>
            </div>

            @hasanyrole('Secretaría|Administrador Hermandad|SuperAdmin')
                <div x-show="tab === 'comunicacion'" x-cloak class="space-y-5">
                    <section class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                        <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-2">Comunicados masivos por email</h3>
                        <p class="text-sm text-slate-600 mb-4">Registro de envíos desde <strong>Secretaría → Comunicados por email</strong>. La apertura se estima mediante pixel de seguimiento (no es 100% fiable en todos los clientes de correo).</p>
                        @if ($comunicadosRecibidos->isEmpty())
                            <p class="text-sm text-slate-500 py-6 text-center">Este hermano no figura como destinatario de comunicados masivos.</p>
                        @else
                            <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-slate-50 text-xs font-bold uppercase text-slate-500">
                                            <th class="px-4 py-3 text-left">Fecha envío</th>
                                            <th class="px-4 py-3 text-left">Asunto</th>
                                            <th class="px-4 py-3 text-left">Correo procesado</th>
                                            <th class="px-4 py-3 text-left">Abierto</th>
                                            <th class="px-4 py-3 text-right">Veces</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($comunicadosRecibidos as $row)
                                            <tr class="border-t border-slate-100">
                                                <td class="px-4 py-2.5 text-slate-600">{{ $row->comunicadoMasivo?->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                                <td class="px-4 py-2.5 font-medium text-[color:var(--color-primary)]">{{ $row->comunicadoMasivo?->asunto ?? '—' }}</td>
                                                <td class="px-4 py-2.5 text-slate-600">{{ $row->correo_enviado_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                                <td class="px-4 py-2.5 text-slate-600">{{ $row->abierto_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $row->aperturas_count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="md:hidden space-y-2">
                                @foreach ($comunicadosRecibidos as $row)
                                    <div class="rounded-xl border border-slate-200 p-3 text-sm">
                                        <p class="font-semibold text-[color:var(--color-primary)]">{{ $row->comunicadoMasivo?->asunto }}</p>
                                        <p class="text-xs text-slate-500 mt-1">Enviado: {{ $row->correo_enviado_en?->format('d/m H:i') ?? '—' }} · Abierto: {{ $row->abierto_en?->format('d/m H:i') ?? '—' }} ({{ $row->aperturas_count }}×)</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>
            @endhasanyrole

            @can('contabilidad.gestion')
                <div x-show="tab === 'economia'" x-cloak class="space-y-5">
                    <section class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                        <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-4">Resumen tesorería</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <article class="rounded-xl border border-slate-200 p-4 {{ (float) $deudaLoteriaPendiente > 0 ? 'bg-amber-50/80 border-amber-200' : 'bg-emerald-50/60 border-emerald-200' }}">
                                <p class="text-xs font-bold uppercase text-slate-600">Lotería / rifas pendiente</p>
                                <p class="text-2xl font-mono font-bold mt-2 text-[color:var(--color-primary)]">{{ number_format((float) $deudaLoteriaPendiente, 2, ',', '.') }} €</p>
                                @if ((float) $deudaLoteriaPendiente <= 0)
                                    <p class="text-sm text-emerald-800 mt-2">Sin tacos pendientes de devolución.</p>
                                @else
                                    <p class="text-sm text-amber-900 mt-2">Tiene participaciones sin marcar como cobradas en el módulo de Lotería.</p>
                                @endif
                            </article>
                            @php
                                $cuotaCardClass = match ($hermano->estado_cuota) {
                                    'Impagada' => 'bg-rose-50/90 border-rose-200',
                                    'Pendiente' => 'bg-amber-50/90 border-amber-200',
                                    default => 'bg-slate-50/80',
                                };
                            @endphp
                            <article class="rounded-xl border border-slate-200 p-4 {{ $cuotaCardClass }}">
                                <p class="text-xs font-bold uppercase text-slate-600">Cuota ordinaria (contabilidad)</p>
                                <div class="mt-3 rounded-lg border border-slate-200/80 bg-white/70 p-3 text-xs text-slate-700">
                                    <p><span class="font-semibold text-slate-800">Periodicidad de pago:</span> {{ $periodicidadPago }}</p>
                                    <p class="mt-1"><span class="font-semibold text-slate-800">Cuota anual de referencia:</span> <span class="font-mono">{{ number_format($importeCuotaAnualReferencia, 2, ',', '.') }} €</span> @if ($hermano->importe_cuota_anual_referencia === null)<span class="text-slate-500">(hermandad / defecto)</span>@endif</p>
                                    <p class="mt-1"><span class="font-semibold text-slate-800">Importe por periodo (prorrateo):</span> <span class="font-mono">{{ number_format($importeCuotaPorPeriodo, 2, ',', '.') }} €</span></p>
                                </div>
                                @if ($hermano->estado_cuota === 'Impagada')
                                    <p class="text-lg font-bold text-rose-900 mt-3">Impagada / devolución bancaria</p>
                                    <p class="text-sm text-rose-950/90 mt-1">
                                        Consta devolución de domiciliación o incidencia de cobro. Regularice desde el portal (Bizum) o en secretaría; al conciliar la remesa o registrar el cobro, el estado se actualizará automáticamente.
                                    </p>
                                @elseif ($hermano->estado_cuota === 'Pendiente')
                                    <p class="text-lg font-bold text-amber-900 mt-3">Pendiente de cobro</p>
                                    <p class="text-sm text-amber-950/90 mt-1">
                                        Ejercicio contable:
                                        <strong>{{ $hermano->cuotaPendienteEjercicio?->año ?? '—' }}</strong>.
                                        Con remesas SEPA, use <strong>Economía → Remesas</strong> y la respuesta del banco para conciliar. Con el asiento masivo clásico en Cuotas, pasa a <strong>Al corriente</strong> al registrar el cobro (572 / 430–431) con el concepto adecuado (<span class="font-mono text-xs">n.º {{ $hermano->numero_hermano }}</span>).
                                    </p>
                                @else
                                    <p class="text-lg font-bold text-emerald-800 mt-3">Al corriente</p>
                                    <p class="text-sm text-slate-700 mt-1">Sin cuota ordinaria pendiente registrada en el sistema. Los cobros deben reflejarse en el libro diario (572 / 430–431).</p>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('economia.libro-diario.index') }}" class="btn-soft text-xs">Libro diario</a>
                                    <a href="{{ route('economia.remesas.index') }}" class="btn-soft text-xs">Remesas SEPA</a>
                                    <a href="{{ route('economia.cuotas.index') }}" class="btn-soft text-xs">Cuotas</a>
                                    <a href="{{ route('economia.facturas.index') }}" class="btn-soft text-xs">Facturas</a>
                                    <a href="{{ route('economia.loterias.index') }}" class="btn-soft text-xs">Lotería</a>
                                </div>
                            </article>
                        </div>
                    </section>

                    @if ($lotesPendientes->isNotEmpty())
                        <section class="card-premium p-6">
                            <h4 class="font-bold text-[color:var(--color-primary)] mb-3">Tacos / participaciones pendientes</h4>
                            <ul class="divide-y divide-slate-100 text-sm">
                                @foreach ($lotesPendientes as $lp)
                                    <li class="py-3 flex flex-wrap justify-between gap-2">
                                        <div>
                                            <span class="font-semibold">{{ $lp->loteria->sorteo }}</span>
                                            <span class="text-slate-500 text-xs block font-mono">{{ $lp->loteria->numero }} @if($lp->referencia_taco) · {{ $lp->referencia_taco }} @endif</span>
                                        </div>
                                        <div class="font-mono tabular-nums">{{ number_format((float) $lp->importe_a_cobrar, 2, ',', '.') }} €</div>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>

                <div x-show="tab === 'contable'" x-cloak class="space-y-5">
                    <section class="card-premium p-6 border-t-2 border-t-[color:var(--color-accent)]">
                        <h3 class="text-lg font-bold text-[color:var(--color-primary)] mb-2">Libro mayor / Extracto de la subcuenta</h3>
                        <p class="text-sm text-slate-600 mb-4">El código de cuenta es <strong>inmutable</strong>: aunque cambie el número de hermano u otros datos, el historial contable permanece en la misma subcuenta auxiliar (p. ej. <span class="font-mono text-xs">430.000125</span>).</p>
                        @if (! $extractoCuenta)
                            <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950">
                                Aún no hay cuenta auxiliar vinculada. Use <a href="{{ route('ajustes.estado-sistema') }}" class="font-semibold underline">Ajustes → Estado del sistema → Sincronizar cuentas auxiliares</a> o cree movimientos de cuota para generarla automáticamente.
                            </div>
                        @else
                            <div class="flex flex-wrap gap-2 mb-4">
                                <a href="{{ route('economia.informes.libro-mayor', ['cuenta_contable_id' => $extractoCuenta->id]) }}" class="btn-accent text-xs">Abrir en Libro mayor (filtros y fechas)</a>
                                <a href="{{ route('hermanos.extracto-contable.pdf', $hermano) }}" target="_blank" rel="noopener" class="btn-soft text-xs border border-[color:var(--color-accent)]/50">Descargar extracto (PDF)</a>
                            </div>
                            @include('economia.informes.partials.libro-mayor-movimientos', [
                                'movimientos' => $extractoMovimientos,
                                'cuentaSel' => $extractoCuenta,
                                'emptyHint' => 'Sin apuntes en esta subcuenta.',
                                'vistaContabilidad' => true,
                                'saldoPorVentana' => true,
                            ])
                        @endif
                    </section>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
