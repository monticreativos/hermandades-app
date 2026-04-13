<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asientos', function (Blueprint $table): void {
            $table->boolean('movimiento_rapido')->default(false)->after('glosa');
            $table->string('canal_origen', 32)->nullable()->after('movimiento_rapido');
            $table->string('categoria_economia', 48)->nullable()->after('canal_origen');
            $table->foreignId('hermano_id')->nullable()->after('categoria_economia')->constrained('hermanos')->nullOnDelete();
            $table->boolean('apt_modelo_182')->default(false)->after('hermano_id');
            $table->boolean('operacion_exenta_iva')->default(true)->after('apt_modelo_182');
            $table->boolean('renta_is_exenta')->default(true)->after('operacion_exenta_iva');
            $table->decimal('base_imponible', 14, 2)->nullable()->after('renta_is_exenta');
            $table->decimal('cuota_iva', 14, 2)->nullable()->after('base_imponible');
        });
    }

    public function down(): void
    {
        Schema::table('asientos', function (Blueprint $table): void {
            $table->dropForeign(['hermano_id']);
            $table->dropColumn([
                'movimiento_rapido',
                'canal_origen',
                'categoria_economia',
                'hermano_id',
                'apt_modelo_182',
                'operacion_exenta_iva',
                'renta_is_exenta',
                'base_imponible',
                'cuota_iva',
            ]);
        });
    }
};
