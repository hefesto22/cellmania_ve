<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

use Illuminate\Support\Facades\Auth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id(); // asigna el creador
        return $data;
    }

    protected function afterCreate(): void
    {
        $roleIds = $this->data['roles'] ?? [];
        $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->toArray();

        $this->record->syncRoles($roleNames);
    }
}
