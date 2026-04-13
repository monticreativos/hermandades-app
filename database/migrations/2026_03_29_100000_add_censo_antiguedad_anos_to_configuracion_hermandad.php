<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_hermandad', function (Blueprint $table): void {
            if (! Schema::hasColumn('configuracion_hermandad', 'censo_antiguedad_anos')) {
                $table->unsignedTinyInteger('censo_antiguedad_anos')->default(1)->after('escudo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_hermandad', function (Blueprint $table): void {
            if (Schema::hasColumn('configuracion_hermandad', 'censo_antiguedad_anos')) {
                $table->dropColumn('censo_antiguedad_anos');
            }
        });
    }
};
