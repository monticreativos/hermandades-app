<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_hermandad', function (Blueprint $table): void {
            $after = Schema::hasColumn('configuracion_hermandad', 'censo_antiguedad_anos')
                ? 'censo_antiguedad_anos'
                : 'escudo_path';
            if (! Schema::hasColumn('configuracion_hermandad', 'firma_secretario_path')) {
                $table->string('firma_secretario_path')->nullable()->after($after);
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'firma_mayordomo_path')) {
                $table->string('firma_mayordomo_path')->nullable();
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'sello_hermandad_path')) {
                $table->string('sello_hermandad_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_hermandad', function (Blueprint $table): void {
            foreach (['firma_secretario_path', 'firma_mayordomo_path', 'sello_hermandad_path'] as $col) {
                if (Schema::hasColumn('configuracion_hermandad', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
