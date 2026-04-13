<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            $table->boolean('rgpd_aceptado')->default(false)->after('observaciones');
            $table->timestamp('rgpd_fecha')->nullable()->after('rgpd_aceptado');
            $table->string('rgpd_ip', 45)->nullable()->after('rgpd_fecha');
        });

        // Cuentas de portal ya verificadas antes de esta versión: conformidad asumida en despliegue (ajustar si aplica política estricta).
        $ids = DB::table('hermano_portal_cuentas')
            ->whereNotNull('password')
            ->whereNotNull('email_verified_at')
            ->pluck('hermano_id');

        if ($ids->isNotEmpty()) {
            DB::table('hermanos')->whereIn('id', $ids->all())->update([
                'rgpd_aceptado' => true,
                'rgpd_fecha' => now(),
                'rgpd_ip' => null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('hermanos', function (Blueprint $table): void {
            $table->dropColumn(['rgpd_aceptado', 'rgpd_fecha', 'rgpd_ip']);
        });
    }
};
