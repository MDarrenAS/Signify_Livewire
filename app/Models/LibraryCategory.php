<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryCategory extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(LibraryItem::class, 'category_id')
        ->orderBy('sort_order');
    }
        public function scopePublised(Builder $query): Builder
    {
        return $query->where('status','published');
    }
}
