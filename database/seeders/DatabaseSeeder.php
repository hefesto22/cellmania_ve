<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear el usuario
        $user = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('12345678'),
            ]
        );

        // Crear el rol admin
        $role = Role::firstOrCreate(['name' => 'admin']);

        // Generar permisos con Shield
        Artisan::call('shield:generate');
        Artisan::call('permission:cache-reset');

        // ⚠️ Vuelve a cargar los permisos después de generarlos
        $permissions = Permission::all();

        // Asignar permisos al rol
        $role->syncPermissions($permissions);

        // Asignar rol al usuario
        $user->assignRole($role);
    }
}
