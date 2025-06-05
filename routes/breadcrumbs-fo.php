<?php

use App\Models\Game;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator;

// * HOMEPAGE
Breadcrumbs::for('fo.games.index', function (Generator $trail) {
    $trail->push(trans('fo_home_title'), route('fo.games.index'));
});

// * GAMES
Breadcrumbs::for('fo.games.show', function (Generator $trail, Game|null $gameModel = null) {
    $trail->parent('fo.games.index');
    if (!is_null($gameModel)) {
        $trail->push($gameModel->name);
    }
});

// * RANKS
Breadcrumbs::for('fo.ranks.index', function (Generator $trail) {
    $trail->parent('fo.games.index');
    $trail->push(trans('fo_ranking_title'));
});
