<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'level',
        'sort_order',
        'status',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'created_by' => 'integer',
        ];
    }

    /**
     * @return HasMany<ModuleLesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(ModuleLesson::class, 'module_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Alias for lessons to match the frontend terminology ('parts').
     *
     * @return HasMany<ModuleLesson, $this>
     */
    public function parts(): HasMany
    {
        return $this->lessons();
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'module_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getIconAttribute(): string
    {
        if ($this->level === 'advanced') {
            return '🏆';
        }

        if ($this->level === 'intermediate') {
            return '💬';
        }

        return '🤟';
    }

    public function getIconEmojiAttribute(): string
    {
        return $this->icon;
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->thumbnail_path;
    }

    public function getXpRewardAttribute(): int
    {
        return 70;
    }

    public function getSubtitleAttribute(): ?string
    {
        return $this->description;
    }
}
