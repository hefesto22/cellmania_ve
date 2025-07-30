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
        Schema::create('datos_empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('rtn', 50)->nullable();
            $table->string('cai', 50)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('lema')->nullable();

            $table->string('rango_desde', 25)->nullable();     // Ej: 053-001-01-01480001
            $table->string('rango_hasta', 25)->nullable();     // Ej: 053-001-01-01830000
            $table->string('numero_actual', 25)->nullable();   // Último número emitido

            $table->date('fecha_limite_emision')->nullable();

            $table->string('logo')->nullable(); // ✅ Campo nuevo para subir imagen del logo

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();
        });

        // Tabla de facturas
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_factura')->unique();
            $table->string('cai')->nullable();
            $table->timestamp('fecha_emision')->useCurrent();

            $table->string('cliente_rtn', 50)->nullable();
            $table->string('cliente_nombre')->nullable();
            $table->text('cliente_direccion')->nullable();

            $table->decimal('subtotal_sin_isv', 15, 2);
            $table->decimal('total_isv', 15, 2);
            $table->decimal('bruto', 15, 2);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('total_final', 15, 2);

            $table->foreignId('datos_empresa_id')
                ->nullable()
                ->constrained('datos_empresa')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();
        });

        // Tabla de productos en la factura
        Schema::create('productosfactura', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_id')
                ->constrained('facturas')
                ->cascadeOnDelete();

            $table->string('tipo'); // Ej: 'telefono', 'producto', etc.
            $table->unsignedBigInteger('referencia_id');

            $table->string('nombre'); // ✅ Nuevo campo para almacenar el nombre del producto

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('porcentaje_isv', 5, 2);
            $table->decimal('total_isv', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('costo', 15, 2)->nullable();

            $table->timestamps();

            $table->index(['tipo', 'referencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productosfactura');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('datos_empresa'); // 👈 Agrega esta línea
    }
};
