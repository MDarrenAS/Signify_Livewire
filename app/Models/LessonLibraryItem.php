<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonLibraryItem extends Model
{
    use HasFactory;

    protected $table = 'lesson_library_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lesson_id',
        'library_item_id',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lesson_id' => 'integer',
            'library_item_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ModuleLesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(ModuleLesson::class, 'lesson_id');
    }

    /**
     * @return BelongsTo<LibraryItem, $this>
     */
    public function libraryItem(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }
}
