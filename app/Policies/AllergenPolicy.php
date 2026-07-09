<?php

namespace App\Policies;

use App\Enums\Permissions\AllergenPermissionEnum;
use App\Models\Allergen;
use App\Models\User;

class AllergenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(AllergenPermissionEnum::ALLERGEN_VIEW->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Allergen $allergen): bool
    {
        return $user->can(AllergenPermissionEnum::ALLERGEN_VIEW->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(AllergenPermissionEnum::ALLERGEN_CREATE->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Allergen $allergen): bool
    {
        return $user->can(AllergenPermissionEnum::ALLERGEN_UPDATE->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Allergen $allergen): bool
    {
        return $user->can(AllergenPermissionEnum::ALLERGEN_DELETE->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Allergen $allergen): bool
    {
        return $user->can(AllergenPermissionEnum::ALLERGEN_RESTORE->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Allergen $allergen): bool
    {
        return $user->can(AllergenPermissionEnum::ALLERGEN_DELETE->value);
    }
}
