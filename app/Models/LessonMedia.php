<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonMedia extends Model
{
    use HasFactory;

    protected $table = 'lesson_media';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lesson_id',
        'media_type',
        'storage_path',
        'mime_type',
        'file_size_bytes',
        'alt_text',
        'sort_order',
        'caption',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lesson_id' => 'integer',
            'file_size_bytes' => 'integer',
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
}
