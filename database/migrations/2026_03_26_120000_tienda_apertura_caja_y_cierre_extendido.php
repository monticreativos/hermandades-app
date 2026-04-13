<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aperturas_caja_tienda', function (Blueprint $table): void {
            $table->id();
            $table->date('fecha');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('saldo_inicial_efectivo', 14, 2);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique('fecha');
        });

        Schema::table('cierres_caja_tienda', function (Blueprint $table): void {
            $table->decimal('saldo_inicial_efectivo', 14, 2)->default(0)->after('teorico_bizum');
            $table->decimal('efectivo_esperado_cierre', 14, 2)->default(0)->after('saldo_inicial_efectivo');
        });

        DB::statement('UPDATE cierres_caja_tienda SET efectivo_esperado_cierre = teorico_efectivo + saldo_inicial_efectivo');
    }

    public function down(): void
    {
        Schema::table('cierres_caja_tienda', function (Blueprint $table): void {
            $table->dropColumn(['saldo_inicial_efectivo', 'efectivo_esperado_cierre']);
        });

        Schema::dropIfExists('aperturas_caja_tienda');
    }
};
