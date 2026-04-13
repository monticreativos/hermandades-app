<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('numero_asiento');
            $table->date('fecha');
            $table->string('glosa');
            $table->timestamps();

            $table->unique(['ejercicio_id', 'numero_asiento']);
            $table->index(['fecha', 'ejercicio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos');
    }
};
