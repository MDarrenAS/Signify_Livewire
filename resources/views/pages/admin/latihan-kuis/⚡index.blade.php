<?php

use App\Models\LibraryItem;
use App\Models\LibraryMedia;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')] #[Title('Kelola Latihan Kuis — Admin Signify')] class extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $levelFilter = 'all';

    // State Modal Paket Kuis
    public bool $showModal = false;
    public ?int $editingQuizId = null;

    public string $title = '';
    public string $subtitle = '';
    public string $level = 'Pemula';
    public int $xpReward = 50;
    public string $iconEmoji = '🎯';
    public $iconFile = null;
    public string $iconUrl = '';
    public bool $isActive = true;

    // State Modal Kelola Soal Kuis
    public bool $showQuestionsModal = false;
    public ?int $manageQuizId = null;

    // State Tambah / Edit Soal
    public bool $showAddQuestionModal = false;
    public ?int $editingQuestionId = null;
    public string $questionType = 'sign_to_word';
    public ?int $selectedLibraryItemId = null;
    public ?int $selectedLibraryMediaId = null;
    public string $questionText = 'Perhatikan gerakan isyarat ini. Manakah arti atau kata yang benar?';
    public string $questionMediaUrl = '';
    public $questionMediaFile = null;
    public array $questionOptions = ['', '', '', ''];
    public array $questionOptionMediaFiles = [null, null, null, null];
    public array $questionOptionMediaUrls = ['', '', '', ''];
    public array $questionOptionLibraryItemIds = [null, null, null, null];
    public array $questionOptionLibraryMediaIds = [null, null, null, null];
    public int $questionCorrectIndex = 0;

    public function boot(): void
    {
        Gate::authorize('manage-kamus');
    }

    #[Computed]
    public function quizzes(): Collection
    {
        return Quiz::query()
            ->withCount('questions')
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%' . trim($this->search) . '%';
                $query->where(function (Builder $sub) use ($term): void {
                    $sub->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($this->levelFilter !== 'all', fn (Builder $q) => $q->where('level', $this->levelFilter))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    #[Computed]
    public function activeQuiz(): ?Quiz
    {
        if (! $this->manageQuizId) {
            return null;
        }

        return Quiz::with(['questions.options', 'questions.libraryItem', 'questions.media'])->find($this->manageQuizId);
    }

    #[Computed]
    public function kamusItems(): Collection
    {
        return LibraryItem::with(['media', 'category'])->orderBy('title')->get();
    }

    // ==================== KELOLA PAKET KUIS ====================
    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editingQuizId = null;
        $this->title         = '';
        $this->subtitle      = '';
        $this->level         = 'Pemula';
        $this->xpReward      = 50;
        $this->iconEmoji     = '🎯';
        $this->iconFile      = null;
        $this->iconUrl       = '';
        $this->isActive      = true;
        $this->showModal     = true;
    }

    public function openEdit(int $quizId): void
    {
        $quiz = Quiz::find($quizId);
        if (! $quiz) {
            return;
        }

        $this->resetValidation();
        $this->editingQuizId = $quiz->id;
        $this->title         = $quiz->title;
        $this->subtitle      = $quiz->description ?? '';
        $this->level         = $quiz->level;
        $this->xpReward      = (int) $quiz->xp_reward;
        $this->iconEmoji     = $quiz->icon_emoji ?: '🎯';
        $this->iconFile      = null;
        $this->iconUrl       = $quiz->icon_url ?? '';
        $this->isActive      = $quiz->isPublished();
        $this->showModal     = true;
    }

    public function savePackage(): void
    {
        $this->validate([
            'title'    => 'required|string|min:3|max:100',
            'xpReward' => 'required|integer|min:5|max:500',
            'iconFile' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|extensions:jpg,jpeg,png,gif,webp|max:10240',
        ], [
            'title.required' => 'Judul paket kuis wajib diisi.',
            'iconFile.mimes' => 'Ikon kuis harus berupa file gambar atau GIF (PNG, JPG, WebP, GIF).',
        ]);

        $finalIconUrl = $this->iconUrl;
        if ($this->iconFile) {
            try {
                $ext = strtolower((string) ($this->iconFile->getClientOriginalExtension() ?: $this->iconFile->extension()));
                $mime = $this->iconFile->getMimeType();
                $allowedMimes = [
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                ];
                $bucketMimeType = $allowedMimes[$ext] ?? $mime;
                $path = $this->iconFile->storeAs('quizzes/icons', Str::uuid().'.'.$ext, [
                    'disk' => 's3',
                    'ContentType' => $bucketMimeType,
                    'mimetype' => $bucketMimeType,
                ]);
                $finalIconUrl = $path;
            } catch (\Throwable $e) {
                try {
                    $path = $this->iconFile->store('quizzes/icons', 'public');
                    $finalIconUrl = '/storage/'.$path;
                } catch (\Throwable) {
                    // Fallback
                }
            }
        }

        $quiz = Quiz::updateOrCreate(
            ['id' => $this->editingQuizId],
            [
                'title'       => $this->title,
                'description' => $this->subtitle,
                'level'       => $this->level,
                'xp_reward'   => $this->xpReward,
                'icon_emoji'  => $this->iconEmoji ?: '🎯',
                'icon_url'    => $finalIconUrl,
                'status'      => $this->isActive ? 'published' : 'draft',
                'created_by'  => auth()->id(),
            ],
        );

        $actionText = $this->editingQuizId ? 'diperbarui' : 'ditambahkan';
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Paket kuis "' . $quiz->title . '" berhasil ' . $actionText . '!',
        ]);

        $this->showModal = false;
    }

    public function toggleStatus(int $quizId): void
    {
        $quiz = Quiz::find($quizId);
        if ($quiz) {
            $newStatus = $quiz->isPublished() ? 'draft' : 'published';
            $quiz->update(['status' => $newStatus]);

            $statusText = $newStatus === 'published' ? 'dipublikasikan' : 'dinonaktifkan';
            $this->dispatch('notify', [
                'type'    => 'info',
                'message' => 'Paket kuis berhasil ' . $statusText . '.',
            ]);
        }
    }

    public function deletePackage(int $quizId): void
    {
        $quiz = Quiz::find($quizId);
        if ($quiz) {
            $name = $quiz->title;
            $quiz->delete();

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Paket kuis "' . $name . '" berhasil dihapus.',
            ]);
        }
    }

    // ==================== KELOLA BUTIR SOAL KUIS ====================
    public function openManageQuestions(int $quizId): void
    {
        Quiz::query()->findOrFail($quizId);
        $this->manageQuizId = $quizId;
        $this->showQuestionsModal = true;
    }

    public function openAddQuestion(): void
    {
        Quiz::query()->findOrFail($this->manageQuizId ?? 0);
        $this->resetValidation();
        $this->editingQuestionId      = null;
        $this->questionType           = 'sign_to_word';
        $this->selectedLibraryItemId  = null;
        $this->selectedLibraryMediaId = null;
        $this->questionText           = 'Perhatikan gerakan isyarat ini. Manakah arti atau huruf yang benar?';
        $this->questionMediaUrl       = '';
        $this->questionMediaFile      = null;
        $this->questionOptions        = ['', '', '', ''];
        $this->questionOptionMediaFiles = [null, null, null, null];
        $this->questionOptionMediaUrls  = ['', '', '', ''];
        $this->questionOptionLibraryItemIds = [null, null, null, null];
        $this->questionOptionLibraryMediaIds = [null, null, null, null];
        $this->questionCorrectIndex   = 0;
        $this->showAddQuestionModal   = true;
    }

    public function updatedQuestionType(string $val): void
    {
        if ($this->selectedLibraryItemId) {
            $this->applyKamusItem($this->selectedLibraryItemId);
        } else {
            if ($val === 'sign_to_word') {
                $this->questionText = 'Perhatikan gerakan isyarat ini. Manakah arti atau huruf yang benar?';
            } elseif ($val === 'word_to_sign') {
                $this->questionText = 'Manakah gerakan isyarat BISINDO yang tepat?';
            } elseif ($val === 'media_to_sign') {
                $this->questionText = 'Perhatikan gambar objek di atas. Manakah gerakan isyarat yang tepat?';
            }
        }
    }

    public function openEditQuestion(int $questionId): void
    {
        $question = QuizQuestion::query()
            ->where('quiz_id', $this->manageQuizId)
            ->with(['options.media', 'options.libraryItem', 'media', 'libraryItem'])
            ->findOrFail($questionId);

        $this->resetValidation();
        $this->editingQuestionId      = $question->id;
        $this->questionType           = $question->question_type ?? 'sign_to_word';
        $this->selectedLibraryItemId  = $question->library_item_id;
        $this->selectedLibraryMediaId = $question->library_media_id;
        $this->questionText           = $question->question_text ?? '';
        $this->questionMediaFile      = null;

        // Resolve media URL
        $this->questionMediaUrl = $question->getEffectiveMediaUrl() ?? '';

        // Load existing options
        $options = $question->options->sortBy('sort_order')->values();
        $this->questionOptions         = ['', '', '', ''];
        $this->questionOptionMediaFiles = [null, null, null, null];
        $this->questionOptionMediaUrls  = ['', '', '', ''];
        $this->questionOptionLibraryItemIds = [null, null, null, null];
        $this->questionOptionLibraryMediaIds = [null, null, null, null];
        $this->questionCorrectIndex    = 0;
        foreach ($options as $idx => $opt) {
            if ($idx < 4) {
                $this->questionOptions[$idx] = $opt->option_text ?? $opt->getEffectiveText();
                $this->questionOptionLibraryItemIds[$idx] = $opt->library_item_id;
                $this->questionOptionLibraryMediaIds[$idx] = $opt->library_media_id;
                $this->questionOptionMediaUrls[$idx] = $opt->getEffectiveMediaUrl() ?? '';
                if ($opt->is_correct) {
                    $this->questionCorrectIndex = $idx;
                }
            }
        }

        $this->showAddQuestionModal = true;
    }

    public function applyKamusItem($itemId): void
    {
        if (blank($itemId)) {
            $this->selectedLibraryItemId = null;
            return;
        }

        $item = LibraryItem::with(['media'])->find($itemId);
        if (! $item) {
            return;
        }

        $this->selectedLibraryItemId = $item->id;
        $primaryMedia = $item->media->first();
        $correctIdx = (int) $this->questionCorrectIndex;

        if ($this->questionType === 'sign_to_word') {
            // Soal berupa isyarat video, jawaban berupa teks kata
            if ($primaryMedia) {
                $this->selectedLibraryMediaId = $primaryMedia->id;
                $this->questionMediaUrl = $this->resolveMediaUrl($primaryMedia);
            }
            $this->questionText = 'Perhatikan peragaan isyarat di atas. Manakah kosakata yang tepat?';
            $this->questionOptions[$correctIdx] = $item->title;
            $this->questionOptionLibraryItemIds[$correctIdx] = $item->id;
            $this->questionOptionLibraryMediaIds[$correctIdx] = null;
            $this->questionOptionMediaUrls[$correctIdx] = '';
            $this->questionOptionMediaFiles[$correctIdx] = null;

            // Isi opsi lainnya dengan kata lain dari kamus jika masih kosong
            $otherWords = LibraryItem::where('id', '!=', $item->id)->inRandomOrder()->limit(3)->pluck('title')->all();
            $otherIdx = 0;
            for ($i = 0; $i < 4; $i++) {
                if ($i !== $correctIdx && isset($otherWords[$otherIdx])) {
                    $this->questionOptions[$i] = $otherWords[$otherIdx];
                    $this->questionOptionLibraryItemIds[$i] = null;
                    $this->questionOptionLibraryMediaIds[$i] = null;
                    $this->questionOptionMediaUrls[$i] = '';
                    $this->questionOptionMediaFiles[$i] = null;
                    $otherIdx++;
                }
            }
        } elseif ($this->questionType === 'word_to_sign') {
            // Soal berupa teks kata/huruf, jawaban berupa video/gambar isyarat!
            $this->selectedLibraryMediaId = null;
            $this->questionMediaUrl = '';
            $this->questionMediaFile = null;
            $this->questionText = 'Manakah gerakan isyarat BISINDO yang melambangkan: "' . $item->title . '"?';

            // Kunci jawaban otomatis menarik video/gambar isyarat dari item kamus terpilih
            $this->questionOptions[$correctIdx] = 'Isyarat: ' . $item->title;
            $this->questionOptionLibraryItemIds[$correctIdx] = $item->id;
            $this->questionOptionLibraryMediaIds[$correctIdx] = $primaryMedia?->id;
            $this->questionOptionMediaUrls[$correctIdx] = $primaryMedia ? $this->resolveMediaUrl($primaryMedia) : '';
            $this->questionOptionMediaFiles[$correctIdx] = null;

            // Opsi pengecoh lainnya otomatis mengambil kosakata lain dari kamus jika tersedia
            $distractors = LibraryItem::with(['media'])
                ->where('id', '!=', $item->id)
                ->inRandomOrder()
                ->limit(3)
                ->get();

            $distIdx = 0;
            for ($i = 0; $i < 4; $i++) {
                if ($i !== $correctIdx) {
                    if (isset($distractors[$distIdx])) {
                        $dItem = $distractors[$distIdx];
                        $dMedia = $dItem->media->first();
                        $this->questionOptions[$i] = 'Isyarat: ' . $dItem->title;
                        $this->questionOptionLibraryItemIds[$i] = $dItem->id;
                        $this->questionOptionLibraryMediaIds[$i] = $dMedia?->id;
                        $this->questionOptionMediaUrls[$i] = $dMedia ? $this->resolveMediaUrl($dMedia) : '';
                        $this->questionOptionMediaFiles[$i] = null;
                        $distIdx++;
                    } elseif (blank($this->questionOptions[$i])) {
                        $this->questionOptions[$i] = 'Isyarat Alternatif ' . chr(65 + $i);
                    }
                }
            }
        } elseif ($this->questionType === 'media_to_sign') {
            // Soal berupa gambar objek riil, jawaban berupa video/gambar isyarat
            $this->questionText = 'Perhatikan gambar objek di atas. Manakah gerakan isyarat yang tepat untuk "' . $item->title . '"?';

            // Kunci jawaban otomatis menarik media isyarat dari kamus
            $this->questionOptions[$correctIdx] = 'Isyarat: ' . $item->title;
            $this->questionOptionLibraryItemIds[$correctIdx] = $item->id;
            $this->questionOptionLibraryMediaIds[$correctIdx] = $primaryMedia?->id;
            $this->questionOptionMediaUrls[$correctIdx] = $primaryMedia ? $this->resolveMediaUrl($primaryMedia) : '';
            $this->questionOptionMediaFiles[$correctIdx] = null;

            // Opsi pengecoh lainnya
            $distractors = LibraryItem::with(['media'])
                ->where('id', '!=', $item->id)
                ->inRandomOrder()
                ->limit(3)
                ->get();

            $distIdx = 0;
            for ($i = 0; $i < 4; $i++) {
                if ($i !== $correctIdx) {
                    if (isset($distractors[$distIdx])) {
                        $dItem = $distractors[$distIdx];
                        $dMedia = $dItem->media->first();
                        $this->questionOptions[$i] = 'Isyarat: ' . $dItem->title;
                        $this->questionOptionLibraryItemIds[$i] = $dItem->id;
                        $this->questionOptionLibraryMediaIds[$i] = $dMedia?->id;
                        $this->questionOptionMediaUrls[$i] = $dMedia ? $this->resolveMediaUrl($dMedia) : '';
                        $this->questionOptionMediaFiles[$i] = null;
                        $distIdx++;
                    } elseif (blank($this->questionOptions[$i])) {
                        $this->questionOptions[$i] = 'Isyarat Alternatif ' . chr(65 + $i);
                    }
                }
            }
        }
    }

    public function applyKamusItemToOption(int $optionIndex, $itemId): void
    {
        if (! isset($this->questionOptions[$optionIndex])) {
            return;
        }

        if (blank($itemId)) {
            $this->questionOptionLibraryItemIds[$optionIndex] = null;
            $this->questionOptionLibraryMediaIds[$optionIndex] = null;
            $this->questionOptionMediaUrls[$optionIndex] = '';
            return;
        }

        $item = LibraryItem::with(['media'])->find($itemId);
        if (! $item) {
            return;
        }

        $primaryMedia = $item->media->first();
        $this->questionOptionLibraryItemIds[$optionIndex] = $item->id;
        $this->questionOptions[$optionIndex] = $this->questionType === 'sign_to_word'
            ? $item->title
            : 'Isyarat: ' . $item->title;

        if ($primaryMedia) {
            $this->questionOptionLibraryMediaIds[$optionIndex] = $primaryMedia->id;
            $this->questionOptionMediaUrls[$optionIndex] = $this->resolveMediaUrl($primaryMedia);
        } else {
            $this->questionOptionLibraryMediaIds[$optionIndex] = null;
            $this->questionOptionMediaUrls[$optionIndex] = '';
        }

        $this->questionOptionMediaFiles[$optionIndex] = null;
    }

    public function clearOptionMedia(int $optionIndex): void
    {
        if (isset($this->questionOptions[$optionIndex])) {
            $this->questionOptionMediaUrls[$optionIndex] = '';
            $this->questionOptionMediaFiles[$optionIndex] = null;
            $this->questionOptionLibraryMediaIds[$optionIndex] = null;
            $this->questionOptionLibraryItemIds[$optionIndex] = null;
        }
    }

    private function resolveMediaUrl(LibraryMedia $media): string
    {
        try {
            return $media->temporaryUrl();
        } catch (\Throwable) {
            return $media->storage_path ? '/storage/' . $media->storage_path : '';
        }
    }

    public function saveQuestion(): void
    {
        $this->resetValidation();
        $this->validate([
            'questionType'                 => ['required', 'in:sign_to_word,word_to_sign,media_to_sign'],
            'questionText'                 => ['nullable', 'string', 'max:250'],
            'questionMediaUrl'             => ['nullable', 'string', 'max:2048'],
            'questionMediaFile'            => ['nullable', 'file', 'mimes:mp4,webm,mov,gif,png,jpg,jpeg,webp', 'extensions:mp4,webm,mov,gif,png,jpg,jpeg,webp', 'max:'.(int) config('services.media.max_upload_kb', 51200)],
            'questionOptions'              => ['required', 'array', 'size:4'],
            'questionOptions.*'            => ['nullable', 'string', 'max:100'],
            'questionOptionMediaFiles'     => ['required', 'array', 'size:4'],
            'questionOptionMediaFiles.*'   => ['nullable', 'file', 'mimes:mp4,webm,mov,gif,png,jpg,jpeg,webp', 'extensions:mp4,webm,mov,gif,png,jpg,jpeg,webp', 'max:'.(int) config('services.media.max_upload_kb', 51200)],
            'questionOptionMediaUrls'      => ['required', 'array', 'size:4'],
            'questionOptionMediaUrls.*'    => ['nullable', 'string', 'max:2048'],
            'questionCorrectIndex'         => ['required', 'integer', 'between:0,3'],
            'selectedLibraryItemId'        => ['nullable', 'integer', 'exists:library_items,id'],
            'selectedLibraryMediaId'       => ['nullable', 'integer', 'exists:library_media,id'],
        ], [
            'questionType.in' => 'Tipe soal tidak valid.',
            'questionMediaFile.mimes' => 'Media soal harus berupa file video (MP4, WEBM, MOV) atau gambar (PNG, JPG, WEBP, GIF).',
            'questionOptionMediaFiles.*.mimes' => 'Media pilihan harus berupa file video (MP4, WEBM, MOV) atau gambar (PNG, JPG, WEBP, GIF).',
        ]);

        $hasQuestionContent = filled($this->questionText)
            || filled($this->questionMediaUrl)
            || $this->questionMediaFile !== null;

        if (! $hasQuestionContent || ($this->questionType === 'word_to_sign' && blank($this->questionText))) {
            $this->addError('questionText', 'Isi teks pertanyaan atau unggah media soal.');

            return;
        }

        foreach ($this->questionOptions as $index => $optionText) {
            $hasOptionContent = filled($optionText)
                || filled($this->questionOptionMediaUrls[$index] ?? null)
                || ($this->questionOptionMediaFiles[$index] ?? null) !== null
                || ($this->questionOptionLibraryMediaIds[$index] ?? null) !== null;

            if (! $hasOptionContent) {
                $this->addError("questionOptions.{$index}", 'Isi teks atau pilih/unggah media untuk pilihan ini.');
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $quiz = Quiz::query()->findOrFail($this->manageQuizId ?? 0);

        // Determine the media_url to persist (must be a short storage path, not a signed URL).
        // When a kamus item is selected, the relationship (library_media_id) handles display,
        // so we don't need to store a URL at all.
        $finalUrl = null;
        if ($this->questionMediaFile) {
            $finalUrl = $this->storeMedia($this->questionMediaFile, 'quizzes/media');
        } elseif (! $this->selectedLibraryMediaId && filled($this->questionMediaUrl)) {
            // Strip any signed URL back to a storage path for direct uploads already on S3
            $finalUrl = $this->extractStoragePath($this->questionMediaUrl);
        }

        $finalOptionMediaUrls = [];
        foreach ($this->questionOptionMediaUrls as $index => $optUrl) {
            if ($this->questionOptionMediaFiles[$index] ?? null) {
                $finalOptionMediaUrls[$index] = $this->storeMedia($this->questionOptionMediaFiles[$index], 'quizzes/options');
            } else {
                $finalOptionMediaUrls[$index] = filled($optUrl) ? $this->extractStoragePath($optUrl) : null;
            }
        }

        DB::transaction(function () use ($quiz, $finalUrl, $finalOptionMediaUrls): void {
            if ($this->editingQuestionId) {
                $question = QuizQuestion::query()
                    ->where('quiz_id', $quiz->id)
                    ->findOrFail($this->editingQuestionId);

                $question->update([
                    'library_item_id'  => $this->selectedLibraryItemId,
                    'library_media_id' => $this->selectedLibraryMediaId,
                    'question_type'    => $this->questionType,
                    'question_text'    => $this->questionText,
                    'media_url'        => $finalUrl,
                ]);

                // Delete old options and recreate
                $question->options()->delete();
                foreach ($this->questionOptions as $idx => $optText) {
                    $optMediaId = $this->questionOptionLibraryMediaIds[$idx] ?? null;
                    $optItemId = $this->questionOptionLibraryItemIds[$idx] ?? null;
                    $optMediaUrl = $optMediaId ? null : ($finalOptionMediaUrls[$idx] ?: null);

                    $question->options()->create([
                        'library_item_id'  => $optItemId,
                        'library_media_id' => $optMediaId,
                        'option_text'      => $optText,
                        'media_url'        => $optMediaUrl,
                        'is_correct'       => $idx === (int) $this->questionCorrectIndex,
                        'sort_order'       => $idx,
                    ]);
                }
            } else {
                $sortOrder = $quiz->questions()->count() + 1;

                $question = $quiz->questions()->create([
                    'library_item_id'  => $this->selectedLibraryItemId,
                    'library_media_id' => $this->selectedLibraryMediaId,
                    'question_type'    => $this->questionType,
                    'question_text'    => $this->questionText,
                    'media_url'        => $finalUrl,
                    'points'           => 1,
                    'sort_order'       => $sortOrder,
                ]);

                foreach ($this->questionOptions as $idx => $optText) {
                    $optMediaId = $this->questionOptionLibraryMediaIds[$idx] ?? null;
                    $optItemId = $this->questionOptionLibraryItemIds[$idx] ?? null;
                    $optMediaUrl = $optMediaId ? null : ($finalOptionMediaUrls[$idx] ?: null);

                    $question->options()->create([
                        'library_item_id'  => $optItemId,
                        'library_media_id' => $optMediaId,
                        'option_text'      => $optText,
                        'media_url'        => $optMediaUrl,
                        'is_correct'       => $idx === (int) $this->questionCorrectIndex,
                        'sort_order'       => $idx,
                    ]);
                }
            }
        });

        $actionText = $this->editingQuestionId ? 'diperbarui' : 'ditambahkan';
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Soal kuis berhasil ' . $actionText . '!',
        ]);

        $this->showAddQuestionModal = false;
    }

    public function removeQuestion(int $qId): void
    {
        $question = QuizQuestion::query()
            ->where('quiz_id', $this->manageQuizId)
            ->findOrFail($qId);

        $question->delete();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Soal kuis berhasil dihapus.',
        ]);
    }

    private function storeMedia(UploadedFile $file, string $directory): string
    {
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));
        $mimeType = $file->getMimeType();

        $allowedMimes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
        ];

        $bucketMimeType = $allowedMimes[$extension] ?? $mimeType;

        try {
            $path = $file->storeAs($directory, Str::uuid().'.'.$extension, [
                'disk' => 's3',
                'ContentType' => $bucketMimeType,
                'mimetype' => $bucketMimeType,
            ]);

            if ($path !== false && is_string($path)) {
                return $path;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // Fallback to local public disk if S3 fails
        $path = $file->store($directory, 'public');

        if ($path === false) {
            throw new \RuntimeException("Unable to store uploaded media in [{$directory}].");
        }

        return '/storage/'.$path;
    }

    /**
     * Extract a short storage path from a value that may be a full signed S3 URL,
     * a /storage/ local path, or already a short storage path.
     */
    private function extractStoragePath(string $url): string
    {
        // Already a short storage path (e.g. "quizzes/media/uuid.mp4" or "kamus/1/uuid.mp4")
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://') && ! str_starts_with($url, '/storage/')) {
            return $url;
        }

        // Local storage path — keep as-is
        if (str_starts_with($url, '/storage/')) {
            return $url;
        }

        // Signed S3 URL — extract the storage path from the URL path segment
        // e.g. https://...supabase.co/storage/v1/s3/bisindo-media/kamus/1/uuid.mp4?X-Amz-...
        $parsed = parse_url($url, PHP_URL_PATH);
        if ($parsed) {
            $bucket = config('filesystems.disks.s3.bucket', 'bisindo-media');
            $marker = '/s3/' . $bucket . '/';
            $pos = strpos($parsed, $marker);
            if ($pos !== false) {
                return substr($parsed, $pos + strlen($marker));
            }

            // Also try /object/ path format: /storage/v1/object/.../bucket/path
            $objectMarker = '/object/public/' . $bucket . '/';
            $objPos = strpos($parsed, $objectMarker);
            if ($objPos !== false) {
                return substr($parsed, $objPos + strlen($objectMarker));
            }

            $objectAuthMarker = '/object/authenticated/' . $bucket . '/';
            $objAuthPos = strpos($parsed, $objectAuthMarker);
            if ($objAuthPos !== false) {
                return substr($parsed, $objAuthPos + strlen($objectAuthMarker));
            }
        }

        // If we can't extract a path, return the original (truncation will be caught by validation)
        return $url;
    }
};
?>

