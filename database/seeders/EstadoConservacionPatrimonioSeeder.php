<?php

namespace Database\Seeders;

use App\Models\EstadoConservacionPatrimonio;
use Illuminate\Database\Seeder;

class EstadoConservacionPatrimonioSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = ['Excelente', 'Bueno', 'Regular', 'Necesita Restauración', 'En Restauración'];
        foreach ($nombres as $nombre) {
            EstadoConservacionPatrimonio::query()->firstOrCreate(['nombre' => $nombre]);
        }
    }
}
