<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id()->comment('Id of the game.');
            $table->string('name')->comment('Name of the game.');
            $table->string('slug')->unique()->comment('Slugify the name of this game.');
            $table->string('picture')->comment('Picture associated to this game.');
            $table->string('music')->nullable()->comment('Path to the music file.');
            $table->foreignId('folder_id')->comment('Folder associated to the game.')
                ->constrained('folders')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('akora_id')->comment('Id of the game in Akora.');
            $table->integer('order')->comment('Order of this game.');
            $table->boolean('published')->comment('The game is published or not.');
            $table->timestamp('published_at')->nullable()->comment('The date on which the game was published.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
