<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected Movie $movie;

    protected TvShow $tvShow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@movie.test',
            'role' => 'admin',
        ]);

        $this->user = User::factory()->create([
            'name' => 'John Reviewer',
            'email' => 'john@movie.test',
            'role' => 'user',
        ]);

        $this->movie = Movie::create([
            'title' => 'Inception',
            'slug' => 'inception',
            'overview' => 'A thief who steals corporate secrets through dreams.',
            'release_date' => '2010-07-16',
            'vote_average' => 8.8,
        ]);

        $this->tvShow = TvShow::create([
            'title' => 'Breaking Bad',
            'slug' => 'breaking-bad',
            'overview' => 'A chemistry teacher turned manufacturer.',
            'first_air_date' => '2008-01-20',
            'vote_average' => 9.5,
        ]);
    }

    public function test_guest_cannot_access_admin_reviews(): void
    {
        $response = $this->get('/admin/reviews');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_reviews(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/reviews');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_reviews_index(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'reviewable_id' => $this->movie->id,
            'reviewable_type' => Movie::class,
            'rating' => 9,
            'content' => 'Mind-bending cinematic masterpiece!',
            'has_spoiler' => false,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/reviews');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reviews/Index')
            ->has('reviews.data', 1)
            ->where('stats.total_reviews', 1)
            ->where('stats.avg_rating', 9)
        );
    }

    public function test_admin_can_filter_reviews_by_media_type_and_search(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'reviewable_id' => $this->movie->id,
            'reviewable_type' => Movie::class,
            'rating' => 9,
            'content' => 'Exceptional movie direction.',
            'has_spoiler' => false,
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'reviewable_id' => $this->tvShow->id,
            'reviewable_type' => TvShow::class,
            'rating' => 10,
            'content' => 'Legendary television series.',
            'has_spoiler' => true,
        ]);

        // Filter by TV shows only
        $response = $this->actingAs($this->admin)->get('/admin/reviews?type=tv');
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reviews/Index')
            ->has('reviews.data', 1)
            ->where('reviews.data.0.rating', 10)
        );

        // Search by content query
        $response = $this->actingAs($this->admin)->get('/admin/reviews?search=Exceptional');
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reviews/Index')
            ->has('reviews.data', 1)
            ->where('reviews.data.0.rating', 9)
        );
    }

    public function test_admin_can_delete_any_review(): void
    {
        $review = Review::create([
            'user_id' => $this->user->id,
            'reviewable_id' => $this->movie->id,
            'reviewable_type' => Movie::class,
            'rating' => 4,
            'content' => 'Spam or inappropriate comment here.',
            'has_spoiler' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/reviews/{$review->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }
}
