<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_cambio_datos', function (Blueprint $table): void {
            $table->string('ip_solicitud', 45)->nullable()->after('hermano_portal_cuenta_id');
            $table->string('user_agent', 512)->nullable()->after('ip_solicitud');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_cambio_datos', function (Blueprint $table): void {
            $table->dropColumn(['ip_solicitud', 'user_agent']);
        });
    }
};
