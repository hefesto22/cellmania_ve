<?php

namespace App\Filament\Resources\AccesorioResource\Pages;

use App\Filament\Resources\AccesorioResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAccesorio extends CreateRecord
{
    protected static string $resource = AccesorioResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['estado'] = 'Disponible'; // 👈 Aquí
        return $data;
    }
}
