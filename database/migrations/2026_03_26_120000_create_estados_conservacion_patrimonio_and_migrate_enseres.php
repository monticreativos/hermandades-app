<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_conservacion_patrimonio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        $nombres = ['Excelente', 'Bueno', 'Regular', 'Necesita Restauración', 'En Restauración'];
        $now = now();
        foreach ($nombres as $nombre) {
            DB::table('estados_conservacion_patrimonio')->insert([
                'nombre' => $nombre,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $buenoId = (int) DB::table('estados_conservacion_patrimonio')->where('nombre', 'Bueno')->value('id');
        $map = DB::table('estados_conservacion_patrimonio')->pluck('id', 'nombre')->all();

        Schema::table('enseres', function (Blueprint $table) use ($buenoId) {
            $table->foreignId('estado_conservacion_id')
                ->default($buenoId)
                ->after('materiales')
                ->constrained('estados_conservacion_patrimonio')
                ->restrictOnDelete();
        });

        foreach (DB::table('enseres')->cursor() as $row) {
            $nombre = $row->estado_conservacion ?? null;
            $id = ($nombre && isset($map[$nombre])) ? (int) $map[$nombre] : $buenoId;
            DB::table('enseres')->where('id', $row->id)->update(['estado_conservacion_id' => $id]);
        }

        Schema::table('enseres', function (Blueprint $table) {
            $table->dropColumn('estado_conservacion');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('enseres') || ! Schema::hasTable('estados_conservacion_patrimonio')) {
            return;
        }

        Schema::table('enseres', function (Blueprint $table) {
            $table->string('estado_conservacion')->nullable()->after('materiales');
        });

        $map = DB::table('estados_conservacion_patrimonio')->pluck('nombre', 'id')->all();
        foreach (DB::table('enseres')->cursor() as $row) {
            $nombre = $map[$row->estado_conservacion_id] ?? 'Bueno';
            DB::table('enseres')->where('id', $row->id)->update(['estado_conservacion' => $nombre]);
        }

        Schema::table('enseres', function (Blueprint $table) {
            $table->dropForeign(['estado_conservacion_id']);
            $table->dropColumn('estado_conservacion_id');
        });

        Schema::dropIfExists('estados_conservacion_patrimonio');
    }
};
