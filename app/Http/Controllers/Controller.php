<?php

namespace App\Http\Controllers;

use App\Enums\Pagination\ItemsPerPaginationEnum;
use App\Enums\Theme\BootstrapThemeEnum;
use App\Lib\Helpers\ToolboxHelper;
use App\Models\Folder;
use App\Models\Game;
use App\Models\Rank;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
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
     * @var integer
     */
    protected $modelsPerPage = 12;

    /**
     * Use for pagination,
     * number of games per page.
     *
     * @var integer
     */
    protected $gamesPerPage = 20;

    /**
     * Get game models published.
     *
     * @param boolean $paginate        Paginate the query.
     * @param integer $nbrItemsPerPage Number of items per pagination page.
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    protected function getGamesPublished(
        bool $paginate = false,
        int $nbrItemsPerPage = 12,
    ): \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection {
        $searchText   = request()->text;
        $searchFolder = request()->folder;
        $searchTag    = request()->tag;
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Game::query()
            ->with('pictures')
            ->when($searchText, function (Builder $query) use ($searchText) {
                return $query->where('name', 'LIKE', '%' . $searchText . '%');
            })
            ->when($searchFolder, function (Builder $query) use ($searchFolder) {
                return $query->whereHas('folder', function (Builder $query) use ($searchFolder) {
                    $query->where('slug', $searchFolder);
                });
            })
            ->when($searchTag, function (Builder $query) use ($searchTag) {
                return $query->whereHas('tags', function (Builder $query) use ($searchTag) {
                    $query->where('slug', $searchTag);
                });
            })
            ->where('published', true)
            ->orderBy('slug', 'ASC')
            ->whereHas('folder', function (Builder $query) {
                $query->where('published', true);
            });
        return ($paginate) ? $query->paginate($nbrItemsPerPage) : $query->get();
    }

    /**
     * Get folder models published.
     *
     * @param boolean $paginate        Paginate the query.
     * @param integer $nbrItemsPerPage Number of items per pagination page.
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    protected function getFoldersPublished(
        bool $paginate = false,
        int $nbrItemsPerPage = 12,
    ): \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Folder::query()->where('published', true);

        if ($paginate) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $folderModels */
            $folderModels = $query->orderby('mandatory', 'DESC')
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
        } else {
            return $query->get()
                ->map(function ($folderModel) {
                    // @phpstan-ignore-next-line
                    $folderModel->nameLocale = $folderModel->getTranslation('name', config('app.locale'));
                    return $folderModel;
                })->sortBy('nameLocale')->sortByDesc('mandatory')->values();
        } //end if
    }

    /**
     * Get tag models published.
     *
     * @param boolean $paginate        Paginate the query.
     * @param integer $nbrItemsPerPage Number of items per pagination page.
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    protected function getTagsPublished(
        bool $paginate = false,
        int $nbrItemsPerPage = 12,
    ): \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection {
        $query = Tag::query()->where('published', true);

        if ($paginate) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $tagModels */
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
        } else {
            return $query->get()
                ->map(function (Tag $tagModel) {
                    // @phpstan-ignore-next-line
                    $tagModel->nameLocale = $tagModel->getTranslation('name', config('app.locale'));
                    return $tagModel;
                })->sortBy('nameLocale')->sortByDesc('mandatory')->values();
        } //end if
    }

    /**
     * Get rank models published.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRanksPublished(): \Illuminate\Support\Collection
    {
        return Rank::query()
            ->orderby('rank', 'ASC')
            ->with('game')
            ->whereHas('game', function (Builder $query) {
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
     * @param \Illuminate\Database\Eloquent\Builder $query       The eloquent query builder.
     * @param string                                $search      The query string.
     * @param callable|null                         $searchQuery The query string.
     * @param string                                ...$fields   Either a simple array or a string
     *                                                           in case of a string the two fields
     *                                                           would be concatenate to make the search.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function searchQuery(
        Builder $query,
        string $search,
        callable|null $searchQuery = null,
        string ...$fields
    ): \Illuminate\Database\Eloquent\Builder {
        if (count($fields)) {
            // Search using words.
            $words = explode(' ', $search);
            $query->where(function (Builder $query) use ($searchQuery, $words, $search, $fields) {
                foreach ($words as $word) {
                    if (!strlen($word)) {
                        continue;
                    }
                    $this->searchOnFields($query, $word, $fields);
                } //end foreach
                // Search whole using whole sentence.
                $this->searchOnFields($query, $search, $fields);
                if ($searchQuery) {
                    $searchQuery($query);
                }
            });
        } //end if
        return $query;
    }

    /**
     * Build query search on all fields
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $search
     * @param array                                 $fields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function searchOnFields(
        Builder $query,
        string $search,
        array $fields
    ): \Illuminate\Database\Eloquent\Builder {
        $table = $query->getModel()->getTable();
        foreach ($fields as $field) {
            $field = str_replace('?', '\?', $field);
            if (is_array($field)) {
                $query->orWhereRaw(
                    sprintf("UPPER(CONCAT(%s)) LIKE UPPER(?)", collect($field)->map(function ($string) use ($table) {
                        return "`$table`.`$string`";
                    })->implode(', \' \', ')),
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
                $query->orWhereHas(Str::remove('_id', $field), function (Builder $queryRelation) use ($field, $search) {
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
        } //end foreach
        return $query;
    }

    /**
     * Get the search value (on string or Enum).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $search
     * @param string                                $field
     * @return string|array
     */
    protected function getSearchValue(Builder $query, string $search, string $field): string|array
    {
        if (
            count($query->getModel()->getCasts()) and // Check if the model has casts.
            isset($query->getModel()->getCasts()[$field]) and // Check if there is cast about specific field.
            enum_exists($query->getModel()->getCasts()[$field]) // Check if the cast is an enum.
        ) {
            // @phpstan-ignore-next-line
            $cases = collect($query->getModel()->getCasts()[$field]::toArray());
            $enum  = $cases->filter(function ($item) use ($search) {
                return preg_match('/' . Str::of($search)->lower() . '/', Str::of($item->label)->lower());
            });
            return $enum->toArray();
        } else {
            return $search;
        }
    }

    /**
     * Sort columns with a query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array                                 $ignore
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function sortQuery(Builder $query, array $ignore = []): \Illuminate\Database\Eloquent\Builder
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
                !empty($ignore) or
                !in_array(request()->sort_col, $ignore)
            ) {
                $query = $query->orderBy(\sprintf("$table.%s", request()->sort_col), request()->sort_way);
            }
        } elseif (Schema::hasColumn($table, 'order')) {
            $query = $query->orderBy('order', 'ASC');
            Session::put("$rName.sort_col", 'order');
            Session::put("$rName.sort_way", 'asc');
        } elseif (Schema::hasColumn($table, 'updated_at')) {
            $query = $query->orderBy('updated_at', 'DESC');
            Session::put("$rName.sort_col", 'updated_at');
            Session::put("$rName.sort_way", 'desc');
        } elseif (Schema::hasColumn($table, 'created_at')) {
            $query = $query->orderBy('created_at', 'DESC');
            Session::put("$rName.sort_col", 'created_at');
            Session::put("$rName.sort_way", 'desc');
        }
        return $query;
    }

    /**
     * Customize pagination with cache or config (default).
     *
     * @param \Illuminate\Database\Eloquent\Builder             $query
     * @param \App\Enums\Pagination\ItemsPerPaginationEnum|null $pagination
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function paginate(
        Builder $query,
        ItemsPerPaginationEnum|null $pagination = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $currentRoutePath = Str::of(request()->route()->getName())->slug();
        $sessionKey       = "pagination.{$currentRoutePath}";
        $pagination       = $pagination ?? ItemsPerPaginationEnum::twelve;
        $pagination       = ToolboxHelper::getValidatedEnum(
            Session::get($sessionKey, $pagination->value()),
            'pagination',
            '\App\Enums\Pagination\ItemsPerPaginationEnum',
        );
        if (Session::get($sessionKey) !== $pagination) {
            Session::put($sessionKey, $pagination);
        }
        return $query->paginate($pagination);
    }

    /**
     * Set the current locale.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLang(Request $request): \Illuminate\Http\RedirectResponse
    {
        $lang = $request->lang;
        if (!\in_array($lang, config('app.locales'))) {
            $lang = config('app.fallback_locale');
        }
        app()->setLocale($lang);
        Session::put('lang', $lang);
        return redirect()->back()->with('success', trans('crud.messages.lang_updated'));
    }

    /**
     * Set the navigation size.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function setNavigation(Request $request): void
    {
        Session::put("navigation", $request->isExtended);
    }

    /**
     * Set the bootstrap theme or light (default).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function setTheme(Request $request): \Illuminate\Http\RedirectResponse
    {
        $theme = ToolboxHelper::getValidatedEnum(
            $request->theme ?: Session::get('theme', BootstrapThemeEnum::light->value()),
            'theme',
            '\App\Http\Controllers\BootstrapThemeEnum',
        );
        if (Session::get('theme') !== $theme) {
            Session::put('theme', $theme);
        }
        return redirect()->back()->with('success', trans('crud.messages.theme_updated'));
    }

    /**
     * Get a paginate resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonSearchPaginate(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $request->targetModel::query()->where('published', true);
        $query = ($request->targetModel === "\App\Models\Folder")
            ? $query->orderby('mandatory', 'DESC')
            : $query;

        /** @var \Illuminate\Pagination\LengthAwarePaginator $models */
        $models = $query->orderBy('name')
            ->where('name', 'like', "%{$request->input('search')}%")
            ->paginate($this->modelsPerPage)
            ->through(function ($model) {
                $model->nameLocale = $model->getTranslation('name', config('app.locale'));
                return $model;
            });
        /** @var \Illuminate\Support\Collection $modelsSorted */
        $modelsSorted = $models->sortBy('nameLocale');
        $modelsSorted = ($request->targetModel === "\App\Models\Folder")
            ? $modelsSorted->sortByDesc('mandatory')
            : $modelsSorted;
        return \response()->json(new LengthAwarePaginator(
            $modelsSorted->values(),
            $models->total(),
            $this->modelsPerPage
        ));
    }
}
