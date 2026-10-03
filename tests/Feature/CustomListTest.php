<?php

namespace Tests\Feature;

use App\Models\CustomList;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_public_custom_lists_index(): void
    {
        $user = User::factory()->create();
        CustomList::create([
            'user_id' => $user->id,
            'title' => 'Top 10 Bilim Kurgu',
            'slug' => 'top-10-bilim-kurgu-abc123',
            'description' => 'Harika filmler',
            'is_public' => true,
        ]);

        $response = $this->get(route('lists.index'));

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_custom_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lists.store'), [
            'title' => 'Korku Gecesi',
            'description' => 'Gece izlenecekler',
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('custom_lists', [
            'user_id' => $user->id,
            'title' => 'Korku Gecesi',
        ]);
    }

    public function test_user_can_add_and_remove_movie_from_custom_list(): void
    {
        $user = User::factory()->create();
        $movie = Movie::create([
            'tmdb_id' => 999999,
            'title' => 'Test Inception',
            'slug' => 'test-inception',
            'release_date' => '2010-07-16',
            'vote_average' => 8.8,
        ]);

        $list = CustomList::create([
            'user_id' => $user->id,
            'title' => 'Favorilerim',
            'slug' => 'favorilerim-xyz999',
            'is_public' => true,
        ]);

        // Add movie
        $addResponse = $this->actingAs($user)->postJson(route('lists.items.add', $list->id), [
            'media_type' => 'movie',
            'media_id' => $movie->id,
        ]);

        $addResponse->assertStatus(200);
        $addResponse->assertJson(['status' => 'added']);
        $this->assertEquals(1, $list->fresh()->items_count);

        // Check item status
        $statusResponse = $this->actingAs($user)->getJson(route('api.custom-lists.status', [
            'media_type' => 'movie',
            'media_id' => $movie->id,
        ]));

        $statusResponse->assertStatus(200);
        $statusResponse->assertJsonFragment([
            'id' => $list->id,
            'contains' => true,
        ]);

        // Remove movie
        $removeResponse = $this->actingAs($user)->postJson(route('lists.items.remove', $list->id), [
            'media_type' => 'movie',
            'media_id' => $movie->id,
        ]);

        $removeResponse->assertStatus(200);
        $removeResponse->assertJson(['status' => 'removed']);
        $this->assertEquals(0, $list->fresh()->items_count);
    }

    public function test_guest_cannot_create_custom_list(): void
    {
        $response = $this->post(route('lists.store'), [
            'title' => 'Yetkisiz Liste',
        ]);

        $response->assertRedirect(route('login'));
    }
}
