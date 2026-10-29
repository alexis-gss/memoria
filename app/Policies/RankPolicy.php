<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Users\RoleEnum;
use App\Models\User;
use App\Policies\Rules\UserStaticRules;

class RankPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can create a new model.
     */
    public function create(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }
}
