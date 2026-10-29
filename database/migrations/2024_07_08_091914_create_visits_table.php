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
        Schema::create('visits', function (Blueprint $table): void {
            $table->id()->comment('Id of the visit.');
            $table->string('uuid')->comment('Universally Unique Identifier (UUID) of the customer.');
            $table->foreignId('game_id')->comment('Game associated to the counter of visits.')
                ->constrained('games')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
