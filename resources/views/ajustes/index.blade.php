<x-app-layout>
    <x-slot name="header">
        <button type="button" x-data @click="$dispatch('open-modal', 'editar-hermandad')" class="btn-accent uppercase tracking-wider text-xs">
            Editar Datos
        </button>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-2 sm:px-4 lg:px-6 space-y-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Configuración de la Hermandad</h2>

            @if (session('status'))
                <div class="p-3 rounded-xl bg-green-50 text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500 mb-4">Administración y auditoría (v1.0)</h3>
                <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                    <a href="{{ route('ajustes.estado-sistema') }}" class="btn-soft text-xs text-center sm:text-left">Estado del sistema</a>
                    <a href="{{ route('ajustes.actividades.index') }}" class="btn-soft text-xs text-center sm:text-left">Registro de actividad</a>
                    <a href="{{ route('ajustes.auditoria.index') }}" class="btn-soft text-xs text-center sm:text-left border border-[color:var(--color-accent)]/40">Auditoría técnica (logins, IP, CRUD)</a>
                    <a href="{{ route('ajustes.renumeracion.show') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold border border-amber-300 bg-amber-50 text-amber-950 hover:bg-amber-100">Recalcular números de hermano</a>
                </div>
            </section>

            <section class="card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
                <div class="flex flex-col md:flex-row md:items-center gap-5">
                    <div class="shrink-0">
                        @php
                            $escudo = $configuracion->escudo_path;
                            $escudoUrl = $escudo ? (str_starts_with($escudo, 'http') ? $escudo : \Illuminate\Support\Facades\Storage::url($escudo)) : null;
                            $nombreMostrar = $configuracion->nombre_corto ?: $configuracion->nombre_hermandad;
                            $iniciales = collect(explode(' ', trim((string) $nombreMostrar)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($parte) => strtoupper(substr($parte, 0, 1)))
                                ->implode('');
                        @endphp
                        @if ($escudoUrl)
                            <img src="{{ $escudoUrl }}" alt="Escudo Hermandad" class="h-24 w-24 rounded-full object-cover border border-slate-300">
                        @else
                            <div class="h-24 w-24 rounded-full border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-lg font-bold text-slate-600">
                                {{ $iniciales ?: 'GH' }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Nombre Hermandad</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $configuracion->nombre_hermandad ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Nombre Corto</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->nombre_corto ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">CIF</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->cif ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Teléfono</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->telefono ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Email Contacto</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->email_contacto ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">IBAN Cuotas</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->iban_cuotas ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">BIC/SWIFT</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->bic_swift ?: '-' }}</p>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <p class="text-xs uppercase font-semibold text-slate-500">Dirección</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->direccion ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Localidad</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->localidad ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">CP</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->cp ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Provincia</p>
                            <p class="mt-1 text-slate-800">{{ $configuracion->provincia ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Antigüedad mín. censo electoral</p>
                            <p class="mt-1 text-slate-800">{{ (int) ($configuracion->censo_antiguedad_anos ?? 1) }} año(s)</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500">Cuota anual por defecto (remesas)</p>
                            <p class="mt-1 text-slate-800 font-mono">{{ $configuracion->importe_cuota_anual_defecto !== null ? number_format((float) $configuracion->importe_cuota_anual_defecto, 2, ',', '.').' €' : '60,00 € (sistema)' }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <x-modal name="editar-hermandad" :show="$errors->any()" maxWidth="2xl">
        <form method="POST" action="{{ route('ajustes.update') }}" enctype="multipart/form-data" class="p-8" x-data="{ tab: 'general' }">
            @csrf
            @method('PUT')

            <h3 class="text-2xl font-bold text-[color:var(--color-primary)] mb-5">Editar Datos de la Hermandad</h3>

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                    Revisa los datos del formulario.
                </div>
            @endif

            <div class="flex flex-wrap gap-2 mb-5">
                <button type="button" @click="tab = 'general'" :class="tab === 'general' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Datos Generales</button>
                <button type="button" @click="tab = 'contacto'" :class="tab === 'contacto' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Contacto y Banco</button>
                <button type="button" @click="tab = 'secretaria'" :class="tab === 'secretaria' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Secretaría</button>
                <button type="button" @click="tab = 'escudo'" :class="tab === 'escudo' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Escudo</button>
                <button type="button" @click="tab = 'firmas'" :class="tab === 'firmas' ? 'bg-[color:var(--color-primary)] text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-2 text-xs rounded-xl font-semibold">Firmas y sellos</button>
            </div>

            <div x-show="tab === 'general'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Nombre Hermandad *</label>
                    <input name="nombre_hermandad" value="{{ old('nombre_hermandad', $configuracion->nombre_hermandad) }}" class="input-premium" required>
                    @error('nombre_hermandad') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Nombre Corto</label>
                    <input name="nombre_corto" value="{{ old('nombre_corto', $configuracion->nombre_corto) }}" class="input-premium" placeholder="Ej: La Sed">
                    @error('nombre_corto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">CIF</label>
                    <input name="cif" value="{{ old('cif', $configuracion->cif) }}" class="input-premium">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Dirección</label>
                    <input name="direccion" value="{{ old('direccion', $configuracion->direccion) }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Localidad</label>
                    <input name="localidad" value="{{ old('localidad', $configuracion->localidad) }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">CP</label>
                    <input name="cp" value="{{ old('cp', $configuracion->cp) }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Provincia</label>
                    <input name="provincia" value="{{ old('provincia', $configuracion->provincia) }}" class="input-premium">
                </div>
            </div>

            <div x-show="tab === 'secretaria'" class="grid grid-cols-1 gap-3" x-cloak>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Años mínimos de antigüedad para el censo electoral</label>
                    <input type="number" name="censo_antiguedad_anos" min="0" max="80" value="{{ old('censo_antiguedad_anos', $configuracion->censo_antiguedad_anos ?? 1) }}" class="input-premium max-w-xs">
                    <p class="text-xs text-slate-500 mt-1">Valor por defecto en Informes → Censo (se puede cambiar solo para un informe concreto).</p>
                    @error('censo_antiguedad_anos') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Importe cuota anual por defecto (€)</label>
                    <input type="number" name="importe_cuota_anual_defecto" step="0.01" min="0" value="{{ old('importe_cuota_anual_defecto', $configuracion->importe_cuota_anual_defecto) }}" class="input-premium max-w-xs font-mono" placeholder="Ej. 60">
                    <p class="text-xs text-slate-500 mt-1">Referencia anual para prorratear cuotas en remesas SEPA cuando el hermano no tiene importe propio.</p>
                    @error('importe_cuota_anual_defecto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div x-show="tab === 'contacto'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Teléfono</label>
                    <input name="telefono" value="{{ old('telefono', $configuracion->telefono) }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Email Contacto</label>
                    <input name="email_contacto" value="{{ old('email_contacto', $configuracion->email_contacto) }}" class="input-premium">
                    @error('email_contacto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">IBAN Cuotas</label>
                    <input name="iban_cuotas" value="{{ old('iban_cuotas', $configuracion->iban_cuotas) }}" class="input-premium" placeholder="ES...">
                    @error('iban_cuotas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">BIC/SWIFT</label>
                    <input name="bic_swift" value="{{ old('bic_swift', $configuracion->bic_swift) }}" class="input-premium" placeholder="Ej: BSCHESMMXXX">
                    @error('bic_swift') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div x-show="tab === 'escudo'" class="grid grid-cols-1 gap-3">
                <div>
                    <label class="text-xs font-semibold text-slate-700">Escudo (JPG/PNG/WEBP)</label>
                    <input type="file" name="escudo" accept=".jpg,.jpeg,.png,.webp" class="input-premium">
                    @error('escudo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div x-show="tab === 'firmas'" class="grid grid-cols-1 md:grid-cols-2 gap-4" x-cloak>
                @php
                    $uFirmaSec = $configuracion->firma_secretario_path;
                    $urlFirmaSec = $uFirmaSec && ! str_starts_with($uFirmaSec, 'http') ? \Illuminate\Support\Facades\Storage::url($uFirmaSec) : null;
                    $uFirmaMay = $configuracion->firma_mayordomo_path;
                    $urlFirmaMay = $uFirmaMay && ! str_starts_with($uFirmaMay, 'http') ? \Illuminate\Support\Facades\Storage::url($uFirmaMay) : null;
                    $uSello = $configuracion->sello_hermandad_path;
                    $urlSello = $uSello && ! str_starts_with($uSello, 'http') ? \Illuminate\Support\Facades\Storage::url($uSello) : null;
                @endphp
                <div class="md:col-span-2">
                    <p class="text-xs text-slate-600">PNG con fondo transparente recomendado. Se estamparán automáticamente en los certificados PDF (pertenencia y resumen Hacienda).</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Firma del Secretario (PNG/JPG)</label>
                    <input type="file" name="firma_secretario" accept=".jpg,.jpeg,.png,.webp" class="input-premium mt-1">
                    @error('firma_secretario') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @if ($urlFirmaSec)
                        <p class="text-[10px] uppercase text-slate-500 mt-2">Vista previa actual</p>
                        <img src="{{ $urlFirmaSec }}" alt="Firma secretario" class="mt-1 max-h-16 object-contain border border-slate-200 rounded-lg bg-white p-1">
                    @endif
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-700">Firma del Mayordomo (PNG/JPG)</label>
                    <input type="file" name="firma_mayordomo" accept=".jpg,.jpeg,.png,.webp" class="input-premium mt-1">
                    @error('firma_mayordomo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @if ($urlFirmaMay)
                        <p class="text-[10px] uppercase text-slate-500 mt-2">Vista previa actual</p>
                        <img src="{{ $urlFirmaMay }}" alt="Firma mayordomo" class="mt-1 max-h-16 object-contain border border-slate-200 rounded-lg bg-white p-1">
                    @endif
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Sello de la Hermandad (PNG transparente)</label>
                    <input type="file" name="sello_hermandad" accept=".jpg,.jpeg,.png,.webp" class="input-premium mt-1">
                    @error('sello_hermandad') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @if ($urlSello)
                        <p class="text-[10px] uppercase text-slate-500 mt-2">Vista previa actual</p>
                        <img src="{{ $urlSello }}" alt="Sello" class="mt-2 h-20 w-auto object-contain border border-slate-200 rounded-lg bg-slate-50 p-2">
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'editar-hermandad')" class="btn-soft">Cancelar</button>
                <button type="submit" class="btn-accent">Guardar Cambios</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>

