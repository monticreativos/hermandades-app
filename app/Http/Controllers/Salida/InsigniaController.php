<?php

namespace App\Http\Controllers\Salida;

use App\Http\Controllers\Controller;
use App\Models\Insignia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsigniaController extends Controller
{
    public function index(): JsonResponse
    {
        $insignias = Insignia::query()->orderBy('orden')->get();

        return response()->json(['insignias' => $insignias]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'tramo' => 'required|in:Cristo,Virgen,General',
            'orden' => 'required|integer|min:0',
            'max_portadores' => 'required|integer|min:1',
            'max_acompanantes' => 'required|integer|min:0',
            'notas' => 'nullable|string|max:1000',
        ]);

        $insignia = Insignia::create($data);

        return response()->json($insignia, 201);
    }

    public function update(Request $request, Insignia $insignia): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'tramo' => 'required|in:Cristo,Virgen,General',
            'orden' => 'required|integer|min:0',
            'max_portadores' => 'required|integer|min:1',
            'max_acompanantes' => 'required|integer|min:0',
            'notas' => 'nullable|string|max:1000',
        ]);

        $insignia->update($data);

        return response()->json($insignia);
    }

    public function destroy(Insignia $insignia): JsonResponse
    {
        $insignia->delete();

        return response()->json(null, 204);
    }
}
