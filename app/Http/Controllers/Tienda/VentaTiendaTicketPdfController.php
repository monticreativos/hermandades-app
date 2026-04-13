<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionHermandad;
use App\Models\VentaTienda;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class VentaTiendaTicketPdfController extends Controller
{
    public function __invoke(VentaTienda $ventaTienda): Response
    {
        $ventaTienda->load(['lineas.producto', 'hermano.cuentaContable', 'user']);

        $hermandad = ConfiguracionHermandad::query()->first();

        $escudoDataUri = null;
        if ($hermandad?->escudo_path && ! str_starts_with((string) $hermandad->escudo_path, 'http')) {
            $rel = ltrim((string) $hermandad->escudo_path, '/');
            $full = storage_path('app/public/'.$rel);
            if (is_file($full)) {
                $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
                $mime = match ($ext) {
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };
                $escudoDataUri = 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($full));
            }
        }

        $pdf = Pdf::loadView('tienda.pdf.ticket-termico', [
            'venta' => $ventaTienda,
            'hermandad' => $hermandad,
            'escudoDataUri' => $escudoDataUri,
        ]);

        $anchoPt = round(80 * 72 / 25.4, 2);
        $pdf->setPaper([0, 0, $anchoPt, 900], 'portrait');

        return $pdf->stream('ticket_'.$ventaTienda->folio.'.pdf');
    }
}
