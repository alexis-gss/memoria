<?php

namespace Database\Seeders;

use App\Lib\Helpers\FileStorageHelper;
use App\Models\Game;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Seeder for taggables table.
        $tags = Tag::query()->get();
        Game::factory(24)->make()->each(function (Game $gameModel, $key) use ($tags) {
            $gameModel->picture = FileStorageHelper::storeFile(
                $gameModel,
                new \SplFileInfo(\resource_path(
                    '../database/factories/assets/games/default-picture.png'
                ))
            );
            $gameModel->music   = FileStorageHelper::storeFile(
                $gameModel,
                new \SplFileInfo(\resource_path(
                    '../database/factories/assets/games/default-music.mp3'
                ))
            );
            $gameModel->order   = $key + 1;
            $gameModel->saveQuietly();

            $offset = rand(0, 15);
            $length = rand(1, 2);
            $gameModel->tags()->saveMany($tags->slice($offset, $length));
        });
    }
}
