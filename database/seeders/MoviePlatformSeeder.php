<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\Season;
use App\Models\SportMatch;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class MoviePlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Developer / Primary Admin User
        User::firstOrCreate(
            ['email' => 'dev@erdemeroglu.com.tr'],
            [
                'name' => 'Erdem Eroğlu',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Demo Admin User
        User::firstOrCreate(
            ['email' => 'admin@movie.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Regular User
        User::firstOrCreate(
            ['email' => 'demo-user@movie.app'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('demo123'),
                'role' => 'user',
            ]
        );

        $dataPath = database_path('seeders/seed_data.json');
        if (! File::exists($dataPath)) {
            $this->command->error('seed_data.json not found!');

            return;
        }

        $data = json_decode(File::get($dataPath), true);

        // Seed Movies
        $this->command->info('Seeding enriched movies...');
        foreach ($data['movies'] ?? [] as $m) {
            Movie::updateOrCreate(
                ['slug' => $m['slug']],
                [
                    'tmdb_id' => $m['tmdb_id'],
                    'title' => $m['title'],
                    'tagline' => $m['tagline'] ?? null,
                    'overview' => $m['overview'],
                    'poster_path' => $m['poster_path'],
                    'backdrop_path' => $m['backdrop_path'],
                    'release_date' => $m['release_date'],
                    'vote_average' => $m['vote_average'],
                    'runtime' => $m['runtime'],
                    'director' => $m['director'] ?? null,
                    'budget' => $m['budget'] ?? null,
                    'revenue' => $m['revenue'] ?? null,
                    'tomato_percent' => $m['tomato_percent'] ?? null,
                    'trailer_url' => $m['trailer_url'],
                    'is_trending' => $m['is_trending'],
                    'is_featured' => $m['is_featured'],
                    'genres' => $m['genres'],
                    'cast' => $m['cast'],
                    'stream_servers' => $m['stream_servers'],
                ]
            );
        }

        // Seed TV Shows
        $this->command->info('Seeding enriched TV shows and episodes...');
        foreach ($data['tv_shows'] ?? [] as $t) {
            $tv = TvShow::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'tmdb_id' => $t['tmdb_id'],
                    'title' => $t['title'],
                    'tagline' => $t['tagline'] ?? null,
                    'overview' => $t['overview'],
                    'poster_path' => $t['poster_path'],
                    'backdrop_path' => $t['backdrop_path'],
                    'first_air_date' => $t['first_air_date'],
                    'vote_average' => $t['vote_average'],
                    'status' => $t['status'],
                    'number_of_seasons' => $t['number_of_seasons'],
                    'number_of_episodes' => $t['number_of_episodes'],
                    'tomato_percent' => $t['tomato_percent'] ?? null,
                    'is_trending' => $t['is_trending'],
                    'is_featured' => $t['is_featured'],
                    'is_anime' => $t['is_anime'],
                    'trailer_url' => $t['trailer_url'] ?? null,
                    'genres' => $t['genres'],
                    'cast' => $t['cast'],
                ]
            );

            // Seasons & Episodes
            foreach ($t['seasons'] ?? [] as $s) {
                $season = Season::updateOrCreate(
                    [
                        'tv_show_id' => $tv->id,
                        'season_number' => $s['season_number'],
                    ],
                    [
                        'name' => $s['name'],
                        'overview' => $s['overview'],
                        'poster_path' => $s['poster_path'],
                    ]
                );

                foreach ($s['episodes'] ?? [] as $ep) {
                    Episode::updateOrCreate(
                        [
                            'season_id' => $season->id,
                            'episode_number' => $ep['episode_number'],
                        ],
                        [
                            'name' => $ep['name'],
                            'overview' => $ep['overview'],
                            'still_path' => $ep['still_path'],
                            'duration' => $ep['duration'],
                            'air_date' => $ep['air_date'],
                            'stream_servers' => $ep['stream_servers'],
                        ]
                    );
                }
            }
        }

        // Seed Sports
        $this->command->info('Seeding sports matches...');
        foreach ($data['sports'] ?? [] as $sp) {
            SportMatch::updateOrCreate(
                ['slug' => $sp['slug']],
                [
                    'title' => $sp['title'],
                    'league' => $sp['league'],
                    'team_home' => $sp['team_home'],
                    'team_away' => $sp['team_away'],
                    'home_logo' => $sp['home_logo'],
                    'away_logo' => $sp['away_logo'],
                    'match_time' => $sp['match_time'],
                    'status' => $sp['status'],
                    'is_live' => $sp['is_live'],
                    'stream_url' => $sp['stream_url'],
                    'stream_servers' => $sp['stream_servers'],
                ]
            );
        }

        $this->command->info('Successfully seeded enriched Movie® data!');
    }
}
