<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Movie extends Model
{
    protected $fillable = [
        'tmdb_id',
        'title',
        'slug',
        'tagline',
        'overview',
        'poster_path',
        'backdrop_path',
        'release_date',
        'vote_average',
        'runtime',
        'director',
        'budget',
        'revenue',
        'tomato_percent',
        'trailer_url',
        'is_trending',
        'is_featured',
        'genres',
        'cast',
        'stream_servers',
    ];

    protected $casts = [
        'genres' => 'array',
        'cast' => 'array',
        'stream_servers' => 'array',
        'is_trending' => 'boolean',
        'is_featured' => 'boolean',
        'vote_average' => 'float',
    ];

    protected $appends = [
        'poster_url',
        'backdrop_url',
    ];

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable')->orderByDesc('created_at');
    }

    public function getPosterUrlAttribute(): string
    {
        if (! $this->poster_path) {
            return '/images/placeholder-poster.jpg';
        }
        if (str_starts_with($this->poster_path, 'http')) {
            return $this->poster_path;
        }

        return 'https://image.tmdb.org/t/p/w500'.(str_starts_with($this->poster_path, '/') ? '' : '/').$this->poster_path;
    }

    public function getBackdropUrlAttribute(): string
    {
        if (! $this->backdrop_path) {
            return $this->poster_url;
        }
        if (str_starts_with($this->backdrop_path, 'http')) {
            return $this->backdrop_path;
        }

        return 'https://image.tmdb.org/t/p/w1280'.(str_starts_with($this->backdrop_path, '/') ? '' : '/').$this->backdrop_path;
    }
}
