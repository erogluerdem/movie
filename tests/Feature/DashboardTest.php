<?php

namespace Tests\Feature;

use App\Models\ContentRequest;
use App\Models\Movie;
use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo-user@movie.app',
            'password' => Hash::make('password'),
            'role' => 'user',
            'preferred_quality' => '1080p',
            'preferred_language' => 'en',
            'autoplay_next' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard_overview(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_watchlist_page(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/watchlist');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_history_page(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/history');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_requests_page(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/requests');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_settings_page(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard/settings');
        $response->assertStatus(200);
    }

    public function test_user_can_update_profile(): void
    {
        $response = $this->actingAs($this->user)->post('/dashboard/profile', [
            'name' => 'Updated Movie Fan',
            'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop',
        ]);

        $response->assertSessionHas('success');
        $this->user->refresh();
        $this->assertEquals('Updated Movie Fan', $this->user->name);
    }

    public function test_user_can_update_streaming_preferences(): void
    {
        $response = $this->actingAs($this->user)->post('/dashboard/preferences', [
            'preferred_quality' => '4K',
            'preferred_language' => 'tr',
            'autoplay_next' => true,
        ]);

        $response->assertSessionHas('success');
        $this->user->refresh();
        $this->assertEquals('4K', $this->user->preferred_quality);
        $this->assertEquals('tr', $this->user->preferred_language);
        $this->assertTrue((bool) $this->user->autoplay_next);
    }

    public function test_user_can_submit_and_delete_content_request(): void
    {
        $response = $this->actingAs($this->user)->post('/dashboard/requests', [
            'title' => 'Interstellar 2',
            'type' => 'movie',
            'release_year' => '2026',
            'user_notes' => 'Please add as soon as available in 4K HDR',
        ]);

        $response->assertSessionHas('success');

        $req = ContentRequest::where('user_id', $this->user->id)
            ->where('title', 'Interstellar 2')
            ->first();

        $this->assertNotNull($req);
        $this->assertEquals('pending', $req->status);

        // Test delete
        $delResponse = $this->actingAs($this->user)->delete("/dashboard/requests/{$req->id}");
        $delResponse->assertSessionHas('success');
        $this->assertNull(ContentRequest::find($req->id));
    }

    public function test_watch_progress_tracking_api(): void
    {
        $movie = Movie::create([
            'tmdb_id' => 999999,
            'title' => 'Test Progress Movie',
            'slug' => 'test-progress-movie',
            'overview' => 'Overview here',
            'poster_path' => 'https://example.com/poster.jpg',
            'backdrop_path' => 'https://example.com/backdrop.jpg',
            'release_date' => '2025-01-01',
            'vote_average' => 8.5,
            'status' => 'Released',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/watch/progress', [
            'media_type' => 'movie',
            'media_id' => $movie->id,
            'progress_percent' => 75,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'saved']);

        $history = WatchHistory::where('user_id', $this->user->id)
            ->where('media_type', 'movie')
            ->where('media_id', $movie->id)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals(75, $history->progress_percent);
    }
}
