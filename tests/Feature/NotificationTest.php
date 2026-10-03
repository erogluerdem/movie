<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@movie.test',
        ]);

        $this->anotherUser = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@movie.test',
        ]);
    }

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(200);
        $response->assertJson(['notifications' => [], 'unread_count' => 0]);
    }

    public function test_user_can_view_their_notifications_and_unread_count(): void
    {
        AppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'New Episode Available',
            'message' => 'Solo Leveling Episode 12 is now streaming in HD!',
            'type' => 'content_ready',
            'link' => '/tv-show/solo-leveling',
            'is_read' => false,
        ]);

        AppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'Review Liked',
            'message' => 'Someone found your Dune review helpful.',
            'type' => 'like',
            'is_read' => true,
        ]);

        // Create another user's notification that should not appear
        AppNotification::create([
            'user_id' => $this->anotherUser->id,
            'title' => 'Private Alert',
            'message' => 'Should not be seen by John',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/notifications');
        $response->assertStatus(200);
        $response->assertJsonPath('unread_count', 1);
        $response->assertJsonCount(2, 'notifications');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $notification = AppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'System Update',
            'message' => 'Movie® v2 is now live.',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/notifications/{$notification->id}/read");
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'marked_read');

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $notification = AppNotification::create([
            'user_id' => $this->anotherUser->id,
            'title' => 'Private Notice',
            'message' => 'Secret alert',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/notifications/{$notification->id}/read");
        $response->assertStatus(403);
        $this->assertFalse($notification->fresh()->is_read);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        AppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'Alert 1',
            'message' => 'Notice 1',
            'is_read' => false,
        ]);

        AppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'Alert 2',
            'message' => 'Notice 2',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/notifications/read-all');
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'all_read');

        $unreadCount = AppNotification::where('user_id', $this->user->id)->where('is_read', false)->count();
        $this->assertEquals(0, $unreadCount);
    }
}
