<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('papeletas_sitio', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->cascadeOnDelete();
            $table->string('puesto');
            $table->foreignId('insignia_id')->nullable()->constrained('insignias')->nullOnDelete();
            $table->string('tramo')->nullable();
            $table->decimal('donativo_pagado', 8, 2)->default(0);
            $table->enum('estado', ['Solicitada', 'Emitida', 'Anulada'])->default('Solicitada');
            $table->boolean('asistencia')->default(false);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['hermano_id', 'ejercicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('papeletas_sitio');
    }
};
