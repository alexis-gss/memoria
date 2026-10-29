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
        Schema::create('telescope_entries', function (Blueprint $table): void {
            $table->bigIncrements('sequence')->comment('Telescope sequence.');
            $table->uuid('uuid')->unique()->comment('Telescope UUID.');
            $table->uuid('batch_id')->comment('Telescope batch id.');
            $table->string('family_hash')->nullable()->comment('Telescope family hash.');
            $table->string('type', 20)->comment('Telescope type.');
            $table->longText('content')->comment('Telescope content.');
            $table->boolean('should_display_on_index')->default(true)
                ->comment('Telescope should display on index status.');
            $table->dateTime('created_at')->nullable()->comment('Telescope created date.');

            $table->index('batch_id');
            $table->index('family_hash');
            $table->index('created_at');
            $table->index(['type', 'should_display_on_index']);
        });

        Schema::create('telescope_entries_tags', function (Blueprint $table): void {
            $table->uuid('entry_uuid')->comment('Telescope entry UUID.');
            $table->string('tag')->comment('Telescope entry tag.');

            $table->primary(['entry_uuid', 'tag']);
            $table->index('tag');
            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('telescope_entries')
                ->onDelete('cascade');
        });

        Schema::create('telescope_monitoring', function (Blueprint $table): void {
            $table->string('tag')->primary()->comment('Telescope monitoring tag.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telescope_entries_tags');
        Schema::dropIfExists('telescope_entries');
        Schema::dropIfExists('telescope_monitoring');
    }
};
