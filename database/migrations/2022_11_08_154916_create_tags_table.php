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
        Schema::create('tags', function (Blueprint $table): void {
            $table->id()->comment('Id of the tag.');
            $table->json('name')->comment('Name of the tag.');
            $table->string('slug')->unique()->comment('Slugify the name of the tag.');
            $table->integer('order')->comment('Order of this tag.');
            $table->boolean('published')->comment('The tag is published or not');
            $table->timestamp('published_at')->nullable()->comment('The date on which the tag was published.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
