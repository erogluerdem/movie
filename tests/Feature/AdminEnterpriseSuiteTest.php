<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CuratedCollection;
use App\Models\Movie;
use App\Models\StreamReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEnterpriseSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
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

    public function test_guest_cannot_access_new_admin_routes(): void
    {
        $this->get('/admin/analytics')->assertRedirect('/login');
        $this->get('/admin/tmdb/bulk')->assertRedirect('/login');
        $this->get('/admin/announcements')->assertRedirect('/login');
        $this->get('/admin/logs')->assertRedirect('/login');
        $this->get('/admin/reports')->assertRedirect('/login');
        $this->get('/admin/collections')->assertRedirect('/login');
        $this->get('/admin/backup/download')->assertRedirect('/login');
    }

    public function test_admin_can_access_streaming_analytics(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/analytics?days=14');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Analytics/Index')
            ->has('stats')
            ->has('chart')
            ->has('topMovies')
            ->has('topShows')
        );
    }

    public function test_admin_can_view_tmdb_bulk_feed_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/tmdb/bulk?feed=popular&type=movie');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Tmdb/Bulk')
            ->has('items')
            ->has('feed')
            ->has('type')
        );
    }

    public function test_admin_can_update_announcement_banner(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/announcements/banner', [
            'enabled' => true,
            'text' => 'Test global duyurusu yayında!',
            'type' => 'warning',
            'link' => '/movies',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('site_settings', [
            'key' => 'announcement_text',
            'value' => 'Test global duyurusu yayında!',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update_announcement',
        ]);
    }

    public function test_admin_can_broadcast_notifications_to_all_users(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/announcements/broadcast', [
            'target' => 'all',
            'type' => 'release',
            'title' => 'Yeni Film Yayında',
            'body' => 'Dune 2 şimdi Movie® üzerinde!',
            'link' => '/movies',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->regularUser->id,
            'title' => 'Yeni Film Yayında',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'broadcast_notification',
        ]);
    }

    public function test_public_user_can_submit_stream_report(): void
    {
        $movie = Movie::create([
            'tmdb_id' => 99999,
            'title' => 'Test Broken Movie',
            'slug' => 'test-broken-movie',
        ]);

        $response = $this->actingAs($this->regularUser)->postJson('/api/reports', [
            'media_type' => 'movie',
            'media_id' => $movie->id,
            'server_name' => 'VidCloud HD',
            'issue_type' => 'dead_link',
            'notes' => 'Video başlatılamıyor siyah ekran kalıyor',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('stream_reports', [
            'media_type' => 'movie',
            'media_id' => $movie->id,
            'issue_type' => 'dead_link',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_moderate_stream_reports(): void
    {
        $report = StreamReport::create([
            'user_id' => $this->regularUser->id,
            'media_type' => 'movie',
            'media_id' => 1,
            'server_name' => 'Server 1',
            'issue_type' => 'buffering',
            'status' => 'pending',
        ]);

        $indexResponse = $this->actingAs($this->adminUser)->get('/admin/reports');
        $indexResponse->assertStatus(200);
        $indexResponse->assertInertia(fn ($page) => $page
            ->component('Admin/Reports/Index')
            ->has('reports')
            ->has('statusCounts')
        );

        $updateResponse = $this->actingAs($this->adminUser)->post("/admin/reports/{$report->id}/status", [
            'status' => 'resolved',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('stream_reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);
    }

    public function test_admin_can_manage_curated_collections(): void
    {
        $movie = Movie::create([
            'tmdb_id' => 88888,
            'title' => 'Collection Movie',
            'slug' => 'collection-movie',
        ]);

        // Create collection
        $createResponse = $this->actingAs($this->adminUser)->post('/admin/collections', [
            'title' => 'Marvel Sinematik Evreni',
            'description' => 'Tüm MCU filmleri kronolojik sırada',
            'is_featured' => true,
            'order' => 1,
        ]);

        $createResponse->assertRedirect();
        $collection = CuratedCollection::where('title', 'Marvel Sinematik Evreni')->firstOrFail();
        $this->assertEquals('marvel-sinematik-evreni', $collection->slug);

        // Add item
        $addItemResponse = $this->actingAs($this->adminUser)->post("/admin/collections/{$collection->id}/items", [
            'type' => 'movie',
            'id' => $movie->id,
        ]);

        $addItemResponse->assertRedirect();
        $this->assertDatabaseHas('curated_collection_items', [
            'curated_collection_id' => $collection->id,
            'collectible_type' => Movie::class,
            'collectible_id' => $movie->id,
        ]);
    }

    public function test_admin_can_view_audit_logs(): void
    {
        AuditLog::log('manual_test', 'Testing audit logs functionality', null, $this->adminUser->id);

        $response = $this->actingAs($this->adminUser)->get('/admin/logs');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Logs/Index')
            ->has('logs')
            ->has('actions')
        );
    }

    public function test_admin_can_toggle_user_ban_status(): void
    {
        $this->assertFalse($this->regularUser->is_banned);

        // Ban user
        $banResponse = $this->actingAs($this->adminUser)->post("/admin/users/{$this->regularUser->id}/toggle-ban", [
            'reason' => 'Spam şikayeti nedeniyle yasaklandı',
        ]);

        $banResponse->assertRedirect();
        $this->regularUser->refresh();
        $this->assertTrue($this->regularUser->is_banned);
        $this->assertEquals('Spam şikayeti nedeniyle yasaklandı', $this->regularUser->banned_reason);

        // Unban user
        $unbanResponse = $this->actingAs($this->adminUser)->post("/admin/users/{$this->regularUser->id}/toggle-ban");
        $unbanResponse->assertRedirect();
        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->is_banned);
        $this->assertNull($this->regularUser->banned_reason);
    }

    public function test_admin_can_download_database_backup(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/backup/download');
        $response->assertStatus(200);
        $this->assertStringContainsString('.sqlite', $response->headers->get('content-disposition'));
    }
}
