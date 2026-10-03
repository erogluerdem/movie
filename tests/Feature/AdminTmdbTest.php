<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTmdbTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_tmdb(): void
    {
        $response = $this->get('/admin/tmdb');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_tmdb(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/tmdb');

        $response->assertForbidden();
    }

    public function test_admin_can_view_admin_tmdb_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/tmdb');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tmdb/Index')
            ->has('stats')
            ->has('tmdb_config')
            ->has('automation')
            ->has('recent_movies')
            ->has('recent_tv_shows')
        );
    }

    public function test_admin_can_update_automation_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/admin/tmdb/settings', [
            'tmdb_auto_sync_enabled' => true,
            'tmdb_auto_sync_frequency' => 'every_12_hours',
            'tmdb_auto_sync_pages' => 3,
            'tmdb_auto_sync_min_votes' => 60,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('every_12_hours', Setting::get('tmdb_auto_sync_frequency'));
        $this->assertEquals('3', Setting::get('tmdb_auto_sync_pages'));
        $this->assertEquals('60', Setting::get('tmdb_auto_sync_min_votes'));
    }

    public function test_admin_can_search_tmdb_titles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/admin/tmdb/search', [
            'query' => 'Interstellar',
            'media' => 'movie',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['results']);
    }
}
