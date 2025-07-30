<?php

namespace App\Filament\Resources\DatosEmpresaResource\Pages;

use App\Filament\Resources\DatosEmpresaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDatosEmpresas extends ListRecords
{
    protected static string $resource = DatosEmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
