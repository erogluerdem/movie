<?php

namespace Tests\Feature;

use App\Models\ContentRequest;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Season;
use App\Models\Setting;
use App\Models\SportMatch;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@movie.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@movie.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }

    public function test_guest_cannot_access_admin_panel(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_gets_403_forbidden(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_overview(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_manage_movies(): void
    {
        // 1. View movies list
        $response = $this->actingAs($this->adminUser)->get('/admin/movies');
        $response->assertStatus(200);

        // 2. Create movie
        $createResponse = $this->actingAs($this->adminUser)->post('/admin/movies', [
            'title' => 'Gladiator II',
            'overview' => 'Years after witnessing the death of Maximus...',
            'release_date' => '2024-11-22',
            'vote_average' => 7.8,
            'runtime' => '148 min',
            'is_trending' => true,
            'is_featured' => false,
            'stream_servers' => [
                ['server_name' => 'VidCloud 4K', 'embed_url' => 'https://vidsrc.to/embed/movie/558449', 'quality' => '4K'],
            ],
        ]);
        $createResponse->assertSessionHas('success');

        $movie = Movie::where('title', 'Gladiator II')->first();
        $this->assertNotNull($movie);
        $this->assertTrue((bool) $movie->is_trending);

        // 3. Toggle trending
        $toggleResponse = $this->actingAs($this->adminUser)->post("/admin/movies/{$movie->id}/toggle-trending");
        $toggleResponse->assertSessionHas('success');
        $movie->refresh();
        $this->assertFalse((bool) $movie->is_trending);

        // 4. Update movie
        $updateResponse = $this->actingAs($this->adminUser)->put("/admin/movies/{$movie->id}", [
            'title' => 'Gladiator II (Updated)',
            'slug' => $movie->slug,
            'vote_average' => 8.2,
            'is_trending' => false,
            'is_featured' => true,
        ]);
        $updateResponse->assertSessionHas('success');
        $movie->refresh();
        $this->assertEquals('Gladiator II (Updated)', $movie->title);

        // 5. Delete movie
        $delResponse = $this->actingAs($this->adminUser)->delete("/admin/movies/{$movie->id}");
        $delResponse->assertSessionHas('success');
        $this->assertNull(Movie::find($movie->id));
    }

    public function test_admin_can_manage_tv_shows_and_episodes(): void
    {
        // 1. View TV shows list
        $response = $this->actingAs($this->adminUser)->get('/admin/tv-shows');
        $response->assertStatus(200);

        // 2. Create show
        $createResponse = $this->actingAs($this->adminUser)->post('/admin/tv-shows', [
            'title' => 'Arcane',
            'overview' => 'Set in the utopian region of Piltover...',
            'first_air_date' => '2021-11-06',
            'vote_average' => 9.0,
            'is_anime' => true,
            'is_trending' => true,
            'is_featured' => false,
        ]);
        $createResponse->assertSessionHas('success');

        $show = TvShow::where('title', 'Arcane')->first();
        $this->assertNotNull($show);
        $this->assertTrue((bool) $show->is_anime);

        // Season 1 was automatically initialized
        $season = Season::where('tv_show_id', $show->id)->first();
        $this->assertNotNull($season);

        // 3. Show details / episodes view
        $episodesViewResponse = $this->actingAs($this->adminUser)->get("/admin/tv-shows/{$show->id}");
        $episodesViewResponse->assertStatus(200);

        // 4. Add Episode
        $epResponse = $this->actingAs($this->adminUser)->post("/admin/seasons/{$season->id}/episodes", [
            'episode_number' => 1,
            'name' => 'Welcome to the Playground',
            'overview' => 'Orphaned sisters Vi and Powder...',
            'duration' => '43m',
            'stream_servers' => [
                ['server_name' => 'VidCloud 4K', 'embed_url' => 'https://vidsrc.to/embed/tv/arcane/1/1', 'quality' => '4K'],
            ],
        ]);
        $epResponse->assertSessionHas('success');

        $episode = Episode::where('season_id', $season->id)->where('episode_number', 1)->first();
        $this->assertNotNull($episode);
        $this->assertEquals('Welcome to the Playground', $episode->name);

        // 5. Delete Show
        $delResponse = $this->actingAs($this->adminUser)->delete("/admin/tv-shows/{$show->id}");
        $delResponse->assertSessionHas('success');
        $this->assertNull(TvShow::find($show->id));
    }

    public function test_admin_can_manage_live_sports(): void
    {
        // 1. View sports
        $response = $this->actingAs($this->adminUser)->get('/admin/sports');
        $response->assertStatus(200);

        // 2. Create match
        $createResponse = $this->actingAs($this->adminUser)->post('/admin/sports', [
            'title' => 'El Clasico: Real Madrid vs Barcelona',
            'league' => 'La Liga',
            'team_home' => 'Real Madrid',
            'team_away' => 'Barcelona',
            'match_time' => 'Today 21:00',
            'is_live' => false,
            'status' => 'Upcoming',
        ]);
        $createResponse->assertSessionHas('success');

        $match = SportMatch::where('team_home', 'Real Madrid')->first();
        $this->assertNotNull($match);

        // 3. Toggle live
        $toggleResponse = $this->actingAs($this->adminUser)->post("/admin/sports/{$match->id}/toggle-live");
        $toggleResponse->assertSessionHas('success');
        $match->refresh();
        $this->assertTrue((bool) $match->is_live);
    }

    public function test_admin_can_manage_content_requests(): void
    {
        $req = ContentRequest::create([
            'user_id' => $this->regularUser->id,
            'title' => 'Inception 2',
            'type' => 'movie',
            'status' => 'pending',
            'user_notes' => 'Looking forward to this!',
        ]);

        // 1. View requests
        $response = $this->actingAs($this->adminUser)->get('/admin/requests');
        $response->assertStatus(200);

        // 2. Update status and note
        $updateResponse = $this->actingAs($this->adminUser)->put("/admin/requests/{$req->id}", [
            'status' => 'available',
            'admin_notes' => 'Now available in 4K HDR!',
        ]);
        $updateResponse->assertSessionHas('success');

        $req->refresh();
        $this->assertEquals('available', $req->status);
        $this->assertEquals('Now available in 4K HDR!', $req->admin_notes);
    }

    public function test_admin_can_manage_users(): void
    {
        // 1. View users
        $response = $this->actingAs($this->adminUser)->get('/admin/users');
        $response->assertStatus(200);

        // 2. Toggle role (promote regular user to admin)
        $toggleResponse = $this->actingAs($this->adminUser)->post("/admin/users/{$this->regularUser->id}/toggle-role");
        $toggleResponse->assertSessionHas('success');
        $this->regularUser->refresh();
        $this->assertEquals('admin', $this->regularUser->role);

        // 3. Demote back to user
        $toggleResponse2 = $this->actingAs($this->adminUser)->post("/admin/users/{$this->regularUser->id}/toggle-role");
        $toggleResponse2->assertSessionHas('success');
        $this->regularUser->refresh();
        $this->assertEquals('user', $this->regularUser->role);

        // 4. Cannot demote self
        $selfToggle = $this->actingAs($this->adminUser)->post("/admin/users/{$this->adminUser->id}/toggle-role");
        $selfToggle->assertSessionHas('error');
    }

    public function test_admin_can_update_site_settings(): void
    {
        // 1. View settings
        $response = $this->actingAs($this->adminUser)->get('/admin/settings');
        $response->assertStatus(200);

        // 2. Update settings
        $saveResponse = $this->actingAs($this->adminUser)->post('/admin/settings', [
            'site_name' => 'Movie® Pro',
            'site_tagline' => 'The Ultimate Streaming Platform',
            'announcement_enabled' => true,
            'announcement_text' => 'Upgrade complete! All servers now support 4K 60FPS.',
            'tmdb_api_key' => 'sample_api_key_123',
            'default_player_server' => 'VidCloud Ultra',
            'maintenance_mode' => false,
        ]);
        $saveResponse->assertSessionHas('success');

        $this->assertEquals('Movie® Pro', Setting::get('site_name'));
        $this->assertEquals('1', Setting::get('announcement_enabled'));
        $this->assertEquals('VidCloud Ultra', Setting::get('default_player_server'));
    }

    public function test_admin_can_clear_cache(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/clear-cache');
        $response->assertSessionHas('success');
    }
}
