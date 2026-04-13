<?php

namespace App\Http\Controllers\Ajustes;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use Illuminate\View\View;

class ActividadRegistroController extends Controller
{
    public function index(): View
    {
        $actividades = Actividad::query()
            ->with('user:id,name,email')
            ->orderByDesc('created_at')
            ->paginate(40);

        return view('ajustes.actividades.index', compact('actividades'));
    }
}
