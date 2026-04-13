<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_tienda_imagenes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('producto_tienda_id')->constrained('productos_tienda')->cascadeOnDelete();
            $table->string('archivo_path');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('es_principal')->default(false);
            $table->timestamps();

            $table->index(['producto_tienda_id', 'orden'], 'prod_tienda_img_orden_idx');
            $table->index(['producto_tienda_id', 'es_principal'], 'prod_tienda_img_principal_idx');
        });

        $productos = DB::table('productos_tienda')
            ->whereNotNull('imagen_path')
            ->where('imagen_path', '!=', '')
            ->select(['id', 'imagen_path'])
            ->get();

        foreach ($productos as $producto) {
            DB::table('producto_tienda_imagenes')->insert([
                'producto_tienda_id' => $producto->id,
                'archivo_path' => $producto->imagen_path,
                'orden' => 1,
                'es_principal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_tienda_imagenes');
    }
};
