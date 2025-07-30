<?php

namespace App\Filament\Resources\DatosEmpresaResource\Pages;

use App\Filament\Resources\DatosEmpresaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDatosEmpresa extends CreateRecord
{
    protected static string $resource = DatosEmpresaResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id(); // ✅ Asigna el usuario autenticado
        return $data;
    }
}
