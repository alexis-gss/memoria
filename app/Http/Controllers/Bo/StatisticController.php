<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Statistics\ActivityDateRequest;
use App\Models\ActivityLog;
use App\Models\Folder;
use App\Models\Game;
use App\Models\Rating;
use App\Models\Tag;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatisticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ActivityDateRequest $request): View
    {
        $latestModels    = [];
        $activityModels  = [];
        $activitiesClass = [
            Game::class,
            Folder::class,
            Tag::class,
            User::class,
        ];

        $dateLastDays = collect(CarbonPeriod::create(
            $request->validated()['date_start'] ?? Date::now()->subDays(29),
            $request->validated()['date_end']   ?? Date::now()
        ));

        collect($activitiesClass)->map(function ($class) use (&$activityModels, &$latestModels, $dateLastDays): void {
            collect($dateLastDays->toArray())->map(function ($date) use (&$activityModels, $class): void {
                $activityModels[$class][] = ActivityLog::query()
                    ->where('model_class', $class)
                    ->whereDate('created_at', $date)
                    ->count();
            });
            $latestModels[$class] = $class::query()->orderBy('updated_at', 'DESC')->first() ?? [];
        });

        return view('back.pages.statistics', [
            'navLinks'             => $this->getNavTabsData($latestModels),
            'activityModels'       => collect($activityModels),
            'dateLastDays'         => $dateLastDays,
            'dateLastDaysFormated' => $dateLastDays->map(fn ($date) => $date->format('d/m'))->toArray(),
            'ratingModels'         => $this->getTopFiveModels(new Rating(), 'picture', ['picture', 'picture.game']),
            'visitModels'          => $this->getTopFiveModels(new Visit(), 'game', ['game']),
        ]);
    }

    /**
     * Get the top 5 of specific models.
     */
    public function getTopFiveModels(
        Model $model,
        string $fieldName,
        array $relations = []
    ): Collection {
        return $model::query()
            ->with($relations)
            ->select("{$fieldName}_id", DB::raw('count(*) as count'))
            ->groupBy("{$fieldName}_id")
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Return an array of data to integrate latest model updated.
     */
    public function getNavTabsData(array $latestModels): array
    {
        $navLinks = [];
        foreach ($latestModels as $model) {
            if ($model instanceof Model) {
                $name        = Str::of(class_basename($model))->lower()->value();
                $translation = ($model::class === Game::class)
                    ? trans_choice('models.game', 1)
                    : trans('models.' . $name);
                $field       = ($model::class === User::class)
                ? Str::of(trans('validation.attributes.first_name'))->ucFirst() . ' / ' .
                    Str::of(trans('validation.attributes.last_name'))->ucFirst()
                    : Str::of(trans('validation.attributes.name'))->ucFirst();
                $value = match ($model::class) {
                    User::class => $model->first_name . ' ' . $model->last_name,
                    // @phpstan-ignore-next-line
                    default => $model->name,
                }; // end switch
                $navLinks[] = [
                    'name'        => $name,
                    'translation' => $translation,
                    'model'       => $model,
                    'field'       => $field,
                    'value'       => $value,
                ];
            } // end if
        } // end foreach

        return $navLinks;
    }
}
