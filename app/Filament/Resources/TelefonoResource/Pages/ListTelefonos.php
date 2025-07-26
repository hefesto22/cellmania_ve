<?php

namespace App\Filament\Resources\TelefonoResource\Pages;

use App\Filament\Resources\TelefonoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTelefonos extends ListRecords
{
    protected static string $resource = TelefonoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
