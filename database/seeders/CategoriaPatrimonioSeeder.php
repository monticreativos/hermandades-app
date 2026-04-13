<?php

namespace Database\Seeders;

use App\Models\CategoriaPatrimonio;
use Illuminate\Database\Seeder;

class CategoriaPatrimonioSeeder extends Seeder
{
    public function run(): void
    {
        $nombres = [
            'Orfebrería',
            'Bordados',
            'Imaginería',
            'Talla',
            'Textiles',
            'Varios',
        ];

        foreach ($nombres as $nombre) {
            CategoriaPatrimonio::query()->firstOrCreate(['nombre' => $nombre]);
        }
    }
}
