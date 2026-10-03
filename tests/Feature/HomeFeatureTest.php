<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\SportMatch;
use App\Models\TvShow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Movie::create([
            'title' => 'Inception',
            'slug' => 'inception-2010',
            'overview' => 'A thief who steals corporate secrets through dream-sharing tech.',
            'release_date' => '2010-07-16',
            'vote_average' => 8.8,
            'poster_path' => '/poster1.jpg',
            'backdrop_path' => '/backdrop1.jpg',
            'is_trending' => true,
            'is_featured' => true,
            'genres' => ['Action', 'Sci-Fi'],
        ]);

        TvShow::create([
            'title' => 'Attack on Titan',
            'slug' => 'attack-on-titan',
            'overview' => 'Humans fight titans behind massive walls.',
            'first_air_date' => '2013-04-07',
            'vote_average' => 9.0,
            'poster_path' => '/aot_poster.jpg',
            'backdrop_path' => '/aot_backdrop.jpg',
            'is_anime' => true,
            'is_trending' => true,
            'genres' => ['Animation', 'Action'],
        ]);

        SportMatch::create([
            'title' => 'Real Madrid vs Barcelona',
            'slug' => 'real-madrid-vs-barcelona',
            'league' => 'La Liga',
            'team_home' => 'Real Madrid',
            'team_away' => 'Barcelona',
            'match_time' => '21:00',
            'status' => 'HD Kalite',
            'is_live' => true,
        ]);
    }

    public function test_home_page_renders_with_expected_props(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('heroMovies')
            ->has('trending')
            ->has('popularMovies')
            ->has('popularShows')
            ->has('animeList')
            ->has('liveSports')
            ->has('spotlightMovie')
        );
    }

    public function test_random_pick_api_returns_success_and_item(): void
    {
        $response = $this->getJson('/api/random-pick');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'overview',
                    'poster_url',
                    'backdrop_url',
                    'vote_average',
                    'release_year',
                    'genres',
                    'type',
                    'watch_url',
                ],
            ]);
    }

    public function test_random_pick_api_filters_by_movie(): void
    {
        $response = $this->getJson('/api/random-pick?type=movie');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'type' => 'movie',
                    'title' => 'Inception',
                ],
            ]);
    }

    public function test_random_pick_api_filters_by_tv(): void
    {
        $response = $this->getJson('/api/random-pick?type=tv');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'type' => 'tv',
                    'title' => 'Attack on Titan',
                ],
            ]);
    }
}
