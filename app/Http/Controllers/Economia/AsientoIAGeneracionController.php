<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Services\IA\OpenRouterAsientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AsientoIAGeneracionController extends Controller
{
    public function __invoke(Request $request, OpenRouterAsientoService $ia): JsonResponse
    {
        $data = $request->validate([
            'descripcion' => ['required', 'string', 'min:12', 'max:2500'],
            'tratamiento_iva' => ['nullable', 'string', 'in:auto,soportado,repercutido,sin_iva'],
        ]);

        try {
            $asiento = $ia->generarAsientoDesdeDescripcion(
                (string) $data['descripcion'],
                isset($data['tratamiento_iva']) ? (string) $data['tratamiento_iva'] : 'auto'
            );
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages([
                'descripcion' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'glosa' => $asiento['glosa'],
            'lineas' => $asiento['lineas'],
        ]);
    }
}
