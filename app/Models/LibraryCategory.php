<?php

namespace App\Models;

use Database\Factories\LibraryCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @use HasFactory<LibraryCategoryFactory>
 */
class LibraryCategory extends Model
{
    use HasFactory;

    public function items(): HasMany
    {
        return $this->hasMany(LibraryItem::class, 'category_id')
            ->orderBy('sort_order');
    }

    public function scopePublised(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
