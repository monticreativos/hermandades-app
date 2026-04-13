<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_tienda', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('categoria', 32);
            $table->decimal('precio_venta', 12, 2);
            $table->decimal('precio_coste', 12, 2)->default(0);
            $table->decimal('iva_porcentaje', 5, 2)->default(21);
            $table->unsignedInteger('stock_actual')->default(0);
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->string('sku', 64)->nullable();
            $table->string('imagen_path')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique('sku');
            $table->index(['activo', 'categoria']);
        });

        Schema::create('ventas_tienda', function (Blueprint $table): void {
            $table->id();
            $table->string('folio', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hermano_id')->nullable()->constrained('hermanos')->nullOnDelete();
            $table->boolean('venta_anonima')->default(false);
            $table->string('metodo_pago', 16);
            $table->decimal('importe_total', 14, 2);
            $table->decimal('total_base', 14, 2);
            $table->decimal('total_iva', 14, 2)->default(0);
            $table->foreignId('asiento_id')->nullable()->constrained('asientos')->nullOnDelete();
            $table->string('pedido_portal_uuid', 36)->nullable()->index();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'metodo_pago']);
        });

        Schema::create('venta_tienda_lineas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venta_tienda_id')->constrained('ventas_tienda')->cascadeOnDelete();
            $table->foreignId('producto_tienda_id')->constrained('productos_tienda')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario_ttc', 12, 2);
            $table->decimal('iva_porcentaje', 5, 2);
            $table->decimal('base_imponible_linea', 14, 2);
            $table->decimal('cuota_iva_linea', 14, 2)->default(0);
            $table->decimal('total_linea', 14, 2);
            $table->decimal('precio_coste_unitario_snapshot', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('cierres_caja_tienda', function (Blueprint $table): void {
            $table->id();
            $table->date('fecha');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('teorico_efectivo', 14, 2);
            $table->decimal('teorico_tarjeta', 14, 2);
            $table->decimal('teorico_bizum', 14, 2);
            $table->decimal('conteo_efectivo_fisico', 14, 2);
            $table->decimal('diferencia_efectivo', 14, 2);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique('fecha');
        });

        Schema::create('pedidos_tienda_portal', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('hermano_id')->constrained('hermanos')->cascadeOnDelete();
            $table->string('estado', 24)->default('borrador');
            $table->decimal('total_ttc', 14, 2)->default(0);
            $table->foreignId('asiento_id')->nullable()->constrained('asientos')->nullOnDelete();
            $table->foreignId('venta_tienda_id')->nullable()->constrained('ventas_tienda')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pedido_tienda_portal_lineas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_tienda_portal_id')->constrained('pedidos_tienda_portal')->cascadeOnDelete();
            $table->foreignId('producto_tienda_id')->constrained('productos_tienda')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario_ttc', 12, 2);
            $table->decimal('iva_porcentaje', 5, 2);
            $table->decimal('subtotal_ttc', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_tienda_portal_lineas');
        Schema::dropIfExists('pedidos_tienda_portal');
        Schema::dropIfExists('cierres_caja_tienda');
        Schema::dropIfExists('venta_tienda_lineas');
        Schema::dropIfExists('ventas_tienda');
        Schema::dropIfExists('productos_tienda');
    }
};
