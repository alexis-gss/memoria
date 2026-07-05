<?php

namespace App\Http\Controllers\Fo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fo\GamesFilteredRequest;
use App\Http\Requests\Fo\MusicOptionsRequest;
use App\Http\Requests\Fo\NextPicturesGameRequest;
use App\Http\Requests\Fo\NextRelatedGamesRequest;
use App\Http\Requests\Fo\ShowGameRequest;
use App\Lib\Helpers\ToolboxHelper;
use App\Models\Game;
use App\Models\Rating;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class GameController extends Controller
{
    /**
     * Show a specific game.
     *
     * @param \App\Http\Requests\Fo\ShowGameRequest $request
     * @return \Illuminate\Http\Response
     */
    public function show(ShowGameRequest $request): \Illuminate\Http\Response
    {
        $validatedData = $request->validated();

        $gameModel = Game::query()
            ->where('published', true)
            ->where('slug', $validatedData['slug'])
            ->whereHas('folder', function (Builder $query) {
                $query->where('published', true);
            })->firstOrFail();

        /** @var \Illuminate\Database\Eloquent\Collection $ratingModels */
        $ratingModels = Rating::query()
            ->where('uuid', $request->cookie('rating-uuid'))
            ->get()
            ->map(function ($rating) {
                return $rating->picture_id;
            });

        $cookie = (new Visit())->setVisit($request, $gameModel);

        $musicOptions = json_decode($request->cookie('music-options'), true) ?? [
            'volume' => 1,
            'muted'  => true,
            'loop'   => false,
        ];

        return response(view('front.pages.game', [
            'gameModel'         => $gameModel,
            'pictureModels'     => $this->getPicturesOfGame($gameModel, $request->query('order', 'date')),
            'ratingModels'      => $ratingModels,
            'gameModels'        => $this->getGamesPublished(true, $this->gamesPerPage),
            'folderModels'      => $this->getFoldersPublished(),
            'tagModels'         => $this->getTagsPublished(),
            'relatedGamesViews' => $this->getRelatedGamesViews($gameModel),
            'music'             => [
                'options' => $musicOptions,
                'data'    => $this->getMusicMetadataAttribute($gameModel),
            ],
        ]))->withCookie($cookie);
    }

    /**
     * Return a list of games filtered.
     *
     * @param \App\Http\Requests\Fo\GamesFilteredRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGamesFiltered(GamesFilteredRequest $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validated();

        /** @var array<string|null> $searchSelects Selected folder/tag id */
        $searchSelects = $validatedData['filters_id'] ?? [];
        /** @var string|null $searchText Search text */
        $searchText = $validatedData['search'] ?? null;
        /** @var string $sort Sort order */
        $sort = $validatedData['sort'] ?? 'alphabetical';

        /** @var \Illuminate\Support\Collection $gamesFiltered */
        $gamesFiltered = Game::query()
            ->when(
                isset($searchSelects[1]) && !empty($searchSelects[1]),
                function (Builder $query) use ($searchSelects) {
                    $query->whereHas('folder', function (Builder $query) use ($searchSelects) {
                        $query->where('slug', $searchSelects[1]);
                    });
                }
            )
            ->when(
                isset($searchSelects[0]) && !empty($searchSelects[0]),
                function (Builder $query) use ($searchSelects) {
                    $query->whereHas('tags', function (Builder $query) use ($searchSelects) {
                        $query->where('slug', $searchSelects[0]);
                    });
                }
            )
            ->when(!is_null($searchText), function (Builder $query) use ($searchText) {
                $query->where('name', 'LIKE', "%{$searchText}%");
            })
            ->with('pictures')
            ->where('published', true)
            ->whereHas('folder', function (Builder $query) {
                $query->where('published', true);
            })
            ->withCount('pictures')
            ->when($sort === 'music', function (Builder $query) {
                $query->orderByRaw('music IS NULL, music IS NOT NULL')
                    ->orderBy('slug', 'ASC');
            })
            ->when($sort === 'pictures', function (Builder $query) {
                $query->orderBy('pictures_count', 'DESC')->orderBy('slug', 'ASC');
            })
            ->when($sort === 'alphabetical' || !in_array($sort, ['music', 'pictures']), function (Builder $query) {
                $query->orderBy('slug', 'ASC');
            })
            ->paginate($this->gamesPerPage);

        return response()->json($gamesFiltered);
    }

    /**
     * Return a list of games filtered.
     *
     * @param \App\Http\Requests\Fo\NextPicturesGameRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextPicturesGame(
        NextPicturesGameRequest $request,
    ): \Illuminate\Http\JsonResponse {
        $validatedData = $request->validated();

        $currentGameModel = Game::query()->where('slug', $validatedData['slug'])->firstOrFail();
        return response()->json($this->getPicturesOfGame($currentGameModel, $validatedData['order']));
    }

    /**
     * Return a list of games views render.
     *
     * @param \App\Models\Game $gameModel
     * @param string           $order
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPicturesOfGame(
        Game $gameModel,
        string $order = 'date',
    ): \Illuminate\Pagination\LengthAwarePaginator {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $pictureModels */
        $pictureModels = new LengthAwarePaginator([], 0, 12);
        if ($gameModel->pictures->isNotEmpty()) {
            $gameModel->pictures->each(function ($picture) {
                // @phpstan-ignore-next-line
                $picture->ratings_count = $picture->ratings->count();
            });
            $orderedPictures = match ($order) {
                'likes'  => $gameModel->pictures->sortByDesc('ratings_count')->values(),
                default  => $gameModel->pictures->values(),
            };
            $pictureModels = ToolboxHelper::customPaginate(
                $orderedPictures,
                ($orderedPictures->count() <= 12) ? $orderedPictures->count() : 12,
                ['path' => Paginator::resolveCurrentPath()]
            );
        }
        return $pictureModels;
    }

    /**
     * Return a list of games filtered.
     *
     * @param \App\Http\Requests\Fo\NextRelatedGamesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNextRelatedGames(NextRelatedGamesRequest $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validated();

        $currentGameModel = Game::query()->where('slug', $validatedData['slug'])->firstOrFail();
        return response()->json($this->getRelatedGamesViews($currentGameModel));
    }

    /**
     * Return a list of games views render.
     *
     * @param \App\Models\Game $gameModel
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getRelatedGamesViews(Game $gameModel): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Game::query()
            ->with('pictures', 'folder')
            ->where([['published', true], ['id', '!=', $gameModel->getKey()]])
            ->whereHas('folder', function (Builder $query) use ($gameModel) {
                $query->where([['published', true], ['id', $gameModel->folder->getKey()]]);
            })
            ->orderby('published_at', 'DESC')
            ->paginate(6)
            ->through(function (Game $randomGameModel) {
                return view('components.front.card-game', ['gameModel' => $randomGameModel])->render();
            });
    }

    /**
     * Save music preferences to cookie.
     *
     * @param \App\Http\Requests\Fo\MusicOptionsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveMusicOptions(MusicOptionsRequest $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validated();

        $payload = json_encode([
            'muted'  => $validatedData['muted'],
            'volume' => $validatedData['volume'],
            'loop'   => $validatedData['loop'],
            'speed'  => $validatedData['speed'],
        ]);

        $cookie = cookie(
            'music-options',
            $payload,
            10 * 365 * 24 * 60
        );

        return response()->json(['success' => true])->withCookie($cookie);
    }

    /**
     * Get music data from mp3 file.
     *
     * @param \App\Models\Game $gameModel
     * @return array|null
     */
    private function getMusicMetadataAttribute(Game $gameModel): ?array
    {
        if (!$gameModel->music) {
            return null;
        }

        if (!file_exists($gameModel->music)) {
            return null;
        }

        $getID3   = new \getID3();
        $fileInfo = $getID3->analyze($gameModel->music);
        $tags     = $fileInfo['tags']['id3v2'] ?? $fileInfo['tags']['id3v1'] ?? [];

        return [
            'src'      => asset($gameModel->music),
            'title'    => $tags['title'][0] ?? pathinfo($gameModel->music, PATHINFO_FILENAME),
            'artist'   => $tags['artist'][0] ?? null,
            'duration' => $fileInfo['playtime_seconds'] ?? null,
            'cover'    => !empty($fileInfo['comments']['picture'][0]['data'])
                ? 'data:' . $fileInfo['comments']['picture'][0]['image_mime'] . ';base64,' .
                base64_encode($fileInfo['comments']['picture'][0]['data'])
                : null,
        ];
    }
}
