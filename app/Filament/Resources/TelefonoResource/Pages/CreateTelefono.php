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
        $data['usuario_id'] = Auth::id(); // ✅ Asignar el usuario actual

        return $data;
    }
}
