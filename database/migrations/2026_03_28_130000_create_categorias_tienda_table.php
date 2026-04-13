<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_tienda', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        $base = ['Medallas', 'Incienso', 'Libros', 'Ropa', 'Varios'];
        foreach ($base as $idx => $nombre) {
            DB::table('categorias_tienda')->insert([
                'nombre' => $nombre,
                'orden' => $idx + 1,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $existentes = DB::table('productos_tienda')
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->distinct()
            ->pluck('categoria');

        foreach ($existentes as $nombre) {
            $ya = DB::table('categorias_tienda')->where('nombre', $nombre)->exists();
            if (! $ya) {
                DB::table('categorias_tienda')->insert([
                    'nombre' => $nombre,
                    'orden' => 999,
                    'activa' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_tienda');
    }
};
