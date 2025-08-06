<?php

namespace App\Filament\Resources\TelefonoResource\Pages;

use App\Filament\Resources\TelefonoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTelefono extends CreateRecord
{
    protected static string $resource = TelefonoResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = Auth::id();
        $data['stock'] = 1;

        $data['estado'] = 'Disponible';
        // Limpiar valores previos si ya contienen "GB"
        $ram = preg_replace('/\D/', '', $data['ram']) . 'GB';
        $almacenamiento = preg_replace('/\D/', '', $data['almacenamiento']) . 'GB';

        // Concatenar modelo completo
        $data['modelo'] = "{$data['modelo']} {$ram} RAM {$almacenamiento}";

        // Guardar valores ya formateados
        $data['ram'] = $ram;
        $data['almacenamiento'] = $almacenamiento;

        return $data;
    }
}
