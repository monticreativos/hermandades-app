<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentas_contables', function (Blueprint $table) {
            $table->foreignId('hermano_id')
                ->nullable()
                ->after('tipo')
                ->constrained('hermanos')
                ->restrictOnDelete();
            $table->foreignId('proveedor_id')
                ->nullable()
                ->after('hermano_id')
                ->constrained('proveedores')
                ->restrictOnDelete();
            $table->unique('hermano_id');
            $table->unique('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::table('cuentas_contables', function (Blueprint $table) {
            $table->dropForeign(['hermano_id']);
            $table->dropForeign(['proveedor_id']);
            $table->dropUnique(['hermano_id']);
            $table->dropUnique(['proveedor_id']);
            $table->dropColumn(['hermano_id', 'proveedor_id']);
        });
    }
};
