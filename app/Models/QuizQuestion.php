<?php

namespace App\Models;

use Database\Factories\QuizQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

/**
 * @use HasFactory<QuizQuestionFactory>
 */
#[Fillable([
    'quiz_id',
    'library_item_id',
    'library_media_id',
    'question_text',
    'question_type',
    'media_url',
    'points',
    'sort_order',
    'explanation',
])]
class QuizQuestion extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function libraryItem(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class, 'library_item_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(LibraryMedia::class, 'library_media_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('sort_order');
    }

    public function correctOption(): HasOne
    {
        return $this->hasOne(QuizOption::class, 'question_id')->where('is_correct', true);
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

        if (filled($this->media_url)) {
            if (str_starts_with($this->media_url, 'http://') || str_starts_with($this->media_url, 'https://') || str_starts_with($this->media_url, '/storage/')) {
                return $this->media_url;
            }

            try {
                return Storage::disk('s3')->temporaryUrl(
                    $this->media_url,
                    now()->addMinutes((int) config('services.media.signed_url_minutes', 60)),
                );
            } catch (\Throwable) {
                return Storage::disk('s3')->url($this->media_url);
            }
        }

        return null;
    }

    protected function question(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->question_text,
        );
    }

    protected function videoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getEffectiveMediaUrl(),
        );
    }

    protected function optionsList(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->options->map(fn ($o) => $o->getEffectiveText())->all(),
        );
    }

    protected function correctIndex(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) ($this->options->values()->search(fn ($opt) => $opt->is_correct) ?: 0),
        );
    }
}
