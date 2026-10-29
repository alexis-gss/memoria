<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Users\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('isConceptor', fn (User $user) => $user->role === RoleEnum::conceptor);
    }
}
