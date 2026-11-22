<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $table = 'quiz_questions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'quiz_id',
        'library_item_id',
        'library_media_id',
        'question_text',
        'question_type',
        'points',
        'sort_order',
        'explanation',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quiz_id' => 'integer',
            'library_item_id' => 'integer',
            'library_media_id' => 'integer',
            'points' => 'float',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * @return BelongsTo<LibraryItem, $this>
     */
    public function libraryItem(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    /**
     * @return HasMany<QuizOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('sort_order')->orderBy('id');
    }
}
