<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loteria_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loteria_id')->constrained('loterias')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('participaciones')->default(1);
            $table->string('referencia_taco')->nullable();
            $table->decimal('importe_a_cobrar', 12, 2);
            $table->boolean('cobrado')->default(false);
            $table->date('fecha_cobro')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['loteria_id', 'cobrado']);
            $table->index(['hermano_id', 'cobrado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loteria_asignaciones');
    }
};
