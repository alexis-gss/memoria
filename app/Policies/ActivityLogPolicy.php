<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Users\RoleEnum;
use App\Models\User;
use App\Policies\Rules\UserStaticRules;

/**
 * Policy for managing access control on ActivityLog model.
 */
class ActivityLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $authModel): bool
    {
        return UserStaticRules::atLeastRole($authModel, RoleEnum::conceptor);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $authModel): bool
    {
        return UserStaticRules::atLeastRole($authModel, RoleEnum::conceptor);
    }
}
