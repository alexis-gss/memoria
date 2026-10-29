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
        Schema::create('ratings', function (Blueprint $table): void {
            $table->id()->comment('Id of the rating.');
            $table->string('uuid')->comment('Universally Unique Identifier (UUID) of the customer.');
            $table->foreignId('picture_id')->comment('Picture associated to the rating.')
                ->constrained('pictures')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
