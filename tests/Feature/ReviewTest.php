<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $anotherUser;

    protected Movie $movie;

    protected TvShow $tvShow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Alice MovieGoer',
            'email' => 'alice@movie.test',
        ]);

        $this->anotherUser = User::factory()->create([
            'name' => 'Bob Reviewer',
            'email' => 'bob@movie.test',
        ]);

        $this->movie = Movie::create([
            'title' => 'Dune: Part Two',
            'slug' => 'dune-part-two',
            'overview' => 'Paul Atreides unites with Chani and the Fremen.',
            'poster_path' => '/dune.jpg',
            'release_date' => '2024-03-01',
            'vote_average' => 8.6,
        ]);

        $this->tvShow = TvShow::create([
            'title' => 'Shogun',
            'slug' => 'shogun',
            'overview' => 'Lord Toranaga battles for his life.',
            'first_air_date' => '2024-02-27',
            'vote_average' => 8.9,
            'number_of_seasons' => 1,
            'number_of_episodes' => 10,
        ]);
    }

    public function test_guest_cannot_submit_review(): void
    {
        $response = $this->postJson('/api/reviews', [
            'media_type' => 'movie',
            'media_id' => $this->movie->id,
            'rating' => 9,
            'content' => 'Outstanding cinematography and soundtrack!',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_user_can_submit_movie_review_with_rating_and_spoiler(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/reviews', [
            'media_type' => 'movie',
            'media_id' => $this->movie->id,
            'rating' => 10,
            'content' => 'Masterpiece from start to finish! Watch out for the sand worm battle.',
            'has_spoiler' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('review.rating', 10);
        $response->assertJsonPath('review.has_spoiler', true);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'reviewable_id' => $this->movie->id,
            'reviewable_type' => Movie::class,
            'rating' => 10,
            'has_spoiler' => true,
        ]);
    }

    public function test_user_can_submit_tv_show_review(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/reviews', [
            'media_type' => 'tv',
            'media_id' => $this->tvShow->id,
            'rating' => 9,
            'content' => 'One of the best miniseries of the decade.',
            'has_spoiler' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'reviewable_id' => $this->tvShow->id,
            'reviewable_type' => TvShow::class,
            'rating' => 9,
        ]);
    }

    public function test_user_can_toggle_like_on_a_review(): void
    {
        $review = Review::create([
            'user_id' => $this->anotherUser->id,
            'reviewable_id' => $this->movie->id,
            'reviewable_type' => Movie::class,
            'rating' => 9,
            'content' => 'Incredible visuals and sound design.',
            'likes_count' => 0,
        ]);

        // 1. Like the review
        $response = $this->actingAs($this->user)->postJson("/api/reviews/{$review->id}/like");
        $response->assertStatus(200);
        $response->assertJson(['liked' => true, 'likes_count' => 1]);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $review->id,
        ]);
        $this->assertEquals(1, $review->fresh()->likes_count);

        // 2. Unlike the review
        $response = $this->actingAs($this->user)->postJson("/api/reviews/{$review->id}/like");
        $response->assertStatus(200);
        $response->assertJson(['liked' => false, 'likes_count' => 0]);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $this->user->id,
            'review_id' => $review->id,
        ]);
        $this->assertEquals(0, $review->fresh()->likes_count);
    }

    public function test_user_can_delete_own_review(): void
    {
        $review = Review::create([
            'user_id' => $this->user->id,
            'reviewable_id' => $this->movie->id,
            'reviewable_type' => Movie::class,
            'rating' => 7,
            'content' => 'Good but a bit lengthy.',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/reviews/{$review->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $review = Review::create([
            'user_id' => $this->anotherUser->id,
            'reviewable_id' => $this->movie->id,
            'reviewable_type' => Movie::class,
            'rating' => 8,
            'content' => 'Great performance.',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/reviews/{$review->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
