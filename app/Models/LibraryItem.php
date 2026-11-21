<?php

namespace App\Models;

use Database\Factories\LibraryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @use HasFactory<LibraryItemFactory>
 */
#[Fillable([
    'category_id',
    'title',
    'slug',
    'item_type',
    'meaning',
    'description',
    'sort_order',
    'status',
    'movement_description',
    'example_usage',
])]
class LibraryItem extends Model
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(LibraryMedia::class, 'library_item_media')
            ->withPivot(['sort_order', 'is_primary', 'caption'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('library_media.id');
    }

    public function scopePublised(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
