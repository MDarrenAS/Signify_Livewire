<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class LibraryItem extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(LibraryMedia::class)
        ->orderBy('sort_order');
    }

    public function scopePublised(Builder $query): Builder
    {
        return $query->where('status','published');
    }
}
