<?php

namespace App\Http\Controllers;

use App\Models\ComunicadoMasivoDestinatario;
use Illuminate\Http\Response;

class ComunicadoMasivoTrackController extends Controller
{
    /**
     * Pixel de apertura (1×1 GIF). Sin autenticación.
     */
    public function __invoke(string $token): Response
    {
        $destinatario = ComunicadoMasivoDestinatario::query()
            ->where('tracking_token', $token)
            ->first();

        if ($destinatario) {
            $destinatario->registrarApertura(request()->ip());
        }

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
