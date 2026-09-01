<?php

use App\Http\Controllers\Api\IgdbController;
use Illuminate\Support\Facades\Route;

Route::post('/games/igdb-url', [IgdbController::class, 'url'])->name('api.games.igdb-url');
