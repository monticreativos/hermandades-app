<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('canal', 32);
            $table->string('evento', 80);
            $table->text('descripcion')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hermano_portal_cuenta_id')->nullable()->constrained('hermano_portal_cuentas')->nullOnDelete();
            $table->foreignId('hermano_id')->nullable()->constrained('hermanos')->nullOnDelete();
            $table->string('email_intento', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('metodo_http', 12)->nullable();
            $table->string('ruta', 255)->nullable();
            $table->string('path', 512)->nullable();
            $table->unsignedSmallInteger('codigo_http')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['canal', 'created_at']);
            $table->index(['evento', 'created_at']);
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_logs');
    }
};
