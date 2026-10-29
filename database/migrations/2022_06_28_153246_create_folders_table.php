<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table): void {
            $table->id()->comment('Id of the folder.');
            $table->string('name')->comment('Name of the folder.');
            $table->string('slug')->unique()->comment('Slugify the name of the folder.');
            $table->unsignedBigInteger('color')->comment('Color of the folder.');
            $table->integer('order')->comment('Order of this folder.');
            $table->boolean('mandatory')->comment('The folder is a mandatory folder or not.');
            $table->boolean('published')->comment('The folder is published or not.');
            $table->timestamp('published_at')->nullable()->comment('The date on which the folder was published.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
