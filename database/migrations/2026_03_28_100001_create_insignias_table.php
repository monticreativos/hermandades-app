<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insignias', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->enum('tramo', ['Cristo', 'Virgen', 'General']);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->unsignedSmallInteger('max_portadores')->default(1);
            $table->unsignedSmallInteger('max_acompanantes')->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insignias');
    }
};
