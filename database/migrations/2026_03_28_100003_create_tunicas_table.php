<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunicas', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('talla');
            $table->enum('estado', ['Disponible', 'Prestada', 'En reparación', 'Baja'])->default('Disponible');
            $table->foreignId('hermano_id')->nullable()->constrained('hermanos')->nullOnDelete();
            $table->decimal('fianza', 8, 2)->default(0);
            $table->date('fecha_prestamo')->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunicas');
    }
};
