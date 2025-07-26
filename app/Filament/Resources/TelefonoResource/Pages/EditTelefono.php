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
}
