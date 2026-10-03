<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuratedCollection extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'backdrop_path',
        'is_featured',
        'order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CuratedCollectionItem::class)->orderBy('order');
    }
}
