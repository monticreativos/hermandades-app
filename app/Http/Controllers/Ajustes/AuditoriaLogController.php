<?php

namespace App\Http\Controllers\Ajustes;

use App\Http\Controllers\Controller;
use App\Models\AuditoriaLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaLogController extends Controller
{
    public function index(Request $request): View
    {
        $q = AuditoriaLog::query()
            ->with([
                'user:id,name,email',
                'portalCuenta:id,email,hermano_id',
                'hermano:id,numero_hermano,nombre,apellidos',
            ]);

        if ($request->filled('canal')) {
            $q->where('canal', $request->string('canal')->toString());
        }

        if ($request->filled('evento')) {
            $q->where('evento', 'like', '%'.$request->string('evento')->toString().'%');
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $q->where(function ($w) use ($term): void {
                $w->where('descripcion', 'like', $term)
                    ->orWhere('email_intento', 'like', $term)
                    ->orWhere('ip_address', 'like', $term)
                    ->orWhere('path', 'like', $term);
            });
        }

        $logs = $q->orderByDesc('id')->paginate(40)->withQueryString();

        return view('ajustes.auditoria.index', [
            'logs' => $logs,
        ]);
    }
}
