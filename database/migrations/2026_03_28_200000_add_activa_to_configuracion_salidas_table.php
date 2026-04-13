<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_salidas', function (Blueprint $table): void {
            $table->boolean('activa')->default(true)->after('notas');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_salidas', function (Blueprint $table): void {
            $table->dropColumn('activa');
        });
    }
};
