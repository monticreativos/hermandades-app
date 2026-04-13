<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="py-8 w-full px-2 sm:px-4 lg:px-6">
        <div class="mb-5">
            <h2 class="text-2xl md:text-3xl font-bold text-[color:var(--color-primary)]">Asistente: generar remesa del mes</h2>
            <p class="text-sm text-slate-600 mt-1">Se incluyen automáticamente los hermanos en Alta con IBAN válido: cuota mensual, trimestres que correspondan, semestres y anual según configuración, más periodos atrasados no cobrados.</p>
        </div>

        @include('economia.partials.subnav')

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">
                <ul class="list-disc ps-4">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-900 text-sm border border-rose-200">{{ session('error') }}</div>
        @endif

        <div class="max-w-xl card-premium border-t-2 border-t-[color:var(--color-accent)] p-6">
            <form method="POST" action="{{ route('economia.remesas.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Año de referencia</label>
                        <input type="number" name="año" min="2000" max="2100" value="{{ old('año', now()->year) }}" class="input-premium w-full font-mono" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700">Mes</label>
                        <select name="mes" class="input-premium w-full" required>
                            @php
                                $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                            @endphp
                            @foreach ($meses as $num => $nombre)
                                <option value="{{ $num }}" @selected((int) old('mes', now()->month) === $num)>
                                    {{ str_pad((string) $num, 2, '0', STR_PAD_LEFT) }} — {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700">Fecha de cargo en cuenta (SEPA)</label>
                    <input type="date" name="fecha_cobro" value="{{ old('fecha_cobro', now()->addDay()->format('Y-m-d')) }}" class="input-premium w-full font-mono" required>
                    <p class="text-xs text-slate-500 mt-1">Debe coincidir con la fecha acordada con su entidad (normalmente días laborables).</p>
                </div>
                <div class="flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn-accent uppercase tracking-wider text-xs">Generar XML y guardar remesa</button>
                    <a href="{{ route('economia.remesas.index') }}" class="btn-soft text-xs">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
