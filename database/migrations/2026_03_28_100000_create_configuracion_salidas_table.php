<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_salidas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('año')->unique();
            $table->date('fecha_salida')->nullable();
            $table->decimal('donativo_defecto', 8, 2)->default(0);
            $table->date('fecha_inicio_reparto')->nullable();
            $table->date('fecha_fin_reparto')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_salidas');
    }
};
