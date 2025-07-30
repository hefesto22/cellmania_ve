<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla categorías
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Tabla marcas
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Tabla accesorios
        // Tabla accesorios
        Schema::create('accesorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_barras')->unique();
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->decimal('isv', 5, 2)->default(15.00); // ISV agregado
            $table->integer('stock')->default(0);
            $table->enum('estado', ['Disponible', 'Vendido', 'Inactivo'])->default('Disponible');

            $table->foreignId('marca_id')->constrained('marcas')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });

        // Tabla teléfonos
        Schema::create('telefonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('cascade');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->string('modelo');
            $table->string('almacenamiento');
            $table->string('ram');
            $table->string('color')->nullable();
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->decimal('isv', 5, 2)->default(15.00); // ISV agregado
            $table->integer('stock')->default(0);
            $table->string('codigo_barras')->unique();
            $table->string('imei')->nullable();
            $table->text('accesorios')->nullable();
            $table->enum('estado', ['Disponible', 'Vendido', 'Reservado', 'Inactivo'])->default('Disponible');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('accesorio_telefono', function (Blueprint $table) {
            $table->id();

            // Relación con el teléfono
            $table->foreignId('telefono_id')->constrained()->onDelete('cascade');

            // Datos copiados del accesorio original
            $table->string('nombre');
            $table->string('codigo_barras');
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2)->default(0);
            $table->decimal('isv', 5, 2)->default(0);
            $table->integer('stock')->default(1); // Generalmente será 1 accesorio asignado

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accesorio_telefono'); // primero las dependientes
        Schema::dropIfExists('telefonos');
        Schema::dropIfExists('accesorios');
        Schema::dropIfExists('marcas');
        Schema::dropIfExists('categorias');
    }
};
