<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'avatar', 'role', 'preferred_quality', 'preferred_language', 'autoplay_next', 'is_banned', 'banned_reason'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'autoplay_next' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }

    public function watchHistories(): HasMany
    {
        return $this->hasMany(WatchHistory::class)->orderByDesc('last_watched_at');
    }

    public function contentRequests(): HasMany
    {
        return $this->hasMany(ContentRequest::class)->orderByDesc('created_at');
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->orderByDesc('created_at');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class)->orderByDesc('created_at');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
