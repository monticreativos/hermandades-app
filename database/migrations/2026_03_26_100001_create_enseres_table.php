<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enseres', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('categoria_id')->constrained('categorias_patrimonio')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('ubicacion')->nullable();
            $table->string('autor')->nullable();
            $table->unsignedSmallInteger('año_creacion')->nullable();
            $table->string('materiales')->nullable();
            $table->string('estado_conservacion');
            $table->decimal('valor_estimado', 12, 2)->nullable();
            $table->text('descripcion_detallada')->nullable();
            $table->date('ultima_revision')->nullable();
            $table->string('imagen_principal_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enseres');
    }
};
