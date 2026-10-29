<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Lib\Helpers\FileStorageHelper;
use App\Models\User;
use Illuminate\Database\Seeder;
use SplFileInfo;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(5)->make()->each(function (User $userModel, int $key): void {
            $userModel->picture = FileStorageHelper::storeFile(
                $userModel,
                new SplFileInfo(\resource_path(
                    '../database/factories/assets/users/default-picture.png'
                ))
            );
            $userModel->order   = $key + 1;
            $userModel->saveQuietly();
        });
    }
}
