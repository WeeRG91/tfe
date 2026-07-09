<?php

namespace App\Policies;

use App\Enums\Permissions\DrinkPermissionEnum;
use App\Models\Drink;
use App\Models\User;

class DrinkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(DrinkPermissionEnum::DRINK_VIEW->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Drink $drink): bool
    {
        return $user->can(DrinkPermissionEnum::DRINK_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(DrinkPermissionEnum::DRINK_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Drink $drink): bool
    {
        return $user->can(DrinkPermissionEnum::DRINK_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Drink $drink): bool
    {
        return $user->can(DrinkPermissionEnum::DRINK_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Drink $drink): bool
    {
        return $user->can(DrinkPermissionEnum::DRINK_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Drink $drink): bool
    {
        return $user->can(DrinkPermissionEnum::DRINK_DELETE->value);
    }
}
