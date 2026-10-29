<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Ranks\StoreRankRequest;
use App\Models\Game;
use App\Models\Rank;
use App\Traits\Controllers\ChangesModelOrder;
use App\Traits\Controllers\UpdateModelPublished;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RankController extends Controller
{
    use ChangesModelOrder;
    use UpdateModelPublished;

    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Rank::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('back.pages.ranks.index', [
            'rankModels'   => $this->getRanksPublished(),
            'gameModels'   => $this->getPublishedGamesInNotRanking()->paginate($this->modelsPerPage),
            'searchFields' => trans('validation.attributes.name'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRankRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            foreach ($request->validated()['ranks'] as $dataValidated) {
                $rank = (new Rank())->fill(['game_id' => $dataValidated]);
                if (! $rank->saveOrFail()) {
                    return back()
                        ->with('error', trans('crud.messages.cannot_be_updated', [
                            'model' => Str::of(trans('models.rank'))->ucfirst()->value(),
                        ]));
                }
            }

            return to_route('bo.ranks.index')
                ->with('success', trans('crud.messages.has_been_updated', [
                    'model' => Str::of(trans('models.rank'))->ucfirst()->value(),
                ]));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rank $rank): array | Collection
    {
        return DB::transaction(function () use ($rank) {
            if ($rank->deleteOrFail()) {
                return $this->getRanksPublished();
            }

            return [
                'message' => trans('crud.messages.cannot_be_deleted', [
                    'model' => Str::of(trans('models.rank'))->ucfirst()->value(),
                ]),
            ];
        });
    }

    /**
     * Save and assign the new order/structure of ranks.
     */
    public function saveOrder(Request $request): void
    {
        foreach ($request->ranks as $newRank) {
            $rank         = Rank::where('id', $newRank['id'])->first();
            $rank['rank'] = $newRank['rank'];
            $rank->saveOrFail();
        }
    }

    /**
     * Get published games that aren't in ranking.
     */
    public function jsonSearchPaginateRanks(Request $request): LengthAwarePaginator
    {
        return $this->getPublishedGamesInNotRanking()
            ->where('name', 'like', "%{$request->input('search')}%")
            ->paginate($request->input('paginate') ?? $this->modelsPerPage);
    }

    /**
     * Get published games that aren't in ranking.
     */
    public function getPublishedGamesInNotRanking(): Builder
    {
        return Game::query()
            ->whereNotIn('id', Rank::query()->pluck('game_id')->all())
            ->where('published', true)
            ->whereHas('folder', function (Builder $query): void {
                $query->where('published', true);
            })
            ->orderBy('slug', 'asc');
    }
}
