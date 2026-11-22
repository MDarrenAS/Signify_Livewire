<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'modul_slug',
    'part_slug',
    'part_type',
    'score',
    'is_completed',
    'completed_at',
])]
class UserModuleProgress extends Model
{
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForModule(Builder $query, string $modulSlug): Builder
    {
        return $query->where('modul_slug', $modulSlug);
    }
}
