<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretaria_registros_documentales', function (Blueprint $table): void {
            $table->id();
            $table->date('fecha');
            $table->string('tipo_movimiento', 16); // entrada|salida
            $table->string('remitente_destinatario');
            $table->string('extracto', 500);
            $table->string('tipo_documento', 40);
            $table->string('numero_protocolo', 32)->unique();
            $table->string('archivo_path');
            $table->string('nombre_original');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->default(0);
            $table->string('sello_registro_path')->nullable();
            $table->text('resumen_ia')->nullable();
            $table->foreignId('subido_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['fecha', 'tipo_movimiento'], 'sec_reg_fecha_tipo_idx');
        });

        Schema::create('secretaria_plantillas_documentales', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 40);
            $table->longText('cuerpo_plantilla');
            $table->string('marca_agua', 120)->nullable();
            $table->boolean('activa')->default(true);
            $table->foreignId('creado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('secretaria_entidades_externas', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 40); // hermandad|ayuntamiento|consejo|proveedor_vip|otros
            $table->string('contacto')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 32)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('secretaria_actos_protocolo', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo');
            $table->date('fecha');
            $table->string('lugar')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('secretaria_invitaciones_acto', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('acto_id')->constrained('secretaria_actos_protocolo')->cascadeOnDelete();
            $table->foreignId('entidad_externa_id')->nullable()->constrained('secretaria_entidades_externas')->nullOnDelete();
            $table->string('nombre_invitado');
            $table->string('estado_confirmacion', 24)->default('pendiente'); // pendiente|confirmado|declinado
            $table->unsignedSmallInteger('fila')->nullable();
            $table->unsignedSmallInteger('banco')->nullable();
            $table->unsignedSmallInteger('orden_protocolo')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['acto_id', 'estado_confirmacion'], 'sec_inv_acto_estado_idx');
        });

        if (Schema::hasTable('documentos_archivo') && ! Schema::hasColumn('documentos_archivo', 'resumen_ia')) {
            Schema::table('documentos_archivo', function (Blueprint $table): void {
                $table->text('resumen_ia')->nullable()->after('descripcion');
            });
        }

        Schema::create('documentos_archivo_justificantes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('documento_padre_id')->constrained('documentos_archivo')->cascadeOnDelete();
            $table->foreignId('documento_hijo_id')->constrained('documentos_archivo')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['documento_padre_id', 'documento_hijo_id'], 'doc_arch_just_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_archivo_justificantes');
        if (Schema::hasTable('documentos_archivo') && Schema::hasColumn('documentos_archivo', 'resumen_ia')) {
            Schema::table('documentos_archivo', function (Blueprint $table): void {
                $table->dropColumn('resumen_ia');
            });
        }
        Schema::dropIfExists('secretaria_invitaciones_acto');
        Schema::dropIfExists('secretaria_actos_protocolo');
        Schema::dropIfExists('secretaria_entidades_externas');
        Schema::dropIfExists('secretaria_plantillas_documentales');
        Schema::dropIfExists('secretaria_registros_documentales');
    }
};
