<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('configuracion_hermandad')) {
            Schema::create('configuracion_hermandad', function (Blueprint $table) {
                $table->id();
                $table->string('nombre_hermandad');
                $table->string('nombre_corto')->nullable();
                $table->string('cif')->nullable();
                $table->string('direccion')->nullable();
                $table->string('localidad')->nullable();
                $table->string('cp', 10)->nullable();
                $table->string('provincia')->nullable();
                $table->string('telefono')->nullable();
                $table->string('email_contacto')->nullable();
                $table->string('iban_cuotas')->nullable();
                $table->string('bic_swift')->nullable();
                $table->string('escudo_path')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('configuracion_hermandad', function (Blueprint $table) {
            if (! Schema::hasColumn('configuracion_hermandad', 'nombre_hermandad')) {
                $table->string('nombre_hermandad')->nullable()->after('id');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'nombre_corto')) {
                $table->string('nombre_corto')->nullable()->after('nombre_hermandad');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'localidad')) {
                $table->string('localidad')->nullable()->after('direccion');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'cp')) {
                $table->string('cp', 10)->nullable()->after('localidad');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'provincia')) {
                $table->string('provincia')->nullable()->after('cp');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'email_contacto')) {
                $table->string('email_contacto')->nullable()->after('telefono');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'iban_cuotas')) {
                $table->string('iban_cuotas')->nullable()->after('email_contacto');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'bic_swift')) {
                $table->string('bic_swift')->nullable()->after('iban_cuotas');
            }
            if (! Schema::hasColumn('configuracion_hermandad', 'escudo_path')) {
                $table->string('escudo_path')->nullable()->after('bic_swift');
            }
        });

        // Migracion de datos legacy -> nuevos campos.
        $rows = DB::table('configuracion_hermandad')->get();
        foreach ($rows as $row) {
            $update = [];

            if (empty($row->nombre_hermandad) && isset($row->nombre)) {
                $update['nombre_hermandad'] = $row->nombre;
            }
            if (empty($row->email_contacto) && isset($row->email)) {
                $update['email_contacto'] = $row->email;
            }
            if (empty($row->iban_cuotas) && isset($row->iban)) {
                $update['iban_cuotas'] = $row->iban;
            }
            if (empty($row->escudo_path) && isset($row->escudo)) {
                $update['escudo_path'] = $row->escudo;
            }

            if (! empty($update)) {
                DB::table('configuracion_hermandad')->where('id', $row->id)->update($update);
            }
        }

        if (Schema::hasColumn('configuracion_hermandad', 'nombre_hermandad')) {
            DB::table('configuracion_hermandad')
                ->whereNull('nombre_hermandad')
                ->orWhere('nombre_hermandad', '')
                ->update(['nombre_hermandad' => 'Mi Hermandad']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('configuracion_hermandad')) {
            return;
        }

        Schema::table('configuracion_hermandad', function (Blueprint $table) {
            foreach (['nombre_hermandad', 'nombre_corto', 'localidad', 'cp', 'provincia', 'email_contacto', 'iban_cuotas', 'bic_swift', 'escudo_path'] as $column) {
                if (Schema::hasColumn('configuracion_hermandad', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
