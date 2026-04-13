<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\StoreAperturaCajaTiendaRequest;
use App\Models\AperturaCajaTienda;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AperturaCajaTiendaController extends Controller
{
    public function create(): View
    {
        $fecha = request('fecha', now()->toDateString());
        $carbon = Carbon::parse($fecha);

        return view('tienda.apertura-caja.create', [
            'fecha' => $fecha,
            'aperturaExistente' => AperturaCajaTienda::query()->whereDate('fecha', $carbon->toDateString())->first(),
        ]);
    }

    public function store(StoreAperturaCajaTiendaRequest $request): RedirectResponse
    {
        $fecha = Carbon::parse($request->validated('fecha'))->startOfDay();

        AperturaCajaTienda::query()->updateOrCreate(
            ['fecha' => $fecha->toDateString()],
            [
                'user_id' => Auth::id(),
                'saldo_inicial_efectivo' => round((float) $request->validated('saldo_inicial_efectivo'), 2),
                'notas' => $request->validated('notas'),
            ]
        );

        return redirect()
            ->route('tienda.apertura-caja.create', ['fecha' => $fecha->toDateString()])
            ->with('status', 'Apertura de caja registrada (efectivo inicial '.number_format((float) $request->validated('saldo_inicial_efectivo'), 2, ',', '.').' €).');
    }
}
