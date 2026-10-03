<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreamReport extends Model
{
    protected $fillable = [
        'user_id',
        'media_type',
        'media_id',
        'server_name',
        'issue_type',
        'notes',
        'status',
    ];

    protected $appends = [
        'media',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMediaAttribute(): ?Model
    {
        if ($this->media_type === 'movie') {
            return Movie::find($this->media_id);
        } elseif ($this->media_type === 'tv') {
            return TvShow::find($this->media_id);
        } elseif ($this->media_type === 'sport') {
            return SportMatch::find($this->media_id);
        }

        return null;
    }
}
