<?php

namespace App\Models;

use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @use HasFactory<QuizFactory>
 */
#[Fillable([
    'module_id',
    'lesson_id',
    'title',
    'slug',
    'description',
    'level',
    'xp_reward',
    'icon_emoji',
    'icon_url',
    'passing_score',
    'status',
    'sort_order',
    'time_limit_minutes',
    'attempt_limit',
    'created_by',
])]
class Quiz extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'xp_reward' => 'integer',
            'passing_score' => 'decimal:2',
            'sort_order' => 'integer',
            'time_limit_minutes' => 'integer',
            'attempt_limit' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Quiz $quiz): void {
            if (empty($quiz->slug)) {
                $baseSlug = Str::slug($quiz->title) ?: 'kuis';
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $quiz->slug = $slug;
            }
        });
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
