<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secretaria_registros_documentales', function (Blueprint $table): void {
            if (! Schema::hasColumn('secretaria_registros_documentales', 'remitente_hermano_id')) {
                $table->unsignedBigInteger('remitente_hermano_id')->nullable()->after('contacto_externo_id');
                $table->foreign('remitente_hermano_id', 'srd_rem_her_fk')->references('id')->on('hermanos')->nullOnDelete();
            }
            if (! Schema::hasColumn('secretaria_registros_documentales', 'remitente_proveedor_id')) {
                $table->unsignedBigInteger('remitente_proveedor_id')->nullable()->after('remitente_hermano_id');
                $table->foreign('remitente_proveedor_id', 'srd_rem_prov_fk')->references('id')->on('proveedores')->nullOnDelete();
            }
            if (! Schema::hasColumn('secretaria_registros_documentales', 'remitente_contacto_externo_id')) {
                $table->unsignedBigInteger('remitente_contacto_externo_id')->nullable()->after('remitente_proveedor_id');
                $table->foreign('remitente_contacto_externo_id', 'srd_rem_cont_fk')->references('id')->on('contactos_externos')->nullOnDelete();
            }
            if (! Schema::hasColumn('secretaria_registros_documentales', 'destinatario_hermano_id')) {
                $table->unsignedBigInteger('destinatario_hermano_id')->nullable()->after('remitente_contacto_externo_id');
                $table->foreign('destinatario_hermano_id', 'srd_des_her_fk')->references('id')->on('hermanos')->nullOnDelete();
            }
            if (! Schema::hasColumn('secretaria_registros_documentales', 'destinatario_proveedor_id')) {
                $table->unsignedBigInteger('destinatario_proveedor_id')->nullable()->after('destinatario_hermano_id');
                $table->foreign('destinatario_proveedor_id', 'srd_des_prov_fk')->references('id')->on('proveedores')->nullOnDelete();
            }
            if (! Schema::hasColumn('secretaria_registros_documentales', 'destinatario_contacto_externo_id')) {
                $table->unsignedBigInteger('destinatario_contacto_externo_id')->nullable()->after('destinatario_proveedor_id');
                $table->foreign('destinatario_contacto_externo_id', 'srd_des_cont_fk')->references('id')->on('contactos_externos')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('secretaria_registros_documentales', function (Blueprint $table): void {
            if (Schema::hasColumn('secretaria_registros_documentales', 'destinatario_contacto_externo_id')) {
                $table->dropForeign('srd_des_cont_fk');
                $table->dropColumn('destinatario_contacto_externo_id');
            }
            if (Schema::hasColumn('secretaria_registros_documentales', 'destinatario_proveedor_id')) {
                $table->dropForeign('srd_des_prov_fk');
                $table->dropColumn('destinatario_proveedor_id');
            }
            if (Schema::hasColumn('secretaria_registros_documentales', 'destinatario_hermano_id')) {
                $table->dropForeign('srd_des_her_fk');
                $table->dropColumn('destinatario_hermano_id');
            }
            if (Schema::hasColumn('secretaria_registros_documentales', 'remitente_contacto_externo_id')) {
                $table->dropForeign('srd_rem_cont_fk');
                $table->dropColumn('remitente_contacto_externo_id');
            }
            if (Schema::hasColumn('secretaria_registros_documentales', 'remitente_proveedor_id')) {
                $table->dropForeign('srd_rem_prov_fk');
                $table->dropColumn('remitente_proveedor_id');
            }
            if (Schema::hasColumn('secretaria_registros_documentales', 'remitente_hermano_id')) {
                $table->dropForeign('srd_rem_her_fk');
                $table->dropColumn('remitente_hermano_id');
            }
        });
    }
};