<div class="space-y-6">
    <x-admin.page-header
        title="Latihan & Tantangan Kuis"
        subtitle="Kelola paket evaluasi mandiri, tingkat kesulitan, reward skor, dan butir-butir soal peraga kuis yang tersimpan di database."
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="openCreate"
                class="inline-flex items-center gap-2 bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all cursor-pointer shadow-xs"
            >
                <flux:icon.plus class="size-4" />
                <span>Tambah Paket Kuis</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter & Search Responsif --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Cari paket kuis berdasarkan judul atau deskripsi..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-medium focus:outline-none focus:border-[#F9B92F] transition-colors"
            />
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#71717A] pointer-events-none">
                <flux:icon.magnifying-glass class="size-4" />
            </div>
        </div>

        <select
            wire:model.live="levelFilter"
            class="px-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-extrabold bg-white text-[#1C1C1E] focus:outline-none focus:border-[#F9B92F] cursor-pointer"
        >
            <option value="all">Semua Tingkat</option>
            <option value="Pemula">Pemula</option>
            <option value="Menengah">Menengah</option>
            <option value="Mahir">Mahir</option>
        </select>
    </div>

    {{-- Tabel Paket Kuis --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E4E4E7] text-[11px] font-extrabold uppercase tracking-wider text-[#71717A] bg-[#FAFAFA]">
                        <th class="py-3.5 px-5">Paket Kuis</th>
                        <th class="py-3.5 px-4">Tingkat</th>
                        <th class="py-3.5 px-4">Jumlah Soal</th>
                        <th class="py-3.5 px-4">Reward</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E4E7] text-sm">
                    @forelse ($this->quizzes as $quiz)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    {{-- Ikon / Thumbnail Kuis --}}
                                    <div class="w-11 h-11 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-xl shrink-0 overflow-hidden shadow-2xs">
                                        @if (!empty($quiz->icon_url))
                                            <img src="{{ $quiz->icon_url }}" alt="{{ $quiz->title }}" class="w-full h-full object-cover" />
                                        @else
                                            {{ $quiz->icon_emoji ?: '🎯' }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-extrabold text-sm text-[#1C1C1E]">{{ $quiz->title }}</h3>
                                        @if (!empty($quiz->description))
                                            <p class="text-xs text-[#71717A] font-medium truncate max-w-md">{{ $quiz->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-[#F4F4F5] text-[#52525B]">
                                    {{ $quiz->level }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs font-extrabold text-[#1C1C1E]">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#F4F4F5]">
                                    {{ $quiz->questions_count }} Soal
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs font-extrabold text-[#C98A06]">
                                +{{ $quiz->xp_reward }} XP
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border {{ $quiz->isPublished() ? 'bg-[#F0FDF4] border-[#86EFAC] text-[#15803D]' : 'bg-zinc-100 border-[#E4E4E7] text-[#71717A]' }}">
                                    {{ $quiz->isPublished() ? 'Aktif' : 'Draft' }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right space-x-1.5">
                                <button
                                    type="button"
                                    wire:click="openManageQuestions({{ $quiz->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] text-xs font-extrabold text-[#C98A06] hover:bg-[#F9B92F] hover:text-[#1C1C1E] transition-colors cursor-pointer"
                                    title="Kelola & Tambah Soal Kuis"
                                >
                                    Kelola Soal ({{ $quiz->questions_count }}) →
                                </button>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $quiz->id }})"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold cursor-pointer transition-colors"
                                    title="{{ $quiz->isPublished() ? 'Nonaktifkan' : 'Publikasikan' }}"
                                >
                                    {{ $quiz->isPublished() ? 'Nonaktifkan' : 'Publikasikan' }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="openEdit({{ $quiz->id }})"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold text-[#52525B] cursor-pointer"
                                    title="Edit Paket"
                                >
                                    ✎
                                </button>
                                <button
                                    type="button"
                                    wire:click="deletePackage({{ $quiz->id }})"
                                    wire:confirm="Yakin ingin menghapus paket kuis '{{ $quiz->title }}'?"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-xs font-extrabold text-[#B91C1C] cursor-pointer"
                                    title="Hapus Paket"
                                >
                                    ✕
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-xs font-medium text-[#71717A]">
                                Tidak ada paket kuis yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah / Edit Paket Kuis --}}
    <x-admin.latihan-kuis.package-modal
        :show="$showModal"
        :edit-index="$editingQuizId"
        :icon-file="$iconFile"
        :icon-url="$iconUrl"
    />

    {{-- Modal Kelola Daftar Butir Soal --}}
    <x-admin.latihan-kuis.questions-modal
        :show="$showQuestionsModal"
        :active-package="$this->activeQuiz"
    />

    {{-- Modal Form Input Soal Baru / Edit Soal --}}
    <x-admin.latihan-kuis.add-question-modal
        :show="$showAddQuestionModal"
        :editing-question-id="$editingQuestionId"
        :media-file="$questionMediaFile"
        :media-url="$questionMediaUrl"
        :correct-index="$questionCorrectIndex"
        :question-type="$questionType"
        :kamus-items="$this->kamusItems"
        :selected-library-item-id="$selectedLibraryItemId"
        :option-media-files="$questionOptionMediaFiles"
        :option-media-urls="$questionOptionMediaUrls"
        :option-library-item-ids="$questionOptionLibraryItemIds"
    />
</div>
