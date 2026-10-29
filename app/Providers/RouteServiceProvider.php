<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Folder;
use App\Models\Game;
use App\Models\StaticPage;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->cspReportRoute();

        $this->routes(function (): void {
            Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web-bo.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web-fo.php'));
        });

        Route::model('user', User::class);
        Route::model('static_page', StaticPage::class);
        Route::model('tag', Tag::class);
        Route::model('folder', Folder::class);
        Route::model('game', Game::class);
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(150)->by($request->user()?->getKey() ?: $request->ip()));
        RateLimiter::for('web', fn (Request $request) => Limit::perMinute(150)->by($request->user()?->getKey() ?: $request->ip()));
        RateLimiter::for('credentials', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->getKey() ?: $request->ip()));
    }

    /**
     * Register CSP report URI.
     *
     * Put this function inside RouteServiceProvider,
     * and call it in RouteServiceProvider::boot
     */
    private function cspReportRoute(): void
    {
        // * CSP REPORT URI, Please add /csp-report to VerifyCsrfToken $except middleware
        Route::post('/csp-report', function (): void {
            $isCspRequest = request()->header('Content-Type') === 'application/csp-report';
            abort_if(! $isCspRequest or ! request()->json('csp-report') or ! is_array(request()->json('csp-report')), 401);
            // * Flatten if needed
            $cspReport = collect(request()->json('csp-report'))
                ->flatMap(fn ($v, $k) => [$k => is_array($v) ? collect($v)->flatten()->implode(', ') : $v])->all();
            // * Filter array keys
            $cspReport = collect(array_intersect_key($cspReport, [
                'blocked-uri' => '', 'document-uri' => '', 'original-policy' => '',
                'referrer'    => '', 'violated-directive' => '',
            ]));
            Log::warning(sprintf(
                "CSP REPORT for %s : \n %s",
                $cspReport->get('document-uri'),
                $cspReport->implode("\n")
            ));
        })->middleware('throttle:5,2')
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->name('cspReportUri');
    }
}
