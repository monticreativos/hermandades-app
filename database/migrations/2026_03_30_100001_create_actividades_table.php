<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('accion', 80);
            $table->string('descripcion', 500);
            $table->timestamps();

            $table->index('accion');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
