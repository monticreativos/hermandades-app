<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_gasto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_id')->constrained('asientos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedTinyInteger('orden_linea');
            $table->string('archivo_path');
            $table->string('nombre_original');
            $table->string('mime_type', 120)->nullable();
            $table->string('proveedor')->nullable();
            $table->string('estado'); // Pendiente, Pagada
            $table->decimal('importe_linea', 12, 2)->nullable();
            $table->date('fecha_documento')->nullable();
            $table->timestamps();

            $table->unique(['asiento_id', 'orden_linea']);
            $table->index(['estado', 'fecha_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_gasto');
    }
};
