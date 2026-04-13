<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuadrillas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('año');
            $table->string('nombre');
            $table->string('paso', 16); // cristo|virgen
            $table->foreignId('capataz_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('numero_trabajaderas')->default(8);
            $table->unsignedSmallInteger('puestos_por_trabajadera')->default(4);
            $table->text('notas')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['año', 'paso']);
        });

        Schema::create('costalero_perfiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->foreignId('cuadrilla_id')->nullable()->constrained('cuadrillas')->nullOnDelete();
            $table->unsignedSmallInteger('altura_cm')->nullable();
            $table->unsignedSmallInteger('calzado_talla')->nullable();
            $table->string('ropa_talla', 16)->nullable();
            $table->unsignedSmallInteger('trabajadera_numero')->nullable();
            $table->string('palo', 32)->nullable(); // costero_izquierdo, costero_derecho, fijador, corriente
            $table->unsignedSmallInteger('anios_cuadrilla')->default(0);
            $table->text('alergias')->nullable();
            $table->text('lesiones')->nullable();
            $table->timestamps();

            $table->unique('hermano_id');
            $table->index(['cuadrilla_id', 'trabajadera_numero']);
        });

        Schema::create('ensayos_cuadrilla', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cuadrilla_id')->constrained('cuadrillas')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->string('lugar')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['cuadrilla_id', 'fecha']);
        });

        Schema::create('ensayo_asistencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ensayo_id')->constrained('ensayos_cuadrilla')->cascadeOnDelete();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->boolean('asistio')->default(false);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['ensayo_id', 'hermano_id']);
            $table->index(['hermano_id', 'asistio']);
        });

        Schema::create('relevos_cuadrilla', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cuadrilla_id')->constrained('cuadrillas')->cascadeOnDelete();
            $table->string('titulo');
            $table->date('fecha_salida');
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('relevo_detalles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('relevo_id')->constrained('relevos_cuadrilla')->cascadeOnDelete();
            $table->string('punto');
            $table->time('hora_desde')->nullable();
            $table->time('hora_hasta')->nullable();
            $table->string('turno', 64)->nullable();
            $table->foreignId('hermano_id')->nullable()->constrained('hermanos')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['relevo_id', 'hora_desde']);
        });

        Schema::create('cuadrilla_avisos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cuadrilla_id')->constrained('cuadrillas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            $table->text('mensaje');
            $table->timestamp('enviado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuadrilla_avisos');
        Schema::dropIfExists('relevo_detalles');
        Schema::dropIfExists('relevos_cuadrilla');
        Schema::dropIfExists('ensayo_asistencias');
        Schema::dropIfExists('ensayos_cuadrilla');
        Schema::dropIfExists('costalero_perfiles');
        Schema::dropIfExists('cuadrillas');
    }
};
