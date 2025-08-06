<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DatosEmpresa;
use Illuminate\Auth\Access\HandlesAuthorization;

class DatosEmpresaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_datos::empresa');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DatosEmpresa $datosEmpresa): bool
    {
        return $user->can('view_datos::empresa');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_datos::empresa');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DatosEmpresa $datosEmpresa): bool
    {
        return $user->can('update_datos::empresa');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DatosEmpresa $datosEmpresa): bool
    {
        return $user->can('delete_datos::empresa');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_datos::empresa');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DatosEmpresa $datosEmpresa): bool
    {
        return $user->can('force_delete_datos::empresa');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_datos::empresa');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DatosEmpresa $datosEmpresa): bool
    {
        return $user->can('restore_datos::empresa');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_datos::empresa');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DatosEmpresa $datosEmpresa): bool
    {
        return $user->can('replicate_datos::empresa');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_datos::empresa');
    }
}
