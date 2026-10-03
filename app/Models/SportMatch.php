<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportMatch extends Model
{
    protected $table = 'sports_matches';

    protected $fillable = [
        'title',
        'slug',
        'league',
        'team_home',
        'team_away',
        'home_logo',
        'away_logo',
        'match_time',
        'status',
        'is_live',
        'stream_url',
        'stream_servers',
    ];

    protected $casts = [
        'is_live' => 'boolean',
        'stream_servers' => 'array',
    ];
}
