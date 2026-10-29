<?php

declare(strict_types=1);

namespace App\Http\Controllers\Fo;

use App\Enums\Pages\StaticPageTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\StaticPage;
use Illuminate\Contracts\View\View;

class StaticPageController extends Controller
{
    /**
     * Show the application homepage.
     */
    public function home(): View
    {
        try {
            $staticPageModel = StaticPage::query()->where('type', StaticPageTypeEnum::home->value())->firstOrFail();
        } catch (\Exception $exception) {
            abort(404, $exception->getMessage());
        }

        $gameLatestModels = Game::query()->where('published', true)->latest('published_at')->take(20)->get();

        return view('front.pages.home', [
            'staticPageModel'  => $staticPageModel,
            'gameLatestModels' => $gameLatestModels,
            'gameModels'       => $this->getGamesPublished(true, $this->gamesPerPage),
            'folderModels'     => $this->getFoldersPublished(),
            'tagModels'        => $this->getTagsPublished(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function ranking(): View
    {
        try {
            $staticPageModel = StaticPage::query()->where('type', StaticPageTypeEnum::ranking->value())->firstOrFail();
        } catch (\Exception $exception) {
            abort(404, $exception->getMessage());
        }

        return view('front.pages.ranking', [
            'staticPageModel' => $staticPageModel,
            'gameModels'      => $this->getGamesPublished(true, $this->gamesPerPage),
            'folderModels'    => $this->getFoldersPublished(),
            'tagModels'       => $this->getTagsPublished(),
            'rankModels'      => $this->getRanksPublished(),
        ]);
    }
}
