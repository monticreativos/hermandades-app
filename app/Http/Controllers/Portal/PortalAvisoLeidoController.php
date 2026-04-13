<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AvisoHermano;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PortalAvisoLeidoController extends Controller
{
    public function __invoke(AvisoHermano $avisoHermano): RedirectResponse
    {
        $cuenta = Auth::guard('portal')->user();
        abort_unless(
            $cuenta && (int) $cuenta->hermano_id === (int) $avisoHermano->hermano_id,
            403
        );

        $avisoHermano->marcarLeido();

        return back();
    }
}
