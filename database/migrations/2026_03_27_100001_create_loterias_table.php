<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loterias', function (Blueprint $table) {
            $table->id();
            $table->string('sorteo'); // Navidad, Niño, etc.
            $table->string('numero');
            $table->string('serie_fraccion')->nullable();
            $table->unsignedInteger('total_participaciones')->default(0);
            $table->decimal('precio_participacion', 10, 2);
            $table->decimal('donativo', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loterias');
    }
};
