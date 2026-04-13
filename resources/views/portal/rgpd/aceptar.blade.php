@extends('layouts.portal-fullscreen')

@section('title', 'Protección de datos')

@section('content')
    <div class="flex-1 flex flex-col justify-center max-w-lg mx-auto w-full">
        <div class="rounded-2xl border border-[color:var(--color-accent)]/35 bg-slate-900/80 shadow-2xl backdrop-blur-sm overflow-hidden flex flex-col max-h-[min(85dvh,calc(100dvh-8rem))]">
            <div class="shrink-0 px-5 pt-5 pb-3 border-b border-white/10">
                <h1 class="text-xl font-bold text-white tracking-tight">Protección de datos personales</h1>
                <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                    Debe leer y aceptar la información antes de usar el portal. Hermano n.º <span class="text-[color:var(--color-accent)] font-semibold">{{ $hermano->numero_hermano }}</span>
                </p>
            </div>

            <div class="flex-1 min-h-0 overflow-y-auto px-5 py-4 text-sm text-slate-300 leading-relaxed space-y-4">
                <p>
                    En cumplimiento del Reglamento (UE) 2016/679 (RGPD) y la Ley Orgánica 3/2018 de protección de datos y garantía de los derechos digitales,
                    le informamos de que los datos personales facilitados serán tratados por la Hermandad con la finalidad de gestionar su condición de hermano,
                    comunicaciones institucionales, cuotas, salidas procesionales y obligaciones legalmente exigibles.
                </p>
                <p>
                    Puede ejercer los derechos de acceso, rectificación, supresión, limitación, oposición y portabilidad dirigiéndose a la Secretaría de la Hermandad,
                    identificándose como hermano y aportando la documentación que proceda.
                </p>
                <p class="text-xs text-slate-500">
                    El responsable del tratamiento es la Hermandad. Conservaremos los datos el tiempo necesario para cumplir las finalidades indicadas y las obligaciones legales.
                    Puede retirar el consentimiento cuando no afecte a un tratamiento basado en obligación legal.
                </p>
            </div>

            <form method="POST" action="{{ route('portal.rgpd.accept') }}" class="shrink-0 border-t border-white/10 p-5 space-y-4 bg-slate-950/50">
                @csrf
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" name="acepto_rgpd" value="1" class="mt-1 rounded border-slate-500 text-[color:var(--color-accent)] focus:ring-[color:var(--color-accent)]" required />
                    <span class="text-sm text-slate-200 group-hover:text-white transition">
                        He leído la información y <strong class="text-[color:var(--color-accent)]">acepto el tratamiento de mis datos personales</strong> conforme a lo indicado, para poder utilizar el Portal del Hermano.
                    </span>
                </label>
                @error('acepto_rgpd')
                    <p class="text-sm font-medium text-rose-300">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-bold uppercase tracking-wider bg-[color:var(--color-accent)] text-[color:var(--color-primary)] shadow-lg shadow-black/20 hover:brightness-110 active:scale-[0.99] transition">
                    Aceptar y continuar
                </button>
            </form>
        </div>
    </div>
@endsection
