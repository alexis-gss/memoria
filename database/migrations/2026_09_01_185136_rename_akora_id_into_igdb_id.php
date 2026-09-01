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
        Schema::table('games', function (Blueprint $table) {
            $table->bigInteger('akora_id')->comment('Id of the game in IGDB.')->change();
            $table->renameColumn('akora_id', 'igdb_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->bigInteger('igdb_id')->comment('Id of the game in IGDB.')->change();
            $table->renameColumn('igdb_id', 'akora_id');
        });
    }
};
