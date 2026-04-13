<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('enseres')) {
            Schema::table('enseres', function (Blueprint $table): void {
                if (! Schema::hasColumn('enseres', 'numero_inventario')) {
                    $table->string('numero_inventario', 64)->nullable()->after('id');
                }
                if (! Schema::hasColumn('enseres', 'codigo_qr_uuid')) {
                    $table->char('codigo_qr_uuid', 36)->nullable()->unique()->after('numero_inventario');
                }
                if (! Schema::hasColumn('enseres', 'tipo_ubicacion')) {
                    $table->string('tipo_ubicacion', 32)->nullable()->after('ubicacion');
                }
                if (! Schema::hasColumn('enseres', 'material_tecnica')) {
                    $table->string('material_tecnica')->nullable()->after('materiales');
                }
                if (! Schema::hasColumn('enseres', 'dimensiones')) {
                    $table->string('dimensiones')->nullable()->after('material_tecnica');
                }
                if (! Schema::hasColumn('enseres', 'valor_historico_artistico')) {
                    $table->text('valor_historico_artistico')->nullable()->after('valor_estimado');
                }
            });
        }

        foreach (DB::table('enseres')->whereNull('codigo_qr_uuid')->cursor() as $row) {
            DB::table('enseres')->where('id', $row->id)->update(['codigo_qr_uuid' => (string) Str::uuid()]);
        }

        if (! Schema::hasTable('patrimonio_fotos')) {
            Schema::create('patrimonio_fotos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('enser_id')->constrained('enseres')->cascadeOnDelete();
                $table->string('archivo_path');
                $table->string('leyenda')->nullable();
                $table->string('tipo_foto', 24)->default('general');
                $table->unsignedSmallInteger('orden')->default(0);
                $table->timestamps();

                $table->index(['enser_id', 'orden']);
            });
        }

        if (! Schema::hasTable('patrimonio_mantenimientos')) {
            Schema::create('patrimonio_mantenimientos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('enser_id')->constrained('enseres')->cascadeOnDelete();
                $table->date('fecha');
                $table->string('tipo_actividad', 48);
                $table->text('descripcion')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['enser_id', 'fecha']);
            });
        }

        if (! Schema::hasTable('patrimonio_alertas_conservacion')) {
            Schema::create('patrimonio_alertas_conservacion', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('enser_id')->constrained('enseres')->cascadeOnDelete();
                $table->string('titulo');
                $table->text('descripcion')->nullable();
                $table->unsignedSmallInteger('periodicidad_meses')->nullable();
                $table->date('proxima_revision_fecha')->nullable();
                $table->date('ultima_ejecucion_fecha')->nullable();
                $table->boolean('activa')->default(true);
                $table->timestamps();

                $table->index(['activa', 'proxima_revision_fecha'], 'pat_alertas_activa_rev_idx');
            });
        }

        if (! Schema::hasTable('patrimonio_prestamos')) {
            Schema::create('patrimonio_prestamos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('enser_id')->constrained('enseres')->cascadeOnDelete();
                $table->string('entidad_solicitante');
                $table->date('fecha_salida');
                $table->date('fecha_prevista_devolucion')->nullable();
                $table->date('fecha_devolucion_efectiva')->nullable();
                $table->boolean('seguro_clavo_clavo')->default(false);
                $table->text('notas')->nullable();
                $table->timestamps();

                $table->index(['enser_id', 'fecha_salida']);
            });
        }

        if (! Schema::hasTable('patrimonio_vinculos_contables')) {
            Schema::create('patrimonio_vinculos_contables', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('enser_id')->constrained('enseres')->cascadeOnDelete();
                $table->foreignId('asiento_id')->nullable()->constrained('asientos')->nullOnDelete();
                $table->foreignId('documento_gasto_id')->nullable()->constrained('documentos_gasto')->nullOnDelete();
                $table->string('concepto')->nullable();
                $table->timestamps();

                $table->index(['enser_id']);
            });
        }

        $now = now();
        if (Schema::hasTable('estados_conservacion_patrimonio')) {
            $exists = DB::table('estados_conservacion_patrimonio')->where('nombre', 'Requiere intervención urgente')->exists();
            if (! $exists) {
                DB::table('estados_conservacion_patrimonio')->insert([
                    'nombre' => 'Requiere intervención urgente',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $categoriasNuevas = ['Escultura', 'Talla', 'Pintura', 'Varios'];
        foreach ($categoriasNuevas as $nombre) {
            if (Schema::hasTable('categorias_patrimonio')) {
                $exists = DB::table('categorias_patrimonio')->where('nombre', $nombre)->exists();
                if (! $exists) {
                    DB::table('categorias_patrimonio')->insert([
                        'nombre' => $nombre,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonio_vinculos_contables');
        Schema::dropIfExists('patrimonio_prestamos');
        Schema::dropIfExists('patrimonio_alertas_conservacion');
        Schema::dropIfExists('patrimonio_mantenimientos');
        Schema::dropIfExists('patrimonio_fotos');

        Schema::table('enseres', function (Blueprint $table): void {
            $table->dropColumn([
                'tipo_ubicacion',
                'material_tecnica',
                'dimensiones',
                'valor_historico_artistico',
                'numero_inventario',
                'codigo_qr_uuid',
            ]);
        });

        if (Schema::hasTable('estados_conservacion_patrimonio')) {
            DB::table('estados_conservacion_patrimonio')->where('nombre', 'Requiere intervención urgente')->delete();
        }

        $categoriasNuevas = ['Escultura', 'Talla', 'Pintura', 'Varios'];
        foreach ($categoriasNuevas as $nombre) {
            if (Schema::hasTable('categorias_patrimonio')) {
                DB::table('categorias_patrimonio')->where('nombre', $nombre)->delete();
            }
        }
    }
};
