<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('secretaria_plantillas_documentales') && ! Schema::hasColumn('secretaria_plantillas_documentales', 'marca_agua_path')) {
            Schema::table('secretaria_plantillas_documentales', function (Blueprint $table): void {
                $table->string('marca_agua_path')->nullable()->after('marca_agua');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('secretaria_plantillas_documentales') && Schema::hasColumn('secretaria_plantillas_documentales', 'marca_agua_path')) {
            Schema::table('secretaria_plantillas_documentales', function (Blueprint $table): void {
                $table->dropColumn('marca_agua_path');
            });
        }
    }
};
