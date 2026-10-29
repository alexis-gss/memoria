<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Pagination\ItemsPerPaginationEnum;
use App\Enums\Theme\BootstrapThemeEnum;
use App\Http\Requests\Bo\JsonPaginateRequest;
use App\Http\Requests\Bo\LangRequest;
use App\Http\Requests\Bo\NavigationRequest;
use App\Http\Requests\Bo\ThemeRequest;
use App\Lib\Helpers\ToolboxHelper;
use App\Models\Folder;
use App\Models\Game;
use App\Models\Rank;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * Use for pagination,
     * number of models per page.
     *
     * @var int
     */
    protected $modelsPerPage = 12;

    /**
     * Use for pagination,
     * number of games per page.
     *
     * @var int
     */
    protected $gamesPerPage = 20;

    /**
     * Get game models published.
     *
     * @param  bool  $paginate  Paginate the query.
     * @param  int  $nbrItemsPerPage  Number of items per pagination page.
     */
    protected function getGamesPublished(
        bool $paginate = false,
        int $nbrItemsPerPage = 12,
    ): LengthAwarePaginator | Collection {
        $searchText   = request()->text;
        $searchFolder = request()->folder;
        $searchTag    = request()->tag;
        $sort         = request()->sort ?? 'alphabetical';

        /** @var Builder $query */
        $query = Game::query()
            ->with('pictures')
            ->when($searchText, fn (Builder $query) => $query->where('name', 'LIKE', '%' . $searchText . '%'))
            ->when($searchFolder, fn (Builder $query) => $query->whereHas('folder', function (Builder $query) use ($searchFolder): void {
                $query->where('slug', $searchFolder);
            }))
            ->when($searchTag, fn (Builder $query) => $query->whereHas('tags', function (Builder $query) use ($searchTag): void {
                $query->where('slug', $searchTag);
            }))
            ->where('published', true)
            ->whereHas('folder', function (Builder $query): void {
                $query->where('published', true);
            })
            ->withCount('pictures')
            ->when($sort === 'music', function (Builder $query): void {
                $query->orderByRaw('music IS NULL, music IS NOT NULL')
                    ->orderBy('slug', 'asc');
            })
            ->when($sort === 'pictures', function (Builder $query): void {
                $query->orderBy('pictures_count', 'desc')->orderBy('slug', 'asc');
            })
            ->when($sort === 'latest', function (Builder $query): void {
                $query->latest()->orderBy('slug', 'asc');
            })
            ->when($sort === 'alphabetical', function (Builder $query): void {
                $query->orderBy('slug', 'asc');
            });

        return ($paginate) ? $query->paginate($nbrItemsPerPage) : $query->get();
    }

    /**
     * Get folder models published.
     *
     * @param  bool  $paginate  Paginate the query.
     * @param  int  $nbrItemsPerPage  Number of items per pagination page.
     */
    protected function getFoldersPublished(
        bool $paginate = false,
        int $nbrItemsPerPage = 12,
    ): LengthAwarePaginator | Collection {
        $query = Folder::query()->where('published', true);

        if ($paginate) {
            /** @var LengthAwarePaginator $folderModels */
            $folderModels = $query->orderby('mandatory', 'desc')
                ->orderBy('name')
                ->paginate($nbrItemsPerPage)
                ->through(function (Folder $folderModel) {
                    // @phpstan-ignore-next-line
                    $folderModel->nameLocale = $folderModel->getTranslation('name', config('app.locale'));

                    return $folderModel;
                });

            return new LengthAwarePaginator(
                $folderModels->sortBy('nameLocale')->sortByDesc('mandatory')->values(),
                $query->paginate($nbrItemsPerPage)->total(),
                $nbrItemsPerPage
            );
        }

        return $query->get()
            ->map(function ($folderModel) {
                // @phpstan-ignore-next-line
                $folderModel->nameLocale = $folderModel->getTranslation('name', config('app.locale'));

                return $folderModel;
            })->sortBy('nameLocale')->sortByDesc('mandatory')->values();
        // end if
    }

    /**
     * Get tag models published.
     *
     * @param  bool  $paginate  Paginate the query.
     * @param  int  $nbrItemsPerPage  Number of items per pagination page.
     */
    protected function getTagsPublished(
        bool $paginate = false,
        int $nbrItemsPerPage = 12,
    ): LengthAwarePaginator | Collection {
        $query = Tag::query()->where('published', true);

        if ($paginate) {
            /** @var LengthAwarePaginator $tagModels */
            $tagModels = $query->orderBy('name')
                ->paginate($nbrItemsPerPage)
                ->through(function (Tag $tagModel) {
                    // @phpstan-ignore-next-line
                    $tagModel->nameLocale = $tagModel->getTranslation('name', config('app.locale'));

                    return $tagModel;
                });

            return new LengthAwarePaginator(
                $tagModels->sortBy('nameLocale')->values(),
                $query->paginate($nbrItemsPerPage)->total(),
                $nbrItemsPerPage
            );
        }

        return $query->get()
            ->map(function (Tag $tagModel) {
                // @phpstan-ignore-next-line
                $tagModel->nameLocale = $tagModel->getTranslation('name', config('app.locale'));

                return $tagModel;
            })->sortBy('nameLocale')->sortByDesc('mandatory')->values();
        // end if
    }

    /**
     * Get rank models published.
     */
    protected function getRanksPublished(): Collection
    {
        return Rank::query()
            ->orderby('rank', 'asc')
            ->with('game')
            ->whereHas('game', function (Builder $query): void {
                $query->where('published', true);
            })
            ->get()
            ->map(function (Rank $rank) {
                // @phpstan-ignore-next-line
                $rank->game_name = $rank->game->name;
                // @phpstan-ignore-next-line
                $rank->game_slug = $rank->game->slug;

                return $rank;
            });
    }

    /**
     * Build Search query on specified fields
     * splitting search into words or using whole sentence
     *
     * @param  Builder  $query  The eloquent query builder.
     * @param  string  $search  The query string.
     * @param  callable|null  $searchQuery  The query string.
     * @param  string  ...$fields  Either a simple array or a string
     *                             in case of a string the two fields
     *                             would be concatenate to make the search.
     */
    protected function searchQuery(
        Builder $query,
        string $search,
        ?callable $searchQuery = null,
        string ...$fields
    ): Builder {
        if (count($fields)) {
            // Search using words.
            $words = explode(' ', $search);
            $query->where(function (Builder $query) use ($searchQuery, $words, $search, $fields): void {
                foreach ($words as $word) {
                    if (! strlen($word)) {
                        continue;
                    }
                    $this->searchOnFields($query, $word, $fields);
                } // end foreach
                // Search whole using whole sentence.
                $this->searchOnFields($query, $search, $fields);
                if ($searchQuery) {
                    $searchQuery($query);
                }
            });
        } // end if

        return $query;
    }

    /**
     * Build query search on all fields
     */
    private function searchOnFields(
        Builder $query,
        string $search,
        array $fields
    ): Builder {
        $table = $query->getModel()->getTable();
        foreach ($fields as $field) {
            $field = str_replace('?', '\?', $field);
            if (is_array($field)) {
                $query->orWhereRaw(
                    sprintf('UPPER(CONCAT(%s)) LIKE UPPER(?)', collect($field)->map(fn ($string) => "`$table`.`$string`")->implode(', \' \', ')),
                    ['%' . htmlspecialchars($search) . '%']
                );

                return $query;
            }
            // Get models with enum's label.
            if (is_array($this->getSearchValue($query, $search, $field))) {
                foreach ($this->getSearchValue($query, $search, $field) as $array) {
                    $query->orWhereRaw(
                        "UPPER(`$table`.`$field`) LIKE UPPER(?)",
                        ['%' . htmlspecialchars($array->value) . '%']
                    );
                }

                return $query;
            }
            // Get models with relation's name/label.
            if (Str::endsWith($field, '_id')) {
                $query->orWhereHas(Str::remove('_id', $field), function (Builder $queryRelation) use ($field, $search): void {
                    if (Schema::hasColumn(Str::remove('_id', $field) . 's', 'name')) {
                        $queryRelation->where('name', 'like', '%' . $search . '%');
                    } elseif (Schema::hasColumn(Str::remove('_id', $field) . 's', 'label')) {
                        $queryRelation->where('label', 'like', '%' . $search . '%');
                    }
                });

                return $query;
            }
            $query->orWhereRaw(
                "UPPER(`$table`.`$field`) LIKE UPPER(?)",
                ['%' . htmlspecialchars($this->getSearchValue($query, $search, $field)) . '%']
            );
        } // end foreach

        return $query;
    }

    /**
     * Get the search value (on string or Enum).
     */
    protected function getSearchValue(Builder $query, string $search, string $field): string | array
    {
        if (
            count($query->getModel()->getCasts())         and // Check if the model has casts.
            isset($query->getModel()->getCasts()[$field]) and // Check if there is cast about specific field.
            enum_exists($query->getModel()->getCasts()[$field]) // Check if the cast is an enum.
        ) {
            // @phpstan-ignore-next-line
            $cases = collect($query->getModel()->getCasts()[$field]::toArray());
            $enum  = $cases->filter(fn ($item) => preg_match(
                '/' . Str::of($search)->lower()->value() . '/',
                Str::of($item->label)->lower()->value(),
            ));

            return $enum->toArray();
        }

        return $search;
    }

    /**
     * Sort columns with a query.
     */
    protected function sortQuery(Builder $query, array $ignore = []): Builder
    {
        $rName = request()->route()->getName();
        $table = $query->getModel()->getTable();
        if (request()->sort_col) {
            Session::put("$rName.sort_col", request()->sort_col);
            Session::put("$rName.sorted", true);
        }
        if (request()->sort_way) {
            Session::put("$rName.sort_way", request()->sort_way);
            Session::put("$rName.sorted", true);
        }
        if (request()->rst) {
            Session::remove("$rName.sorted");
            Session::remove("$rName.sort_col");
            Session::remove("$rName.sort_way");
        }
        if (request()->sort_col and request()->sort_way and Schema::hasColumn($table, request()->sort_col)) {
            if (
                ! empty($ignore) or ! in_array(request()->sort_col, $ignore)
            ) {
                $query = $query->orderBy(\sprintf("$table.%s", request()->sort_col), request()->sort_way);
            }
        } elseif (Schema::hasColumn($table, 'order')) {
            $query = $query->orderBy('order', 'asc');
            Session::put("$rName.sort_col", 'order');
            Session::put("$rName.sort_way", 'asc');
        } elseif (Schema::hasColumn($table, 'updated_at')) {
            $query = $query->latest('updated_at');
            Session::put("$rName.sort_col", 'updated_at');
            Session::put("$rName.sort_way", 'desc');
        } elseif (Schema::hasColumn($table, 'created_at')) {
            $query = $query->latest();
            Session::put("$rName.sort_col", 'created_at');
            Session::put("$rName.sort_way", 'desc');
        }

        return $query;
    }

    /**
     * Customize pagination with cache or config (default).
     */
    protected function paginate(
        Builder $query,
        ?ItemsPerPaginationEnum $pagination = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $currentRoutePath = Str::of(request()->route()->getName())->slug();
        $sessionKey       = "pagination.{$currentRoutePath}";
        $pagination ??= ItemsPerPaginationEnum::twelve;
        $pagination       = ToolboxHelper::getValidatedEnum(
            Session::get($sessionKey, $pagination->value()),
            'pagination',
            ItemsPerPaginationEnum::class,
        );
        if (Session::get($sessionKey) !== $pagination) {
            Session::put($sessionKey, $pagination);
        }

        return $query->paginate($pagination);
    }

    /**
     * Set the current locale.
     */
    public function setLang(LangRequest $request): RedirectResponse
    {
        $lang = $request->validated('lang');
        app()->setLocale($lang);
        Session::put('lang', $lang);

        return back()->with('success', trans('crud.messages.lang_updated'));
    }

    /**
     * Set the navigation size.
     */
    protected function setNavigation(NavigationRequest $request): void
    {
        Session::put('navigation', $request->validated('isExtended'));
    }

    /**
     * Set the bootstrap theme or light (default).
     */
    protected function setTheme(ThemeRequest $request): RedirectResponse
    {
        $theme = ToolboxHelper::getValidatedEnum(
            $request->validated('theme') ?: Session::get('theme', BootstrapThemeEnum::light->value()),
            'theme',
            '\App\Enums\Theme\BootstrapThemeEnum',
        );
        if (Session::get('theme') !== $theme) {
            Session::put('theme', $theme);
        }

        return back()->with('success', trans('crud.messages.theme_updated'));
    }

    /**
     * Get a paginate resource.
     */
    public function jsonSearchPaginate(JsonPaginateRequest $request): JsonResponse
    {
        /** @var string $targetModel */
        $targetModel = $request->validated('targetModel');

        /** @var string $search */
        $search = $request->validated('search');

        /** @var Builder $query */
        $query = $targetModel::query()->where('published', true);
        $query = ($targetModel === Folder::class)
            ? $query->orderby('mandatory', 'desc')
            : $query;

        /** @var LengthAwarePaginator $models */
        $models = $query->orderBy('name')
            ->where('name', 'like', "%{$search}%")
            ->paginate($this->modelsPerPage)
            ->through(function ($model) {
                // @phpstan-ignore-next-line
                $model->nameLocale = $model->getTranslation('name', config('app.locale'));

                return $model;
            });

        /** @var Collection $modelsSorted */
        $modelsSorted = $models->sortBy('nameLocale');
        $modelsSorted = ($targetModel === Folder::class)
            ? $modelsSorted->sortByDesc('mandatory')
            : $modelsSorted;

        return \response()->json(new LengthAwarePaginator(
            $modelsSorted->values(),
            $models->total(),
            $this->modelsPerPage
        ));
    }
}
