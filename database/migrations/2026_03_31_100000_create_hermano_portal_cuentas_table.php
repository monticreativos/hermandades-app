<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hermano_portal_cuentas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('hermano_id')->unique()->constrained('hermanos')->cascadeOnDelete();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('activacion_token_hash', 64)->nullable()->index();
            $table->timestamp('activacion_expira')->nullable();
            $table->string('recuperacion_codigo_hash')->nullable();
            $table->timestamp('recuperacion_expira')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hermano_portal_cuentas');
    }
};
