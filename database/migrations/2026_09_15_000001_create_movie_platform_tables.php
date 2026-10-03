<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('tmdb_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->string('release_date')->nullable();
            $table->decimal('vote_average', 3, 1)->default(0.0);
            $table->string('runtime')->nullable();
            $table->string('director')->nullable();
            $table->string('budget')->nullable();
            $table->string('revenue')->nullable();
            $table->string('tomato_percent')->nullable();
            $table->string('trailer_url')->nullable();
            $table->boolean('is_trending')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->json('genres')->nullable();
            $table->json('cast')->nullable();
            $table->json('stream_servers')->nullable();
            $table->timestamps();
        });

        Schema::create('tv_shows', function (Blueprint $table) {
            $table->id();
            $table->string('tmdb_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->string('first_air_date')->nullable();
            $table->decimal('vote_average', 3, 1)->default(0.0);
            $table->string('status')->default('Ended');
            $table->integer('number_of_seasons')->default(1);
            $table->integer('number_of_episodes')->default(1);
            $table->string('tomato_percent')->nullable();
            $table->boolean('is_trending')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_anime')->default(false)->index();
            $table->string('trailer_url')->nullable();
            $table->json('genres')->nullable();
            $table->json('cast')->nullable();
            $table->timestamps();
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_show_id')->constrained('tv_shows')->cascadeOnDelete();
            $table->integer('season_number');
            $table->string('name')->nullable();
            $table->text('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->timestamps();
        });

        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
            $table->integer('episode_number');
            $table->string('name');
            $table->text('overview')->nullable();
            $table->string('still_path')->nullable();
            $table->string('duration')->nullable();
            $table->string('air_date')->nullable();
            $table->json('stream_servers')->nullable();
            $table->timestamps();
        });

        Schema::create('sports_matches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('league')->nullable();
            $table->string('team_home')->nullable();
            $table->string('team_away')->nullable();
            $table->string('home_logo')->nullable();
            $table->string('away_logo')->nullable();
            $table->string('match_time')->nullable();
            $table->string('status')->default('Upcoming');
            $table->boolean('is_live')->default(false)->index();
            $table->string('stream_url')->nullable();
            $table->json('stream_servers')->nullable();
            $table->timestamps();
        });

        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('media_type');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();
            $table->unique(['user_id', 'media_type', 'media_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('sports_matches');
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('tv_shows');
        Schema::dropIfExists('movies');
    }
};
