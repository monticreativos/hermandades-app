<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remesas_sepa', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ejercicio_id')->constrained('ejercicios')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_emision');
            $table->unsignedSmallInteger('año_referencia');
            $table->unsignedTinyInteger('mes_referencia');
            $table->unsignedTinyInteger('trimestre_referencia')->nullable();
            $table->string('etiqueta_periodo', 64);
            $table->unsignedInteger('numero_recibos')->default(0);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->string('archivo_xml_path')->nullable();
            $table->string('msg_id', 64)->nullable();
            $table->string('pmt_inf_id', 64)->nullable();
            $table->string('estado', 32)->default('enviada');
            $table->foreignId('asiento_conciliacion_id')->nullable()->constrained('asientos')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['año_referencia', 'mes_referencia']);
            $table->index('estado');
        });

        Schema::create('remesa_recibos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('remesa_id')->constrained('remesas_sepa')->cascadeOnDelete();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->string('end_to_end_id', 70)->unique();
            $table->string('periodo_clave', 32);
            $table->decimal('importe', 12, 2);
            $table->string('estado', 24)->default('pendiente_banco');
            $table->timestamp('fecha_estado')->nullable();
            $table->string('motivo_devolucion', 500)->nullable();
            $table->string('codigo_devolucion', 32)->nullable();
            $table->foreignId('asiento_cobro_id')->nullable()->constrained('asientos')->nullOnDelete();
            $table->decimal('comision_banco', 12, 2)->nullable();
            $table->foreignId('asiento_comision_id')->nullable()->constrained('asientos')->nullOnDelete();
            $table->timestamps();

            $table->index(['remesa_id', 'estado']);
            $table->index(['hermano_id', 'estado']);
        });

        Schema::create('importaciones_respuesta_banco', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('remesa_id')->nullable()->constrained('remesas_sepa')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo_archivo', 24);
            $table->string('archivo_path');
            $table->string('nombre_original', 255)->nullable();
            $table->json('resultado_json')->nullable();
            $table->unsignedInteger('recibos_cobrados')->default(0);
            $table->unsignedInteger('recibos_devueltos')->default(0);
            $table->unsignedInteger('recibos_no_encontrados')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_respuesta_banco');
        Schema::dropIfExists('remesa_recibos');
        Schema::dropIfExists('remesas_sepa');
    }
};
