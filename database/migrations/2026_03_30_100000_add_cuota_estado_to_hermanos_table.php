<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            if (! Schema::hasColumn('hermanos', 'estado_cuota')) {
                $table->string('estado_cuota', 24)->default('Al_corriente')->after('estado');
            }
            if (! Schema::hasColumn('hermanos', 'cuota_pendiente_ejercicio_id')) {
                $table->foreignId('cuota_pendiente_ejercicio_id')
                    ->nullable()
                    ->after('estado_cuota')
                    ->constrained('ejercicios')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            if (Schema::hasColumn('hermanos', 'cuota_pendiente_ejercicio_id')) {
                $table->dropConstrainedForeignId('cuota_pendiente_ejercicio_id');
            }
            if (Schema::hasColumn('hermanos', 'estado_cuota')) {
                $table->dropColumn('estado_cuota');
            }
        });
    }
};
