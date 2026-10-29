<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Users\RoleEnum;
use App\Models\User;
use App\Policies\Rules\UserStaticRules;

class UserPolicy
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
    public function view(User $authUser, User $user): bool
    {
        return $authUser->getRouteKey() === $user->getRouteKey() || UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
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
    public function update(User $authUser, User $user): bool
    {
        return $authUser->getRouteKey() === $user->getRouteKey() || UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $authUser, User $user): bool
    {
        return $authUser->getRouteKey() !== $user->getRouteKey() && UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can duplicate the model.
     */
    public function duplicate(User $authUser, User $user): bool
    {
        return $authUser->getRouteKey() !== $user->getRouteKey() && UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can change the order model.
     */
    public function changeOrder(User $authUser): bool
    {
        return UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can publish/unpublish the model.
     */
    public function changePublished(User $authUser, User $user): bool
    {
        return $authUser->getRouteKey() !== $user->getRouteKey() && UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can send a email to the user for reset his password.
     */
    public function resetPassword(User $authUser, User $user): bool
    {
        return $authUser->getRouteKey() !== $user->getRouteKey() && UserStaticRules::atLeastRole($authUser, RoleEnum::conceptor);
    }
}
