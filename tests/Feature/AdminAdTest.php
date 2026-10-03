<?php

namespace Tests\Feature;

use App\Models\AdPlacement;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAdTest extends TestCase
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

        AdPlacement::seedDefaults();
    }

    public function test_guest_cannot_access_ad_management(): void
    {
        $this->get('/admin/ads')->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_ad_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/ads');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_ad_management_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/ads');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Ads/Index')
            ->has('placements', 7)
            ->has('settings.ads_enabled')
            ->has('settings.adblock_detector_enabled')
        );
    }

    public function test_admin_can_update_ad_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->post('/admin/ads/settings', [
            'ads_enabled' => true,
            'adblock_detector_enabled' => true,
            'adblock_strict_mode' => true,
            'adblock_title' => 'Lütfen Reklam Engelleyiciyi Kapatın',
            'adblock_message' => 'Movie® platformu için lütfen reklam engelleyicinizi devre dışı bırakın.',
            'global_ad_client' => 'ca-pub-1234567890123456',
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('1', Setting::get('ads_enabled'));
        $this->assertEquals('1', Setting::get('adblock_detector_enabled'));
        $this->assertEquals('1', Setting::get('adblock_strict_mode'));
        $this->assertEquals('Lütfen Reklam Engelleyiciyi Kapatın', Setting::get('adblock_title'));
        $this->assertEquals('ca-pub-1234567890123456', Setting::get('global_ad_client'));
    }

    public function test_admin_can_update_ad_placement(): void
    {
        $placement = AdPlacement::where('slug', 'player_top')->first();
        $this->assertNotNull($placement);

        $response = $this->actingAs($this->adminUser)->post("/admin/ads/placements/{$placement->id}", [
            'name' => 'Player Top Mega Banner',
            'description' => 'Updated description for testing',
            'type' => 'adsense',
            'is_active' => true,
            'ad_client' => 'ca-pub-9999888877776666',
            'ad_slot' => '9876543210',
            'device_target' => 'desktop',
            'frequency_minutes' => 15,
        ]);

        $response->assertSessionHas('success');

        $placement->refresh();
        $this->assertEquals('Player Top Mega Banner', $placement->name);
        $this->assertTrue($placement->is_active);
        $this->assertEquals('ca-pub-9999888877776666', $placement->ad_client);
        $this->assertEquals('9876543210', $placement->ad_slot);
        $this->assertEquals('desktop', $placement->device_target);
        $this->assertEquals(15, $placement->frequency_minutes);
    }

    public function test_admin_can_reset_default_placements(): void
    {
        AdPlacement::query()->delete();
        $this->assertEquals(0, AdPlacement::count());

        $response = $this->actingAs($this->adminUser)->post('/admin/ads/reset');
        $response->assertSessionHas('success');

        $this->assertGreaterThanOrEqual(7, AdPlacement::count());
    }

    public function test_inertia_shares_ads_payload_on_front_pages(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('ads')
            ->has('ads.enabled')
            ->has('ads.adblock')
            ->has('ads.placements')
        );
    }
}
