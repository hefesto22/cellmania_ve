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
        $ram = preg_replace('/\D/', '', $data['ram']) . 'GB';
        $almacenamiento = preg_replace('/\D/', '', $data['almacenamiento']) . 'GB';

        $data['modelo'] = "{$data['modelo']} {$ram} RAM {$almacenamiento}";
        $data['ram'] = $ram;
        $data['almacenamiento'] = $almacenamiento;

        return $data;
    }
}
