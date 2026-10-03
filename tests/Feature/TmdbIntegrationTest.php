<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TmdbIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tmdb_service_extracts_youtube_official_trailer(): void
    {
        $service = new TmdbService;

        $videos = [
            [
                'site' => 'Vimeo',
                'key' => '12345',
                'type' => 'Trailer',
            ],
            [
                'site' => 'YouTube',
                'key' => 'clip123',
                'type' => 'Clip',
            ],
            [
                'site' => 'YouTube',
                'key' => 'official_trailer_key',
                'type' => 'Trailer',
                'official' => true,
            ],
            [
                'site' => 'YouTube',
                'key' => 'secondary_trailer_key',
                'type' => 'Trailer',
                'official' => false,
            ],
        ];

        $trailerUrl = $service->extractTrailerUrl($videos);

        $this->assertEquals('https://www.youtube.com/watch?v=official_trailer_key', $trailerUrl);
    }

    public function test_tmdb_service_formats_movie_data_correctly(): void
    {
        $service = new TmdbService;

        $sampleTmdbResponse = [
            'id' => 157336,
            'title' => 'Interstellar',
            'tagline' => 'Mankind was born on Earth. It was never meant to die here.',
            'overview' => 'The adventures of a group of explorers...',
            'poster_path' => '/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
            'backdrop_path' => '/xJHokMbljvjADYdit5fK5VQsXEG.jpg',
            'release_date' => '2014-11-05',
            'vote_average' => 8.4,
            'runtime' => 169,
            'budget' => 165000000,
            'revenue' => 701729206,
            'genres' => [
                ['id' => 12, 'name' => 'Adventure'],
                ['id' => 18, 'name' => 'Drama'],
                ['id' => 878, 'name' => 'Science Fiction'],
            ],
            'credits' => [
                'cast' => [
                    ['name' => 'Matthew McConaughey', 'character' => 'Cooper', 'profile_path' => '/eOK9U80ZqR.jpg'],
                    ['name' => 'Anne Hathaway', 'character' => 'Brand', 'profile_path' => '/tLel4NahQG.jpg'],
                ],
                'crew' => [
                    ['name' => 'Christopher Nolan', 'job' => 'Director'],
                ],
            ],
            'videos' => [
                'results' => [
                    [
                        'site' => 'YouTube',
                        'key' => 'zSWdZVtXT7E',
                        'type' => 'Trailer',
                        'official' => true,
                    ],
                ],
            ],
        ];

        $formatted = $service->formatMovieData($sampleTmdbResponse);

        $this->assertEquals('157336', $formatted['tmdb_id']);
        $this->assertEquals('Interstellar', $formatted['title']);
        $this->assertEquals('interstellar', $formatted['slug']);
        $this->assertEquals('Christopher Nolan', $formatted['director']);
        $this->assertEquals('169 min', $formatted['runtime']);
        $this->assertEquals('https://www.youtube.com/watch?v=zSWdZVtXT7E', $formatted['trailer_url']);
        $this->assertContains('Science Fiction', $formatted['genres']);
        $this->assertCount(2, $formatted['cast']);
        $this->assertCount(4, $formatted['stream_servers']);
    }

    public function test_tmdb_sync_command_handles_missing_api_key(): void
    {
        config(['services.tmdb.api_key' => null]);
        config(['services.tmdb.read_token' => null]);

        $this->artisan('tmdb:sync')
            ->expectsOutputToContain('TMDB API Key is not set.')
            ->assertExitCode(1);
    }

    public function test_admin_import_route_requires_authenticated_admin(): void
    {
        $response = $this->post('/admin/movies/import-tmdb', [
            'tmdb_id' => '157336',
        ]);

        $response->assertRedirect('/login');

        $regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($regularUser)->post('/admin/movies/import-tmdb', [
            'tmdb_id' => '157336',
        ]);

        $response->assertStatus(403);
    }

    public function test_live_search_endpoint_returns_valid_json(): void
    {
        $response = $this->getJson('/api/search/live?q=Inter');

        $response->assertStatus(200);
        $response->assertJsonStructure(['results']);
    }
}
