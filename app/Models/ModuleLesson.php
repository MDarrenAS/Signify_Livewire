<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleLesson extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'type',
        'subtitle',
        'category',
        'badge_text',
        'xp_reward',
        'passing_score',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module_id' => 'integer',
            'xp_reward' => 'integer',
            'passing_score' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * @return HasMany<LessonItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(LessonItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<ModuleLessonQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ModuleLessonQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<UserLessonProgress, $this>
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserLessonProgress::class);
    }
}
