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
        Schema::create('static_pages', function (Blueprint $table): void {
            $table->id()->comment('Id of the static page.');
            $table->json('seo_title')->comment('Seo title of the static page.');
            $table->json('seo_description')->comment('Seo description of the static page.');
            $table->json('title')->comment('Title of the static page.');
            $table->unsignedTinyInteger('type')->unique()->comment('Type of the static page.');
            $table->integer('order')->comment('Order of the static page.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_pages');
    }
};
