<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicados_masivos', function (Blueprint $table): void {
            $table->id();
            $table->string('asunto');
            $table->longText('cuerpo_html');
            $table->string('filtro_envio', 40);
            $table->string('filtro_tramo_valor', 120)->nullable();
            $table->foreignId('creado_por_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('estado', 24)->default('encolado');
            $table->unsignedInteger('total_destinatarios')->default(0);
            $table->unsignedInteger('correos_enviados')->default(0);
            $table->timestamp('finalizado_en')->nullable();
            $table->timestamps();
        });

        Schema::create('comunicado_masivo_destinatarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('comunicado_masivo_id')->constrained('comunicados_masivos')->cascadeOnDelete();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->uuid('tracking_token');
            $table->timestamp('correo_enviado_en')->nullable();
            $table->timestamp('abierto_en')->nullable();
            $table->unsignedSmallInteger('aperturas_count')->default(0);
            $table->string('ultima_apertura_ip', 45)->nullable();
            $table->timestamps();

            $table->unique(['comunicado_masivo_id', 'hermano_id'], 'cmd_dest_com_herm_uq');
            $table->unique('tracking_token', 'cmd_dest_track_uq');
            $table->index(['hermano_id', 'comunicado_masivo_id'], 'cmd_dest_herm_com_idx');
        });

        Schema::create('documentos_archivo', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo');
            $table->string('categoria', 48);
            $table->string('nivel_acceso', 24);
            $table->text('descripcion')->nullable();
            $table->string('archivo_path');
            $table->string('nombre_original')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->foreignId('subido_por_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['categoria', 'nivel_acceso']);
        });

        Schema::create('firma_conformidad_solicitudes', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->foreignId('documento_archivo_id')->nullable()->constrained('documentos_archivo')->nullOnDelete();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->string('estado', 16)->default('pendiente');
            $table->timestamp('firmado_en')->nullable();
            $table->string('firmado_ip', 45)->nullable();
            $table->foreignId('creado_por_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['hermano_id', 'estado']);
        });

        Schema::table('avisos', function (Blueprint $table): void {
            $table->boolean('urgente')->default(false)->after('solo_portal');
            $table->boolean('visible_tablon')->default(true)->after('urgente');
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table): void {
            $table->dropColumn(['urgente', 'visible_tablon']);
        });
        Schema::dropIfExists('firma_conformidad_solicitudes');
        Schema::dropIfExists('documentos_archivo');
        Schema::dropIfExists('comunicado_masivo_destinatarios');
        Schema::dropIfExists('comunicados_masivos');
    }
};
