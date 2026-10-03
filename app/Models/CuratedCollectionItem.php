<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CuratedCollectionItem extends Model
{
    protected $fillable = [
        'curated_collection_id',
        'collectible_type',
        'collectible_id',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(CuratedCollection::class, 'curated_collection_id');
    }

    public function collectible(): MorphTo
    {
        return $this->morphTo();
    }
}
