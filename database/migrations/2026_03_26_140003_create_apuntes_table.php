<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apuntes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_id')->constrained('asientos')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('cuenta_contable_id')->constrained('cuentas_contables')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('debe', 12, 2)->default(0);
            $table->decimal('haber', 12, 2)->default(0);
            $table->text('concepto_detalle')->nullable();
            $table->timestamps();

            $table->index(['cuenta_contable_id', 'asiento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apuntes');
    }
};
