<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomList extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'is_public',
        'items_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'items_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CustomListItem::class)->orderBy('order')->orderByDesc('id');
    }

    public function refreshItemsCount(): void
    {
        $this->update(['items_count' => $this->items()->count()]);
    }
}
