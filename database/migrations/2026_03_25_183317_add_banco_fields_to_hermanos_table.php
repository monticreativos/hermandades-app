<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hermanos', function (Blueprint $table) {
            $table->foreignId('banco_id')
                ->nullable()
                ->after('email')
                ->constrained('bancos')
                ->nullOnDelete();
            $table->string('sucursal', 120)->nullable()->after('banco_id');
            $table->string('titular_cuenta_menor', 180)->nullable()->after('titular_cuenta');
            $table->date('fecha_baja')->nullable()->after('fecha_alta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hermanos', function (Blueprint $table) {
            $table->dropForeign(['banco_id']);
            $table->dropColumn(['banco_id', 'sucursal', 'titular_cuenta_menor', 'fecha_baja']);
        });
    }
};
