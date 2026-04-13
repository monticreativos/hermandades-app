<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos_externos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('entidad_institucion')->nullable();
            $table->string('cargo')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('direccion')->nullable();
            $table->string('categoria', 60);
            $table->timestamps();
            $table->index(['categoria', 'nombre'], 'cont_ext_cat_nom_idx');
        });

        Schema::create('contacto_externo_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('contacto_externo_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contacto_externo_id')->constrained('contactos_externos')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('contacto_externo_tags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['contacto_externo_id', 'tag_id'], 'cont_ext_tag_unique');
        });

        Schema::table('secretaria_registros_documentales', function (Blueprint $table): void {
            $table->foreignId('hermano_relacionado_id')->nullable()->after('remitente_destinatario')->constrained('hermanos')->nullOnDelete();
            $table->foreignId('contacto_externo_id')->nullable()->after('hermano_relacionado_id')->constrained('contactos_externos')->nullOnDelete();
        });

        Schema::table('secretaria_invitaciones_acto', function (Blueprint $table): void {
            $table->foreignId('hermano_id')->nullable()->after('entidad_externa_id')->constrained('hermanos')->nullOnDelete();
            $table->foreignId('contacto_externo_id')->nullable()->after('hermano_id')->constrained('contactos_externos')->nullOnDelete();
            $table->string('categoria_fuente', 60)->nullable()->after('nombre_invitado');
        });

        Schema::table('comunicados_masivos', function (Blueprint $table): void {
            $table->string('filtro_contacto_categoria', 60)->nullable()->after('filtro_tramo_valor');
            $table->json('audiencia_mixta')->nullable()->after('filtro_contacto_categoria');
            $table->json('destinatarios_individuales')->nullable()->after('audiencia_mixta');
        });

        Schema::table('comunicado_masivo_destinatarios', function (Blueprint $table): void {
            $table->foreignId('contacto_externo_id')->nullable()->after('hermano_id')->constrained('contactos_externos')->nullOnDelete();
            $table->string('nombre_destinatario')->nullable()->after('tracking_token');
            $table->string('email_destinatario')->nullable()->after('nombre_destinatario');
        });
    }

    public function down(): void
    {
        Schema::table('comunicado_masivo_destinatarios', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('contacto_externo_id');
            $table->dropColumn(['nombre_destinatario', 'email_destinatario']);
        });
        Schema::table('comunicados_masivos', function (Blueprint $table): void {
            $table->dropColumn(['filtro_contacto_categoria', 'audiencia_mixta', 'destinatarios_individuales']);
        });
        Schema::table('secretaria_invitaciones_acto', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('hermano_id');
            $table->dropConstrainedForeignId('contacto_externo_id');
            $table->dropColumn('categoria_fuente');
        });
        Schema::table('secretaria_registros_documentales', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('hermano_relacionado_id');
            $table->dropConstrainedForeignId('contacto_externo_id');
        });
        Schema::dropIfExists('contacto_externo_tag');
        Schema::dropIfExists('contacto_externo_tags');
        Schema::dropIfExists('contactos_externos');
    }
};
