<?php

namespace App\Http\Controllers\Ajustes;

use App\Http\Controllers\Controller;
use App\Services\Sistema\SaludSistemaChecker;
use Illuminate\View\View;

class EstadoSistemaController extends Controller
{
    public function __construct(
        private readonly SaludSistemaChecker $saludSistemaChecker
    ) {}

    public function index(): View
    {
        $storage = $this->saludSistemaChecker->enlaceStoragePublico();
        $ejercicios = $this->saludSistemaChecker->ejerciciosContables();
        $hermanosIncompletos = $this->saludSistemaChecker->hermanosDatosIncompletos();
        $cuentasAuxiliares = $this->saludSistemaChecker->cuentasAuxiliaresPendientes();

        return view('ajustes.estado-sistema', compact('storage', 'ejercicios', 'hermanosIncompletos', 'cuentasAuxiliares'));
    }
}
