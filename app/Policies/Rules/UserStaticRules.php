<?php

declare(strict_types=1);

namespace App\Policies\Rules;

use App\Enums\Users\RoleEnum;
use App\Models\User;

/**
 * Userlevel Policy Static Rules.
 */
abstract class UserStaticRules
{
    /**
     * Allow everyone.
     */
    public static function everyOne(): bool
    {
        return true;
    }

    /**
     * Allow no one.
     */
    public static function noOne(): bool
    {
        return false;
    }

    /**
     * At least a minimum role.
     */
    public static function atLeastRole(User $user, RoleEnum $minRole): bool
    {
        return $user->role->value() <= $minRole->value();
    }
}
