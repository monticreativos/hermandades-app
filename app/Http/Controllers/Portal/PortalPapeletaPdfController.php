<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Salida\PapeletaPdfController;
use App\Models\PapeletaSitio;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PortalPapeletaPdfController extends Controller
{
    public function __invoke(PapeletaSitio $papeleta): Response
    {
        $cuenta = Auth::guard('portal')->user();

        abort_unless(
            $cuenta && (int) $cuenta->hermano_id === (int) $papeleta->hermano_id,
            403,
            'No autorizado.'
        );

        abort_unless(
            $papeleta->estado === PapeletaSitio::ESTADO_EMITIDA,
            403,
            'La papeleta aún no está disponible para descarga.'
        );

        return app(PapeletaPdfController::class)->papeleta($papeleta);
    }
}
