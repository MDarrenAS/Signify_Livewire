<?php

namespace App\Models;

use Database\Factories\QuizOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<QuizOptionFactory>
 */
#[Fillable([
    'question_id',
    'library_item_id',
    'library_media_id',
    'option_text',
    'media_url',
    'is_correct',
    'sort_order',
])]
class QuizOption extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    public function libraryItem(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(LibraryMedia::class, 'library_media_id');
    }

    public function getEffectiveMediaUrl(): ?string
    {
        if ($this->media) {
            try {
                return $this->media->temporaryUrl();
            } catch (\Throwable) {
                return $this->media->storage_path ? '/storage/'.$this->media->storage_path : null;
            }
        }

        return $this->media_url;
    }

    public function getEffectiveText(): string
    {
        return $this->libraryItem?->title ?? $this->option_text ?? '';
    }
}
