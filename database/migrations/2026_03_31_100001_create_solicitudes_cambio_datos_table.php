<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_cambio_datos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->foreignId('hermano_portal_cuenta_id')->nullable()->constrained('hermano_portal_cuentas')->nullOnDelete();
            $table->json('datos_solicitados');
            $table->string('estado', 24)->default('Pendiente');
            $table->text('motivo_rechazo')->nullable();
            $table->foreignId('procesado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('procesado_en')->nullable();
            $table->timestamps();

            $table->index(['hermano_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_cambio_datos');
    }
};
