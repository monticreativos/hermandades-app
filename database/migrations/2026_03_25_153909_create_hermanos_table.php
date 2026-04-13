<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hermanos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero_hermano')->unique();
            $table->string('nombre');
            $table->string('apellidos');
            $table->string('dni')->unique();
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['Hombre', 'Mujer', 'Otro']);
            $table->string('direccion')->nullable();
            $table->string('localidad')->nullable();
            $table->string('provincia')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('iban')->nullable();
            $table->string('titular_cuenta')->nullable();
            $table->date('fecha_alta')->nullable();
            $table->date('fecha_bautismo')->nullable();
            $table->string('parroquia_bautismo')->nullable();
            $table->enum('estado', ['Alta', 'Baja', 'Difunto'])->default('Alta');
            $table->text('observaciones')->nullable();
            $table->string('partida_bautismo_path')->nullable();
            $table->string('dni_escaneado_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hermanos');
    }
};
