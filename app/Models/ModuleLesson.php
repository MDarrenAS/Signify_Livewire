<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'description',
        'content',
        'sort_order',
        'status',
        'lesson_type',
        'summary',
        'estimated_minutes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'module_id' => 'integer',
            'sort_order' => 'integer',
            'estimated_minutes' => 'integer',
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
     * @return BelongsToMany<LibraryItem, $this>
     */
    public function libraryItems(): BelongsToMany
    {
        return $this->belongsToMany(LibraryItem::class, 'lesson_library_items', 'lesson_id', 'library_item_id')
            ->withPivot(['id', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('library_items.id');
    }

    /**
     * @return HasMany<LessonMedia, $this>
     */
    public function lessonMedia(): HasMany
    {
        return $this->hasMany(LessonMedia::class, 'lesson_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return HasOne<Quiz, $this>
     */
    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class, 'lesson_id');
    }

    /**
     * @return HasMany<UserLessonProgress, $this>
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserLessonProgress::class, 'lesson_id');
    }

    public function getTypeAttribute(): string
    {
        return $this->lesson_type ?? 'lesson';
    }

    public function setTypeAttribute(string $value): void
    {
        $this->attributes['lesson_type'] = $value;
    }

    public function getSubtitleAttribute(): ?string
    {
        return $this->summary ?? $this->description;
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->summary ?? $this->description;
    }

    public function getXpRewardAttribute(): int
    {
        return 10;
    }

    public function getPassingScoreAttribute(): int
    {
        return (int) ($this->quiz?->passing_score ?? 80);
    }

    public function getBadgeTextAttribute(): string
    {
        if ($this->lesson_type === 'quiz') {
            return 'Kuis Latihan';
        }

        $itemsCount = count($this->items);

        return $itemsCount > 0 ? "{$itemsCount} Isyarat" : 'Materi Belajar';
    }

    /**
     * Get items for the lesson player.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getItemsAttribute(): array
    {
        // 1. If stored in content JSON, decode and return
        if (! empty($this->content)) {
            $decoded = json_decode($this->content, true);
            if (is_array($decoded) && isset($decoded['items']) && is_array($decoded['items'])) {
                return $decoded['items'];
            }
        }

        // 2. Otherwise derive from attached libraryItems
        $items = [];
        $libraryItems = $this->libraryItems;

        foreach ($libraryItems as $idx => $lib) {
            $mediaUrl = null;
            if ($lib->media && $lib->media->isNotEmpty()) {
                try {
                    $mediaUrl = $lib->media->first()->temporaryUrl();
                } catch (\Throwable) {
                    $mediaUrl = $lib->media->first()->storage_path;
                }
            }

            $items[] = [
                'id' => $lib->id,
                'library_item_id' => $lib->id,
                'order' => $lib->pivot->sort_order ?? ($idx + 1),
                'symbol' => strtoupper(substr($lib->title, 0, 3)),
                'title' => $lib->title,
                'hand_type' => '2 Tangan',
                'video_url' => $mediaUrl,
                'instruction' => $lib->movement_description ?: ($lib->description ?: 'Perhatikan gerakan tangan.'),
                'description' => $lib->movement_description ?: ($lib->description ?: 'Perhatikan gerakan tangan.'),
                'una_tip' => $lib->example_usage ?: 'Perhatikan posisi jari dan arah telapak tangan.',
                'tip' => $lib->example_usage ?: 'Perhatikan posisi jari dan arah telapak tangan.',
            ];
        }

        return $items;
    }

    /**
     * Get questions for the quiz player.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getQuestionsAttribute(): array
    {
        // 1. If stored in content JSON, decode and return
        if (! empty($this->content)) {
            $decoded = json_decode($this->content, true);
            if (is_array($decoded) && isset($decoded['questions']) && is_array($decoded['questions'])) {
                return $decoded['questions'];
            }
        }

        // 2. Otherwise derive from quiz relation
        $quiz = $this->quiz;
        if (! $quiz) {
            return [];
        }

        $questions = [];
        foreach ($quiz->questions as $q) {
            $options = $q->options->pluck('option_text')->all();
            $correctIdx = 0;
            foreach ($q->options as $oIdx => $opt) {
                if ($opt->is_correct) {
                    $correctIdx = $oIdx;
                    break;
                }
            }

            $questions[] = [
                'id' => $q->id,
                'order' => $q->sort_order,
                'question' => $q->question_text,
                'media_url' => null,
                'video_url' => null,
                'symbol_hint' => null,
                'options' => $options,
                'correct_index' => $correctIdx,
                'explanation' => $q->explanation,
            ];
        }

        return $questions;
    }
}
