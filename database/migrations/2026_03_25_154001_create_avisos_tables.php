<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo');
            $table->text('cuerpo');
            $table->string('alcance', 24);
            $table->boolean('solo_alta')->default(true);
            $table->boolean('solo_portal')->default(false);
            $table->foreignId('hermano_id')->nullable()->constrained('hermanos')->nullOnDelete();
            $table->foreignId('creado_por_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('enviado_en')->nullable();
            $table->timestamps();
        });

        Schema::create('aviso_hermano', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('aviso_id')->constrained('avisos')->cascadeOnDelete();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->timestamp('leido_en')->nullable();
            $table->timestamps();

            $table->unique(['aviso_id', 'hermano_id']);
            $table->index(['hermano_id', 'leido_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aviso_hermano');
        Schema::dropIfExists('avisos');
    }
};
