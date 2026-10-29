<?php

declare(strict_types=1);

use App\Enums\Pages\StaticPageTypeEnum;
use App\Models\Game;
use App\Models\StaticPage;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator;

$homeRouteName    = StaticPageTypeEnum::home->routeName();
$rankingRouteName = StaticPageTypeEnum::ranking->routeName();

// * HOMEPAGE.
Breadcrumbs::for($homeRouteName, function (Generator $trail) use ($homeRouteName): void {
    $staticPageHome = StaticPage::query()->where('type', StaticPageTypeEnum::home->value())->first();
    $trail->push($staticPageHome->title, route($homeRouteName));
});

// * GAMES.
Breadcrumbs::for('fo.games.show', function (Generator $trail, ?Game $gameModel = null) use ($homeRouteName): void {
    $trail->parent($homeRouteName);
    if (! is_null($gameModel)) {
        $trail->push($gameModel->name);
    }
});

// * RANKS.
Breadcrumbs::for($rankingRouteName, function (Generator $trail) use ($homeRouteName, $rankingRouteName): void {
    $staticPageRanking = StaticPage::query()->where('type', StaticPageTypeEnum::ranking->value())->first();
    $trail->parent($homeRouteName);
    $trail->push($staticPageRanking->title, route($rankingRouteName));
});
