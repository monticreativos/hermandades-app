<?php

namespace Database\Seeders;

use App\Models\CategoriaPatrimonio;
use App\Models\Enser;
use App\Models\EstadoConservacionPatrimonio;
use Illuminate\Database\Seeder;

class EnserSeeder extends Seeder
{
    public function run(): void
    {
        $catOrfe = CategoriaPatrimonio::query()->where('nombre', 'Orfebrería')->first();
        $catBord = CategoriaPatrimonio::query()->where('nombre', 'Bordados')->first();
        $catVarios = CategoriaPatrimonio::query()->where('nombre', 'Varios')->first();

        $estExcelente = EstadoConservacionPatrimonio::query()->where('nombre', 'Excelente')->value('id');
        $estBueno = EstadoConservacionPatrimonio::query()->where('nombre', 'Bueno')->value('id');
        $estRest = EstadoConservacionPatrimonio::query()->where('nombre', 'Necesita Restauración')->value('id');

        if (! $catOrfe || ! $catBord || ! $catVarios || ! $estExcelente || ! $estBueno || ! $estRest) {
            return;
        }

        $items = [
            [
                'nombre' => 'Respiraderos del Paso de Palio',
                'categoria_id' => $catOrfe->id,
                'ubicacion' => 'Casa Hermandad',
                'autor' => 'Orfebrería sevillana',
                'año_creacion' => 1988,
                'materiales' => 'Plata de ley',
                'estado_conservacion_id' => $estExcelente,
                'valor_estimado' => 45000.00,
                'descripcion_detallada' => 'Juego de respiraderos para el palio, revisión anual en archivo fotográfico.',
                'ultima_revision' => now()->subMonths(3)->toDateString(),
            ],
            [
                'nombre' => 'Manto bordado en oro fino',
                'categoria_id' => $catBord->id,
                'ubicacion' => 'Vitrina 4',
                'autor' => 'Taller de bordado',
                'año_creacion' => 1995,
                'materiales' => 'Seda, hilo de oro',
                'estado_conservacion_id' => $estBueno,
                'valor_estimado' => 28000.00,
                'descripcion_detallada' => 'Manto de salida con escudo corporativo.',
                'ultima_revision' => now()->subYear()->toDateString(),
            ],
            [
                'nombre' => 'Cruz de guía (réplica histórica)',
                'categoria_id' => $catVarios->id,
                'ubicacion' => 'Almacén',
                'autor' => null,
                'año_creacion' => 1960,
                'materiales' => 'Madera de cedro, metal',
                'estado_conservacion_id' => $estRest,
                'valor_estimado' => 3200.00,
                'descripcion_detallada' => 'Pieza sensible a humedad; priorizar intervención en taller especializado.',
                'ultima_revision' => now()->subMonths(18)->toDateString(),
            ],
        ];

        foreach ($items as $row) {
            Enser::query()->updateOrCreate(
                ['nombre' => $row['nombre']],
                $row
            );
        }
    }
}
