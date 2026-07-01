<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\Fo\GameController;
use App\Http\Controllers\Fo\StaticPageController;
use App\Http\Controllers\Fo\RatingController;
use Illuminate\Support\Facades\Route;

Route::name('fo.')
    ->group(function () {
        // * GAME
        Route::get('/game/{slug}', [GameController::class, 'show'])
            ->where('slug', '^[a-zA-Z0-9-]*$')
            ->name('games.show');
        Route::get('/game/{slug}/pictures', [GameController::class, 'getNextPicturesOfGame'])
            ->where('slug', '^[a-zA-Z0-9-]*$')
            ->name('games.pictures');
        Route::get('/game/{slug}/related', [GameController::class, 'getNextRelatedGames'])
            ->where('slug', '^[a-zA-Z0-9-]*$')
            ->name('games.related');

        // * MUSIC
        Route::post('/music/options', [GameController::class, 'saveMusicOptions'])
            ->name('music.options');

        // * GAMES
        Route::post('/games/filtered', [GameController::class, 'getGamesFiltered'])
            ->name('games.filtered');

        // * STATIC PAGES
        Route::get('/', [StaticPageController::class, 'home'])
            ->name('games.index');
        Route::get('/ranking', [StaticPageController::class, 'ranking'])
            ->name('ranks.index');

        // * RATINGS
        Route::post('/ratings/{picture_id}/{picture_place}', [RatingController::class, 'update'])
            ->where('PICTUREID', '^[0-9]*$')
            ->where('PICTUREPLACE', '^[0-9]*$')
            ->name('ratings.update');

        // * CHANGE LANGUAGES.
        Route::post('/lang/set', [Controller::class, 'setLang'])->name('lang.set');
    });
