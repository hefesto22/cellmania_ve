<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Telefono;
use App\Models\Accesorio;
use App\Models\Marca;
use App\Models\Categoria;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 3;

        // Crear categoría y marca por defecto asociadas al usuario 3
        $categoria = Categoria::firstOrCreate(
            ['nombre' => 'General'],
            ['created_by' => $userId]
        );

        $marca = Marca::firstOrCreate(
            ['nombre' => 'Genérica'],
            ['created_by' => $userId]
        );

        // Crear 5 accesorios
        foreach (range(1, 5) as $i) {
            Accesorio::create([
                'nombre' => "Accesorio $i",
                'codigo_barras' => "ACCESORIO00$i",
                'precio_compra' => 100 + $i,
                'precio_venta' => 150 + $i,
                'isv' => 15.00,
                'stock' => 5,
                'estado' => 'Disponible',
                'marca_id' => $marca->id,
                'categoria_id' => $categoria->id,
                'created_by' => $userId,
            ]);
        }

        // Crear 5 teléfonos
        foreach (range(1, 5) as $i) {
            Telefono::create([
                'marca_id' => $marca->id,
                'categoria_id' => $categoria->id,
                'modelo' => "Modelo $i",
                'almacenamiento' => '128GB',
                'ram' => '6GB',
                'color' => 'Negro',
                'precio_compra' => 200 + $i,
                'precio_venta' => 300 + $i,
                'isv' => 15.00,
                'stock' => 5,
                'codigo_barras' => "TELEFONO00$i",
                'imei' => "12345678901234$i",
                'accesorios' => 'Cargador, Cable',
                'estado' => 'Disponible',
                'usuario_id' => $userId,
            ]);
        }
    }
}
