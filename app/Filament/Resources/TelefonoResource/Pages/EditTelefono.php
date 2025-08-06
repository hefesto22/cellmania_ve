<?php

namespace App\Filament\Resources\TelefonoResource\Pages;

use App\Filament\Resources\TelefonoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTelefono extends EditRecord
{
    protected static string $resource = TelefonoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extraer valores numéricos, aunque el usuario escriba "12 GB" o "12gb"
        $ramNum = (int) filter_var($data['ram'], FILTER_SANITIZE_NUMBER_INT);
        $almacenamientoNum = (int) filter_var($data['almacenamiento'], FILTER_SANITIZE_NUMBER_INT);

        // Formato correcto
        $ram = "{$ramNum}GB";
        $almacenamiento = "{$almacenamientoNum}GB";

        // Limpiar el modelo anterior eliminando cualquier parte tipo: "128GB RAM 8GB"
        $modeloBase = preg_replace('/\s*\d+GB\s*RAM\s*\d+GB$/i', '', $data['modelo']);

        // Reconstruir modelo
        $data['modelo'] = trim($modeloBase) . " {$almacenamiento} RAM {$ram}";

        // Actualizar campos formateados
        $data['ram'] = $ram;
        $data['almacenamiento'] = $almacenamiento;
        $data['stock'] = 1;
        return $data;
    }
}
