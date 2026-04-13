<?php

namespace App\Services\Informes;

use App\Models\Apunte;
use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ExtractoContableHermanoPdfService
{
    /**
     * @return array{pdf: \Barryvdh\DomPDF\PDF, nombre_archivo: string}
     */
    public function generar(Hermano $hermano, ConfiguracionHermandad $hermandad): array
    {
        $hermano->loadMissing('cuentaContable');
        $cuenta = $hermano->cuentaContable;
        $movimientos = $cuenta
            ? Apunte::extractoSubcuentaConSaldo($cuenta->id)
            : collect();

        $pdf = Pdf::loadView('informes.pdf.hermano-extracto-contable', [
            'hermano' => $hermano,
            'hermandad' => $hermandad,
            'cuenta' => $cuenta,
            'movimientos' => $movimientos,
            'escudoDataUri' => $this->embedPublicImage($hermandad->escudo_path),
            'fechaEmision' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $slug = 'extracto_contable_n'.preg_replace('/\W+/', '_', (string) $hermano->numero_hermano).'.pdf';

        return ['pdf' => $pdf, 'nombre_archivo' => $slug];
    }

    public function outputBinary(Hermano $hermano, ConfiguracionHermandad $hermandad): string
    {
        $g = $this->generar($hermano, $hermandad);

        return $g['pdf']->output();
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
}
