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
        Schema::create('taggables', function (Blueprint $table): void {
            $table->string('taggable_type', 60)->comment('Model associated.');
            $table->foreignId('taggable_id')->comment('Id of the associated model.')
                ->references('id')->on('games')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('tag_id')->comment('Id of the tag.')
                ->references('id')->on('tags')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
