<?php

namespace App\Filament\Resources\DatosEmpresaResource\Pages;

use App\Filament\Resources\DatosEmpresaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDatosEmpresa extends EditRecord
{
    protected static string $resource = DatosEmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['user_id'] = Auth::id(); // Opcional: actualizar usuario responsable
        return $data;
    }
}
