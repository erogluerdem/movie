<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TvShow extends Model
{
    protected $fillable = [
        'tmdb_id',
        'title',
        'slug',
        'tagline',
        'overview',
        'poster_path',
        'backdrop_path',
        'first_air_date',
        'vote_average',
        'status',
        'number_of_seasons',
        'number_of_episodes',
        'tomato_percent',
        'is_trending',
        'is_featured',
        'is_anime',
        'trailer_url',
        'genres',
        'cast',
    ];

    protected $casts = [
        'genres' => 'array',
        'cast' => 'array',
        'is_trending' => 'boolean',
        'is_featured' => 'boolean',
        'is_anime' => 'boolean',
        'vote_average' => 'float',
    ];

    protected $appends = [
        'poster_url',
        'backdrop_url',
    ];

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class)->orderBy('season_number');
    }

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
