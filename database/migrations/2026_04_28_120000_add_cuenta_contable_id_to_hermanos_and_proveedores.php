<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            $table->foreignId('cuenta_contable_id')
                ->nullable()
                ->after('cuota_pendiente_ejercicio_id')
                ->constrained('cuentas_contables')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::table('proveedores', function (Blueprint $table): void {
            $table->foreignId('cuenta_contable_id')
                ->nullable()
                ->after('notas')
                ->constrained('cuentas_contables')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cuenta_contable_id');
        });

        Schema::table('proveedores', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('cuenta_contable_id');
        });
    }
};
