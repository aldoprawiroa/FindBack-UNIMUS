<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title',
    'description',
    'category_id',
    'found_at',
    'status',
    'published_at',
    'photo_path',
])]

class FoundItem extends Model
{
    protected function casts(): array
    {
        return [
            'found_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}