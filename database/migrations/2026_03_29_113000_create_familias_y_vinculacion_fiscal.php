<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familias', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 180);
            $table->boolean('pago_unificado')->default(false);
            $table->foreignId('pagador_hermano_id')->nullable()->constrained('hermanos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('familia_hermano', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('familia_id')->constrained('familias')->cascadeOnDelete();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->string('parentesco', 40)->default('Hijo/a');
            $table->timestamps();
            $table->unique(['familia_id', 'hermano_id'], 'familia_hermano_unique');
        });

        Schema::table('hermanos', function (Blueprint $table): void {
            if (! Schema::hasColumn('hermanos', 'es_cabeza_familia')) {
                $table->boolean('es_cabeza_familia')->default(false)->after('estado');
            }
            if (! Schema::hasColumn('hermanos', 'beneficiario_fiscal_hermano_id')) {
                $table->foreignId('beneficiario_fiscal_hermano_id')->nullable()->after('es_cabeza_familia')
                    ->constrained('hermanos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            if (Schema::hasColumn('hermanos', 'beneficiario_fiscal_hermano_id')) {
                $table->dropConstrainedForeignId('beneficiario_fiscal_hermano_id');
            }
            if (Schema::hasColumn('hermanos', 'es_cabeza_familia')) {
                $table->dropColumn('es_cabeza_familia');
            }
        });

        Schema::dropIfExists('familia_hermano');
        Schema::dropIfExists('familias');
    }
};
