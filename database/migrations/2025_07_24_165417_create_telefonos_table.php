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
        Schema::create('telefonos', function (Blueprint $table) {
            $table->id();
            $table->string('marca');
            $table->string('modelo');
            $table->string('almacenamiento'); // Ej: "128 GB"
            $table->string('ram'); // Ej: "6 GB"
            $table->string('color')->nullable();
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('codigo_barras')->unique();
            $table->enum('estado', ['Disponible', 'Vendido', 'Reservado', 'Inactivo'])->default('Disponible');

            // Solo usuario_id
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telefonos');
    }
};
