<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Users\RoleEnum;
use App\Models\User;
use App\Policies\Rules\UserStaticRules;

class TagPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return UserStaticRules::everyOne();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(): bool
    {
        return UserStaticRules::everyOne();
    }

    /**
     * Determine whether the user can create a new model.
     */
    public function create(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }

    /**
     * Determine whether the user can duplicate the model.
     */
    public function duplicate(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }

    /**
     * Determine whether the user can change the order model.
     */
    public function changeOrder(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }

    /**
     * Determine whether the user can publish/unpublish the model.
     */
    public function changePublished(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::admin);
    }
}
