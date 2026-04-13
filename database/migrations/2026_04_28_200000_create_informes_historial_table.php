<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes_historial', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 64);
            $table->string('titulo');
            $table->unsignedSmallInteger('periodo_año')->nullable();
            $table->unsignedTinyInteger('periodo_mes')->nullable();
            $table->string('archivo_path');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_historial');
    }
};
