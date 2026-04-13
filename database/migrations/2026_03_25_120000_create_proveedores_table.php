<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('tipo_persona', 32)->default('juridica');
            $table->string('nif_cif', 32)->nullable();
            $table->string('direccion')->nullable();
            $table->string('codigo_postal', 16)->nullable();
            $table->string('municipio')->nullable();
            $table->string('provincia')->nullable();
            $table->string('pais', 2)->default('ES');
            $table->string('telefono', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('regimen_iva', 48)->nullable();
            $table->string('iban', 34)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('razon_social');
            $table->unique('nif_cif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
