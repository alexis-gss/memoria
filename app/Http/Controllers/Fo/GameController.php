<?php

declare(strict_types=1);

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
use getID3;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class GameController extends Controller
{
    /**
     * Show a specific game.
     */
    public function show(ShowGameRequest $request): Response
    {
        $validatedData = $request->validated();

        $gameModel = Game::query()
            ->where('published', true)
            ->where('slug', $validatedData['slug'])
            ->whereHas('folder', function (Builder $query): void {
                $query->where('published', true);
            })->firstOrFail();

        $ratingModels = Rating::query()
            ->where('uuid', $request->cookie('rating-uuid'))
            ->get()
            ->map(fn ($rating) => $rating->picture_id);

        $cookie = (new Visit())->setVisit($request, $gameModel);

        $musicOptionsCookie = $request->cookie('music-options');
        $musicOptions       = \is_string($musicOptionsCookie)
            ? \json_decode($musicOptionsCookie, true)
            : null;

        if (! \is_array($musicOptions)) {
            $musicOptions = [
                'volume' => 1,
                'muted'  => true,
                'loop'   => false,
            ];
        }

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
     */
    public function getGamesFiltered(GamesFilteredRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var array<string|null> $searchSelects Selected folder/tag id */
        $searchSelects = $validatedData['filters_id'] ?? [];
        /** @var string|null $searchText Search text */
        $searchText = $validatedData['search'] ?? null;
        /** @var string $sort Sort order */
        $sort = $validatedData['sort'] ?? 'alphabetical';

        $gamesFiltered = Game::query()
            ->when(
                isset($searchSelects[1]) && ! empty($searchSelects[1]),
                function (Builder $query) use ($searchSelects): void {
                    $query->whereHas('folder', function (Builder $query) use ($searchSelects): void {
                        $query->where('slug', $searchSelects[1]);
                    });
                }
            )
            ->when(
                isset($searchSelects[0]) && ! empty($searchSelects[0]),
                function (Builder $query) use ($searchSelects): void {
                    $query->whereHas('tags', function (Builder $query) use ($searchSelects): void {
                        $query->where('slug', $searchSelects[0]);
                    });
                }
            )
            ->when(! is_null($searchText), function (Builder $query) use ($searchText): void {
                $query->where('name', 'LIKE', "%{$searchText}%");
            })
            ->with('pictures')
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
            })
            ->paginate($this->gamesPerPage);

        return response()->json($gamesFiltered);
    }

    /**
     * Return a list of games filtered.
     */
    public function getNextPicturesGame(
        NextPicturesGameRequest $request,
    ): JsonResponse {
        $validatedData = $request->validated();

        $currentGameModel = Game::query()->where('slug', $validatedData['slug'])->firstOrFail();

        return response()->json($this->getPicturesOfGame($currentGameModel, $validatedData['order']));
    }

    /**
     * Return a list of games views render.
     */
    public function getPicturesOfGame(
        Game $gameModel,
        string $order = 'date',
    ): LengthAwarePaginator {
        $pictureModels = new LengthAwarePaginator([], 0, 12);
        if ($gameModel->pictures->isNotEmpty()) {
            $gameModel->pictures->each(function ($picture): void {
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
     */
    public function getNextRelatedGames(NextRelatedGamesRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $currentGameModel = Game::query()->where('slug', $validatedData['slug'])->firstOrFail();

        return response()->json($this->getRelatedGamesViews($currentGameModel));
    }

    /**
     * Return a list of games views render.
     */
    public function getRelatedGamesViews(Game $gameModel): LengthAwarePaginator
    {
        return Game::query()
            ->with('pictures', 'folder')
            ->where([['published', true], ['id', '!=', $gameModel->getKey()]])
            ->whereHas('folder', function (Builder $query) use ($gameModel): void {
                $query->where([['published', true], ['id', $gameModel->folder->getKey()]]);
            })
            ->latest('published_at')
            ->paginate(6)
            ->through(fn (Game $randomGameModel) => view('components.front.card-game', ['gameModel' => $randomGameModel])->render());
    }

    /**
     * Save music preferences to cookie.
     */
    public function saveMusicOptions(MusicOptionsRequest $request): JsonResponse
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
     */
    private function getMusicMetadataAttribute(Game $gameModel): ?array
    {
        if (! $gameModel->music) {
            return null;
        }

        if (! file_exists($gameModel->music)) {
            return null;
        }

        $getID3   = new getID3();
        $fileInfo = $getID3->analyze($gameModel->music);
        $tags     = $fileInfo['tags']['id3v2'] ?? $fileInfo['tags']['id3v1'] ?? [];

        return [
            'src'      => asset($gameModel->music),
            'title'    => $tags['title'][0]             ?? pathinfo($gameModel->music, PATHINFO_FILENAME),
            'artist'   => $tags['artist'][0]            ?? null,
            'duration' => $fileInfo['playtime_seconds'] ?? null,
            'cover'    => ! empty($fileInfo['comments']['picture'][0]['data'])
                ? 'data:' . $fileInfo['comments']['picture'][0]['image_mime'] . ';base64,' .
                base64_encode($fileInfo['comments']['picture'][0]['data'])
                : null,
        ];
    }
}
