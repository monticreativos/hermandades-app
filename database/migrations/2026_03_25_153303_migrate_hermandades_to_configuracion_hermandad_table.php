<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('hermandades') && ! Schema::hasTable('configuracion_hermandad')) {
            Schema::rename('hermandades', 'configuracion_hermandad');
        }

        if (! Schema::hasTable('configuracion_hermandad')) {
            Schema::create('configuracion_hermandad', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('escudo')->nullable();
                $table->string('cif')->nullable();
                $table->string('cuenta_bancaria')->nullable();
                $table->string('iban')->nullable();
                $table->string('telefono')->nullable();
                $table->string('email')->nullable();
                $table->string('direccion')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('configuracion_hermandad', function (Blueprint $table) {
            if (Schema::hasColumn('configuracion_hermandad', 'subdominio')) {
                $table->dropColumn('subdominio');
            }

            if (! Schema::hasColumn('configuracion_hermandad', 'escudo')) {
                $table->string('escudo')->nullable()->after('nombre');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'cif')) {
                $table->string('cif')->nullable()->after('escudo');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'cuenta_bancaria')) {
                $table->string('cuenta_bancaria')->nullable()->after('cif');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'iban')) {
                $table->string('iban')->nullable()->after('cuenta_bancaria');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'telefono')) {
                $table->string('telefono')->nullable()->after('iban');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'email')) {
                $table->string('email')->nullable()->after('telefono');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'direccion')) {
                $table->string('direccion')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('configuracion_hermandad')) {
            return;
        }

        Schema::table('configuracion_hermandad', function (Blueprint $table) {
            foreach (['escudo', 'cif', 'cuenta_bancaria', 'iban', 'telefono', 'email', 'direccion'] as $column) {
                if (Schema::hasColumn('configuracion_hermandad', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (! Schema::hasColumn('configuracion_hermandad', 'subdominio')) {
                $table->string('subdominio')->nullable();
            }
        });

        if (! Schema::hasTable('hermandades')) {
            Schema::rename('configuracion_hermandad', 'hermandades');
        }
    }
};
