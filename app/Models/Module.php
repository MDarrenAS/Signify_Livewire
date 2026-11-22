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
        'unit_id',
        'title',
        'slug',
        'icon_emoji',
        'icon_url',
        'subtitle',
        'description',
        'xp_reward',
        'visual_content',
        'tag',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_id' => 'integer',
            'xp_reward' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return HasMany<ModuleLesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(ModuleLesson::class)->orderBy('sort_order')->orderBy('id');
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
}
