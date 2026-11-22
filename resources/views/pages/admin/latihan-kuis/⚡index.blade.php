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
            'iconFile' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:10240',
        ], [
            'title.required' => 'Judul paket kuis wajib diisi.',
            'iconFile.mimes' => 'Ikon kuis harus berupa file gambar atau GIF (JPG, PNG, GIF, SVG, WEBP).',
        ]);

        $finalIconUrl = $this->iconUrl;
        if ($this->iconFile) {
            try {
                $path = $this->iconFile->store('quizzes/icons', 'public');
                $finalIconUrl = '/storage/' . $path;
            } catch (\Throwable $e) {
                // Fallback
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
        $this->questionCorrectIndex   = 0;
        $this->showAddQuestionModal   = true;
    }

    public function openEditQuestion(int $questionId): void
    {
        $question = QuizQuestion::query()
            ->where('quiz_id', $this->manageQuizId)
            ->with(['options', 'media'])
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
        $this->questionCorrectIndex    = 0;
        foreach ($options as $idx => $opt) {
            if ($idx < 4) {
                $this->questionOptions[$idx] = $opt->option_text ?? $opt->getEffectiveText();
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
        if ($primaryMedia) {
            $this->selectedLibraryMediaId = $primaryMedia->id;
            try {
                $this->questionMediaUrl = $primaryMedia->temporaryUrl();
            } catch (\Throwable) {
                $this->questionMediaUrl = $primaryMedia->storage_path ? '/storage/' . $primaryMedia->storage_path : '';
            }
        }

        if ($this->questionType === 'sign_to_word') {
            $this->questionText = 'Perhatikan peragaan isyarat di atas. Manakah kosakata yang tepat?';
            $this->questionOptions[$this->questionCorrectIndex] = $item->title;

            // Isi opsi lainnya dengan kata lain dari kamus jika masih kosong
            $otherWords = LibraryItem::where('id', '!=', $item->id)->inRandomOrder()->limit(3)->pluck('title')->all();
            $otherIdx = 0;
            for ($i = 0; $i < 4; $i++) {
                if ($i !== (int) $this->questionCorrectIndex && isset($otherWords[$otherIdx])) {
                    $this->questionOptions[$i] = $otherWords[$otherIdx];
                    $otherIdx++;
                }
            }
        } elseif ($this->questionType === 'word_to_sign') {
            $this->questionText = 'Manakah gerakan isyarat BISINDO yang melambangkan: "' . $item->title . '"?';
            $this->questionOptions[$this->questionCorrectIndex] = 'Gerakan: ' . $item->title;
        } elseif ($this->questionType === 'media_to_sign') {
            $this->questionText = 'Perhatikan gambar objek di atas. Manakah gerakan isyarat yang tepat untuk "' . $item->title . '"?';
            $this->questionOptions[$this->questionCorrectIndex] = 'Isyarat: ' . $item->title;
        }
    }

    public function saveQuestion(): void
    {
        $this->resetValidation();
        $this->validate([
            'questionType'                 => ['required', 'in:sign_to_word,word_to_sign,media_to_sign'],
            'questionText'                 => ['nullable', 'string', 'max:250'],
            'questionMediaUrl'             => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/storage\/)/i'],
            'questionMediaFile'            => ['nullable', 'file', 'mimes:mp4,webm,gif,png,jpg,jpeg,webp', 'max:30720'],
            'questionOptions'              => ['required', 'array', 'size:4'],
            'questionOptions.*'            => ['nullable', 'string', 'max:100'],
            'questionOptionMediaFiles'     => ['required', 'array', 'size:4'],
            'questionOptionMediaFiles.*'   => ['nullable', 'file', 'mimes:mp4,webm,gif,png,jpg,jpeg,webp', 'max:30720'],
            'questionOptionMediaUrls'      => ['required', 'array', 'size:4'],
            'questionOptionMediaUrls.*'    => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/storage\/)/i'],
            'questionCorrectIndex'         => ['required', 'integer', 'between:0,3'],
            'selectedLibraryItemId'        => ['nullable', 'integer', 'exists:library_items,id'],
            'selectedLibraryMediaId'       => ['nullable', 'integer', 'exists:library_media,id'],
        ], [
            'questionType.in' => 'Tipe soal tidak valid.',
            'questionMediaFile.mimes' => 'Media soal harus berupa file video (MP4, WEBM) atau gambar (GIF, PNG, JPG).',
            'questionOptionMediaFiles.*.mimes' => 'Media pilihan harus berupa file video (MP4, WEBM) atau gambar (GIF, PNG, JPG).',
        ]);

        $hasQuestionContent = filled($this->questionText)
            || filled($this->questionMediaUrl)
            || $this->questionMediaFile !== null;

        if (! $hasQuestionContent || ($this->questionType === 'word_to_sign' && blank($this->questionText))) {
            $this->addError('questionText', 'Isi teks pertanyaan atau unggah media soal.');

            return;
        }

        foreach ($this->questionOptions as $index => $optionText) {
            if (
                blank($optionText)
                && blank($this->questionOptionMediaUrls[$index] ?? null)
                && ($this->questionOptionMediaFiles[$index] ?? null) === null
            ) {
                $this->addError("questionOptions.{$index}", 'Isi teks atau unggah media untuk pilihan ini.');
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $quiz = Quiz::query()->findOrFail($this->manageQuizId ?? 0);
        $finalUrl = $this->questionMediaUrl;
        if ($this->questionMediaFile) {
            $finalUrl = $this->storeMedia($this->questionMediaFile, 'quizzes/media');
        }

        $finalOptionMediaUrls = $this->questionOptionMediaUrls;
        foreach ($this->questionOptionMediaFiles as $index => $mediaFile) {
            if ($mediaFile) {
                $finalOptionMediaUrls[$index] = $this->storeMedia($mediaFile, 'quizzes/options');
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
                    $question->options()->create([
                        'option_text' => $optText,
                        'media_url'   => $finalOptionMediaUrls[$idx] ?: null,
                        'is_correct'  => $idx === (int) $this->questionCorrectIndex,
                        'sort_order'  => $idx,
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
                    $question->options()->create([
                        'option_text' => $optText,
                        'media_url'   => $finalOptionMediaUrls[$idx] ?: null,
                        'is_correct'  => $idx === (int) $this->questionCorrectIndex,
                        'sort_order'  => $idx,
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
        $path = $file->store($directory, 'public');

        if ($path === false) {
            throw new \RuntimeException("Unable to store uploaded media in [{$directory}].");
        }

        return '/storage/'.$path;
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
    />
</div>
