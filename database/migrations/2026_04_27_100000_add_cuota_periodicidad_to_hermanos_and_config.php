<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            $table->string('periodicidad_pago', 24)->default('Mensual')->after('cuota_pendiente_ejercicio_id');
            $table->decimal('importe_cuota_anual_referencia', 10, 2)->nullable()->after('periodicidad_pago');
            $table->json('periodos_cuota_cobrados_json')->nullable()->after('importe_cuota_anual_referencia');
        });

        Schema::table('configuracion_hermandad', function (Blueprint $table): void {
            $table->decimal('importe_cuota_anual_defecto', 10, 2)->nullable()->after('iban_cuotas');
        });
    }

    public function down(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            $table->dropColumn(['periodicidad_pago', 'importe_cuota_anual_referencia', 'periodos_cuota_cobrados_json']);
        });

        Schema::table('configuracion_hermandad', function (Blueprint $table): void {
            $table->dropColumn('importe_cuota_anual_defecto');
        });
    }
};
