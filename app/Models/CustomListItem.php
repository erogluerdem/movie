<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomListItem extends Model
{
    protected $fillable = [
        'custom_list_id',
        'media_type',
        'media_id',
        'notes',
        'order',
    ];

    protected $appends = ['media'];

    public function customList()
    {
        return $this->belongsTo(CustomList::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class, 'media_id');
    }

    public function tvShow()
    {
        return $this->belongsTo(TvShow::class, 'media_id');
    }

    public function getMediaAttribute()
    {
        if ($this->media_type === 'movie') {
            return $this->movie;
        }

        return $this->tvShow;
    }
}
