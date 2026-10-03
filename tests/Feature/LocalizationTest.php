<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_turkish(): void
    {
        $this->assertEquals('tr', config('app.locale'));
        $response = $this->get('/');

        $response->assertOk();
        $this->assertEquals('tr', app()->getLocale());
    }

    public function test_user_can_switch_locale_via_post(): void
    {
        $response = $this->post(route('locale.switch'), [
            'locale' => 'en',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('locale', 'en');
    }

    public function test_authenticated_user_preferred_language_is_updated_on_switch(): void
    {
        $user = User::factory()->create([
            'preferred_language' => 'tr',
        ]);

        $response = $this->actingAs($user)->post(route('locale.switch'), [
            'locale' => 'en',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $this->assertEquals('en', $user->fresh()->preferred_language);
    }

    public function test_cannot_switch_to_unsupported_locale_via_post(): void
    {
        $response = $this->post(route('locale.switch'), [
            'locale' => 'fr',
        ]);

        $response->assertSessionHasErrors('locale');
    }

    public function test_user_can_switch_locale_via_get_route(): void
    {
        $response = $this->get(route('locale.switch.get', ['locale' => 'en']));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('locale', 'en');
    }

    public function test_invalid_locale_in_get_route_falls_back_to_turkish(): void
    {
        $response = $this->get(route('locale.switch.get', ['locale' => 'de']));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'tr');
        $response->assertCookie('locale', 'tr');
    }

    public function test_inertia_shares_active_locale_props(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'en')
            ->has('locales.tr')
            ->has('locales.en')
        );
    }
}
