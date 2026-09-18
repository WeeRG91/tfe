<?php

namespace App\Policies;

use App\Enums\Permissions\MeatPermissionEnum;
use App\Models\Meat;
use App\Models\User;

class MeatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(MeatPermissionEnum::MEAT_VIEW->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Meat $meat): bool
    {
        return $user->can(MeatPermissionEnum::MEAT_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(MeatPermissionEnum::MEAT_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Meat $meat): bool
    {
        return $user->can(MeatPermissionEnum::MEAT_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Meat $meat): bool
    {
        return $user->can(MeatPermissionEnum::MEAT_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Meat $meat): bool
    {
        return $user->can(MeatPermissionEnum::MEAT_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Meat $meat): bool
    {
        return $user->can(MeatPermissionEnum::MEAT_DELETE->value);
    }
}
