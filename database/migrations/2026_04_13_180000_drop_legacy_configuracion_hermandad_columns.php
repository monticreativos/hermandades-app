<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tras renombrar hermandades → configuracion_hermandad y añadir nombre_hermandad,
     * las columnas antiguas (nombre, email, iban…) siguen existiendo y obligan a insertar
     * valores en el seeder. Se eliminan si siguen presentes.
     */
    public function up(): void
    {
        if (! Schema::hasTable('configuracion_hermandad')) {
            return;
        }

        $legacy = ['nombre', 'email', 'iban', 'escudo', 'cuenta_bancaria'];
        $toDrop = array_values(array_filter($legacy, fn (string $col) => Schema::hasColumn('configuracion_hermandad', $col)));

        if ($toDrop === []) {
            return;
        }

        Schema::table('configuracion_hermandad', function (Blueprint $table) use ($toDrop): void {
            $table->dropColumn($toDrop);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuracion_hermandad')) {
            return;
        }

        Schema::table('configuracion_hermandad', function (Blueprint $table): void {
            if (! Schema::hasColumn('configuracion_hermandad', 'nombre')) {
                $table->string('nombre')->nullable()->after('id');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'escudo')) {
                $table->string('escudo')->nullable();
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'cuenta_bancaria')) {
                $table->string('cuenta_bancaria')->nullable();
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'iban')) {
                $table->string('iban')->nullable();
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'email')) {
                $table->string('email')->nullable();
            }
        });
    }
};
