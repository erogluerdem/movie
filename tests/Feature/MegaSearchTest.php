<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\SportMatch;
use App\Models\TvShow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MegaSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Movie::create([
            'title' => 'Interstellar',
            'slug' => 'interstellar-2014',
            'overview' => 'A team of explorers travel through a wormhole in space.',
            'release_date' => '2014-11-07',
            'vote_average' => 8.7,
            'poster_path' => '/interstellar.jpg',
            'genres' => ['Sci-Fi', 'Adventure'],
        ]);

        Movie::create([
            'title' => 'Average Movie',
            'slug' => 'average-movie',
            'overview' => 'An ordinary movie.',
            'release_date' => '2022-01-01',
            'vote_average' => 5.5,
            'poster_path' => '/average.jpg',
            'genres' => ['Comedy'],
        ]);

        TvShow::create([
            'title' => 'Death Note',
            'slug' => 'death-note',
            'overview' => 'A high school student discovers a supernatural notebook.',
            'first_air_date' => '2006-10-04',
            'vote_average' => 9.0,
            'poster_path' => '/deathnote.jpg',
            'is_anime' => true,
            'genres' => ['Animation', 'Mystery'],
        ]);

        SportMatch::create([
            'title' => 'Real Madrid vs Liverpool',
            'slug' => 'real-madrid-vs-liverpool',
            'league' => 'Champions League',
            'team_home' => 'Real Madrid',
            'team_away' => 'Liverpool',
            'match_time' => '22:00',
            'status' => 'HD Kalite',
            'is_live' => true,
        ]);
    }

    public function test_mega_search_empty_query_returns_discovery_data(): void
    {
        $response = $this->getJson('/api/search/mega');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_empty_query' => true,
            ])
            ->assertJsonStructure([
                'status',
                'is_empty_query',
                'top_picks',
                'live_sports',
                'trending_keywords',
                'genres',
            ]);
    }

    public function test_mega_search_finds_matching_movie(): void
    {
        $response = $this->getJson('/api/search/mega?q=Interstellar');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_empty_query' => false,
            ]);

        $movies = $response->json('movies');
        $this->assertNotEmpty($movies);
        $this->assertEquals('Interstellar', $movies[0]['title']);
    }

    public function test_mega_search_finds_matching_tv_show_and_anime(): void
    {
        $response = $this->getJson('/api/search/mega?q=Death+Note&type=anime');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $shows = $response->json('shows');
        $this->assertNotEmpty($shows);
        $this->assertEquals('Death Note', $shows[0]['title']);
        $this->assertEquals('anime', $shows[0]['type']);
    }

    public function test_mega_search_filters_by_rating(): void
    {
        // Interstellar has rating 8.7
        $responseMatch = $this->getJson('/api/search/mega?q=Interstellar&min_rating=8.0');
        $responseMatch->assertStatus(200);
        $this->assertNotEmpty($responseMatch->json('movies'));

        // Rating above 9.5 should exclude Interstellar
        $responseNoMatch = $this->getJson('/api/search/mega?q=Interstellar&min_rating=9.5');
        $responseNoMatch->assertStatus(200);
        $this->assertEmpty($responseNoMatch->json('movies'));
    }

    public function test_mega_search_finds_sports_matches(): void
    {
        $response = $this->getJson('/api/search/mega?q=Madrid&type=sports');

        $response->assertStatus(200);
        $sports = $response->json('sports');
        $this->assertNotEmpty($sports);
        $this->assertEquals('Real Madrid vs Liverpool', $sports[0]['title']);
    }
}
