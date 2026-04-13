<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hermano_portal_cuentas', function (Blueprint $table): void {
            $table->string('activacion_codigo_hash', 64)->nullable()->after('activacion_expira');
            $table->timestamp('activacion_codigo_expira')->nullable()->after('activacion_codigo_hash');
        });
    }

    public function down(): void
    {
        Schema::table('hermano_portal_cuentas', function (Blueprint $table): void {
            $table->dropColumn(['activacion_codigo_hash', 'activacion_codigo_expira']);
        });
    }
};
