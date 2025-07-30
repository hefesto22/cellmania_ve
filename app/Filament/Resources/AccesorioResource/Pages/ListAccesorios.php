<?php

namespace App\Filament\Resources\AccesorioResource\Pages;

use App\Filament\Resources\AccesorioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccesorios extends ListRecords
{
    protected static string $resource = AccesorioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
