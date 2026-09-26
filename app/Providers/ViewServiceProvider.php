<?php

namespace App\Providers;

use App\Models\Game;
use App\Models\Folder;
use App\Models\Rank;
use App\Models\StaticPage;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use LaravelActivityLogs\Models\ActivityLog;
use Throwable;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Get data from composer.json.
        $appInfos = Cache::remember('composer', 360, function () {
            return json_decode(File::get(app_path('../composer.json')));
        });

        // Share composer.json infos.
        View::share('globalName', $appInfos->name);
        View::share('globalVersion', $appInfos->version);
        View::share('globalLicense', $appInfos->license);

        // Default values: always available in every view
        View::share('globalGames', collect());
        View::share('globalFolders', collect());
        View::share('globalTags', collect());
        View::share('globalUsers', collect());
        View::share('globalActivities', collect());
        View::share('globalRanks', collect());
        View::share('globalStaticPages', collect());

        if (!app()->runningInConsole()) {
            try {
                if (
                    Schema::hasTable('games') &&
                    Schema::hasTable('folders') &&
                    Schema::hasTable('tags') &&
                    Schema::hasTable('users') &&
                    Schema::hasTable('activity_logs') &&
                    Schema::hasTable('ranks') &&
                    Schema::hasTable('static_pages')
                ) {
                    View::share('globalGames', Game::query()->with('pictures')->orderBy('name', 'ASC')->get());
                    View::share('globalFolders', Folder::query()->with('games')->orderBy('name', 'ASC')->get());
                    View::share('globalTags', Tag::query()->with('games')->orderBy('name', 'ASC')->get());
                    View::share('globalUsers', User::query()->orderBy('last_name', 'ASC')->get());
                    View::share('globalActivities', ActivityLog::query()->with('user')->get());
                    View::share('globalRanks', Rank::query()->orderBy('rank', 'ASC')->get());
                    View::share('globalStaticPages', StaticPage::query()->orderBy('title', 'ASC')->get());
                }
            } catch (Throwable $e) {
                // Keep the application error pages renderable
                Log::warning('Unable to load global view data.', [
                    'exception' => $e::class,
                    'message' => $e->getMessage(),
                ]);
            } //end try

            // Pagination
            (collect(explode('/', request()->getPathInfo()))->get(1) === 'bo')
                ? Paginator::defaultView('components.back.pagination')
                : Paginator::useBootstrapFive();
        } //end if
    }
}
