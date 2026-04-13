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
        if (! Schema::hasColumn('users', 'hermandad_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Eliminamos FK/index de forma tolerante a distintos estados.
            try {
                $table->dropForeign(['hermandad_id']);
            } catch (Throwable $th) {
                // No-op
            }

            try {
                $table->dropIndex(['hermandad_id']);
            } catch (Throwable $th) {
                // No-op
            }

            $table->dropColumn('hermandad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'hermandad_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('hermandad_id')->nullable()->index();
        });
    }
};
