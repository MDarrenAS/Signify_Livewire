<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleLessonQuestion extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'module_lesson_id',
        'library_item_id',
        'question',
        'video_url',
        'symbol_hint',
        'options',
        'correct_index',
        'explanation',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module_lesson_id' => 'integer',
            'library_item_id' => 'integer',
            'options' => 'array',
            'correct_index' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ModuleLesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(ModuleLesson::class, 'module_lesson_id');
    }

    /**
     * @return BelongsTo<LibraryItem, $this>
     */
    public function libraryItem(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }
}
