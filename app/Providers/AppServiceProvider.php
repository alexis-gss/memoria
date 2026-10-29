<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->environment('production'));

        // * HTTPS FIX
        URL::forceScheme('https');

        // * LOCAL FORCE eg: for Carbon
        setlocale(LC_ALL, \sprintf(
            '%s_%s.UTF-8',
            config('app.locale'),
            \strtoupper(config('app.locale'))
        ));
        Date::setLocale(config('app.locale'));
    }
}
