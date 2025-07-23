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
        // Crear tabla sucursales
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); // Nombre de la sucursal
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            
            // Relación con el usuario jefe (puede ser null al crear)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar tabla sucursales
        Schema::dropIfExists('sucursales');
    }
};
