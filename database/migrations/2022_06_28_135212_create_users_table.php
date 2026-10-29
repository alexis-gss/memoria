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
        Schema::create('users', function (Blueprint $table): void {
            $table->id()->comment('Id of the user.');
            $table->string('first_name')->comment('First name of the user.');
            $table->string('last_name')->comment('Last name of the user.');
            $table->string('email')->unique()->comment('Email of the user that is unique.');
            $table->string('picture')->comment('Picture associated to this user.');
            $table->string('password')->comment('Encrypted password of the user.');
            $table->rememberToken();
            $table->unsignedTinyInteger('role')->comment('Role of the user who defines the rights/access.');
            $table->integer('order')->comment('Order of this user.');
            $table->boolean('published')->comment('The user is published or not.');
            $table->timestamp('published_at')->nullable()->comment('The date on which the user was published.');
            $table->timestamp('email_verified_at')->nullable()->comment('The date on which the email was verified.');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
