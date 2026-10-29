<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Theme\BootstrapThemeEnum;
use Illuminate\Support\Facades\Cache;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->authorization();
        if (Cache::get('theme', BootstrapThemeEnum::light->value()) === BootstrapThemeEnum::dark->value()) {
            Telescope::night();
        }
    }
}
