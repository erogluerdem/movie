<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    protected $fillable = [
        'season_id',
        'episode_number',
        'name',
        'overview',
        'still_path',
        'duration',
        'air_date',
        'stream_servers',
    ];

    protected $casts = [
        'stream_servers' => 'array',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function getStillUrlAttribute(): string
    {
        if (! $this->still_path) {
            return '/images/placeholder-still.jpg';
        }
        if (str_starts_with($this->still_path, 'http')) {
            return $this->still_path;
        }

        return 'https://image.tmdb.org/t/p/w500'.$this->still_path;
    }
}
