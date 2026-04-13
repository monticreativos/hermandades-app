<?php

namespace App\Services\Informes;

use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use App\Models\LoteriaAsignacion;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CertificadoHermanoService
{
    public function datosPertenencia(Hermano $hermano, ConfiguracionHermandad $hermandad): array
    {
        $ref = Carbon::now()->startOfDay();

        return array_merge($this->assets($hermandad), [
            'hermano' => $hermano,
            'hermandad' => $hermandad,
            'fechaEmision' => $ref,
            'antiguedadTexto' => $this->antiguedadTexto($hermano, $ref),
        ]);
    }

    /**
     * @return array{lineas: Collection, total: float, año: int}
     */
    public function datosCuotasHacienda(Hermano $hermano, ConfiguracionHermandad $hermandad, int $año): array
    {
        $lineas = LoteriaAsignacion::query()
            ->where('hermano_id', $hermano->id)
            ->where('cobrado', true)
            ->whereYear('fecha_cobro', $año)
            ->with('loteria')
            ->orderBy('fecha_cobro')
            ->get();

        $total = (float) $lineas->sum('importe_a_cobrar');

        $cuotaOrdinariaPendienteEsteAño = in_array($hermano->estado_cuota, ['Pendiente', 'Impagada'], true)
            && $hermano->cuotaPendienteEjercicio
            && (int) $hermano->cuotaPendienteEjercicio->año === $año;

        return array_merge($this->assets($hermandad), [
            'hermano' => $hermano,
            'hermandad' => $hermandad,
            'fechaEmision' => Carbon::now()->startOfDay(),
            'año' => $año,
            'lineasCuotas' => $lineas,
            'totalCuotas' => $total,
            'cuotaOrdinariaPendienteEsteAño' => $cuotaOrdinariaPendienteEsteAño,
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function assets(ConfiguracionHermandad $hermandad): array
    {
        return [
            'escudoDataUri' => $this->embedPublicImage($hermandad->escudo_path),
            'firmaSecretarioDataUri' => $this->embedPublicImage($hermandad->firma_secretario_path),
            'firmaMayordomoDataUri' => $this->embedPublicImage($hermandad->firma_mayordomo_path),
            'selloDataUri' => $this->embedPublicImage($hermandad->sello_hermandad_path),
        ];
    }

    private function embedPublicImage(?string $relativePath): ?string
    {
        if (! $relativePath || str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return null;
        }

        $abs = Storage::disk('public')->path($relativePath);
        if (! is_file($abs) || ! is_readable($abs)) {
            return null;
        }

        $mime = @mime_content_type($abs) ?: 'image/png';
        $data = base64_encode((string) file_get_contents($abs));

        return 'data:'.$mime.';base64,'.$data;
    }

    private function antiguedadTexto(Hermano $hermano, Carbon $ref): string
    {
        if (! $hermano->fecha_alta) {
            return 'No consta fecha de alta en el registro.';
        }

        $alta = $hermano->fecha_alta->copy()->startOfDay();
        $años = (int) $alta->diffInYears($ref);
        $meses = (int) $alta->copy()->addYears($años)->diffInMonths($ref);

        if ($años === 0 && $meses === 0) {
            return 'Menos de un mes de antigüedad cofrade.';
        }

        $partes = [];
        if ($años > 0) {
            $partes[] = $años.' '.($años === 1 ? 'año' : 'años');
        }
        if ($meses > 0) {
            $partes[] = $meses.' '.($meses === 1 ? 'mes' : 'meses');
        }

        return implode(' y ', $partes).' de pertenencia a la Hermandad.';
    }
}
