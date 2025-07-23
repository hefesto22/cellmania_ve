<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

protected function afterSave(): void
{
    $roleIds = $this->data['roles'] ?? [];
    $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->toArray();

    $this->record->syncRoles($roleNames);
}
}
