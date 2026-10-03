<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'media_type',
        'media_id',
        'season_number',
        'episode_number',
        'progress_percent',
        'completed',
        'last_watched_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'progress_percent' => 'integer',
        'last_watched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'media_id');
    }

    public function tvShow(): BelongsTo
    {
        return $this->belongsTo(TvShow::class, 'media_id');
    }
}
