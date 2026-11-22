<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quizzes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'module_id',
        'lesson_id',
        'title',
        'slug',
        'description',
        'passing_score',
        'status',
        'time_limit_minutes',
        'attempt_limit',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module_id' => 'integer',
            'lesson_id' => 'integer',
            'passing_score' => 'float',
            'time_limit_minutes' => 'integer',
            'attempt_limit' => 'integer',
            'created_by' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    /**
     * @return BelongsTo<ModuleLesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(ModuleLesson::class, 'lesson_id');
    }

    /**
     * @return HasMany<QuizQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('sort_order')->orderBy('id');
    }
}
