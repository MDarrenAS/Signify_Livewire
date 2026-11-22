<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Tantangan Kuis BISINDO')] class extends Component
{
    public string $slug;
    public int $currentIndex = 0;
    public ?int $selectedOptionId = null;
    public bool $isAnswerChecked = false;
    public bool $isCurrentCorrect = false;
    public int $correctCount = 0;
    public bool $isFinished = false;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    #[Computed]
    public function quiz(): Quiz
    {
        return Quiz::published()
            ->where('slug', $this->slug)
            ->with([
                'questions.options',
                'questions.libraryItem',
                'questions.media',
            ])
            ->firstOrFail();
    }

    #[Computed]
    public function currentQuestion(): ?QuizQuestion
    {
        return $this->quiz->questions->get($this->currentIndex);
    }

    public function selectOption(int $optionId): void
    {
        if ($this->isAnswerChecked) {
            return;
        }

        $this->selectedOptionId = $optionId;
    }

    public function checkAnswer(): void
    {
        if (! $this->selectedOptionId || $this->isAnswerChecked) {
            return;
        }

        $selectedOption = QuizOption::find($this->selectedOptionId);
        $this->isCurrentCorrect = (bool) ($selectedOption?->is_correct);

        if ($this->isCurrentCorrect) {
            $this->correctCount++;
        }

        $this->isAnswerChecked = true;
    }

    public function nextQuestion(): void
    {
        $totalQuestions = $this->quiz->questions->count();

        if ($this->currentIndex + 1 < $totalQuestions) {
            $this->currentIndex++;
            $this->selectedOptionId = null;
            $this->isAnswerChecked  = false;
            $this->isCurrentCorrect = false;
        } else {
            $this->isFinished = true;
            $this->saveAttempt();
        }
    }

    public function saveAttempt(): void
    {
        if (! auth()->check()) {
            return;
        }

        $totalQuestions = $this->quiz->questions->count();
        $percentage = $totalQuestions > 0 ? round(($this->correctCount / $totalQuestions) * 100) : 0;
        $isPassed = $percentage >= (float) $this->quiz->passing_score;
        $pointsEarned = $isPassed ? (float) $this->quiz->xp_reward : round($this->quiz->xp_reward * ($percentage / 100));

        $attemptNumber = QuizAttempt::where('user_id', auth()->id())
            ->where('quiz_id', $this->quiz->id)
            ->count() + 1;

        QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $this->quiz->id,
            'status' => 'submitted',
            'score' => $percentage,
            'points_earned' => $pointsEarned,
            'points_total' => $this->quiz->xp_reward,
            'passed' => $isPassed,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
    }

    public function retry(): void
    {
        $this->currentIndex     = 0;
        $this->selectedOptionId = null;
        $this->isAnswerChecked  = false;
        $this->isCurrentCorrect = false;
        $this->correctCount     = 0;
        $this->isFinished       = false;
    }
};
?>

<div class="max-w-3xl mx-auto space-y-6 pb-12">
    {{-- Top Bar: Kembali, Progress & XP --}}
    <div class="flex items-center justify-between gap-4">
        <a
            href="{{ route('latihan.index') }}"
            wire:navigate
            class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#71717A] hover:text-[#1C1C1E] transition-colors"
        >
            ← Kembali ke Daftar Kuis
        </a>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FFF8E6] border border-[#F9B92F] text-xs font-extrabold text-[#C98A06]">
            <span>{{ $this->quiz->icon_emoji ?: '🎯' }}</span>
            <span>+{{ $this->quiz->xp_reward }} XP</span>
        </div>
    </div>

    @if ($this->quiz->questions->isEmpty())
        <div class="p-12 text-center border-2 border-dashed border-[#E4E4E7] rounded-3xl bg-white space-y-3">
            <span class="text-4xl block">📝</span>
            <h3 class="text-base font-extrabold text-[#1C1C1E]">Belum Ada Soal</h3>
            <p class="text-xs text-[#71717A]">Paket kuis ini belum memiliki pertanyaan yang terdaftar.</p>
            <a href="{{ route('latihan.index') }}" wire:navigate class="inline-block px-4 py-2 rounded-xl bg-[#F9B92F] text-xs font-extrabold text-[#1C1C1E]">
                Kembali
            </a>
        </div>
    @elseif ($isFinished)
        {{-- Layar Selesai Kuis --}}
        @php
            $totalQuestions = $this->quiz->questions->count();
            $percentage = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;
            $isPassed = $percentage >= (float) $this->quiz->passing_score;
        @endphp

        <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-3xl p-8 sm:p-12 text-center space-y-6 shadow-sm">
            <div class="w-24 h-24 mx-auto rounded-3xl {{ $isPassed ? 'bg-[#F0FDF4] border-2 border-[#86EFAC]' : 'bg-[#FFF8E6] border-2 border-[#F9B92F]' }} flex items-center justify-center text-5xl">
                {{ $isPassed ? '🎉' : '💪' }}
            </div>

            <div class="space-y-2">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1C1C1E]">
                    {{ $isPassed ? 'Luar Biasa, Kuis Selesai!' : 'Tetap Semangat, Coba Lagi!' }}
                </h2>
                <p class="text-sm font-medium text-[#71717A]">
                    Kamu telah menyelesaikan paket <span class="font-extrabold text-[#1C1C1E]">{{ $this->quiz->title }}</span>
                </p>
            </div>

            {{-- Stat Box --}}
            <div class="grid grid-cols-3 gap-3 max-w-md mx-auto pt-2">
                <div class="p-4 rounded-2xl bg-[#FAFAFA] border border-[#E4E4E7] space-y-1">
                    <span class="text-[10px] font-extrabold uppercase text-[#71717A]">Benar</span>
                    <p class="text-xl font-extrabold text-[#22B05B]">{{ $correctCount }}/{{ $totalQuestions }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-[#FAFAFA] border border-[#E4E4E7] space-y-1">
                    <span class="text-[10px] font-extrabold uppercase text-[#71717A]">Akurasi</span>
                    <p class="text-xl font-extrabold text-[#1C1C1E]">{{ $percentage }}%</p>
                </div>
                <div class="p-4 rounded-2xl bg-[#FAFAFA] border border-[#E4E4E7] space-y-1">
                    <span class="text-[10px] font-extrabold uppercase text-[#71717A]">XP Didapat</span>
                    <p class="text-xl font-extrabold text-[#C98A06]">+{{ $isPassed ? $this->quiz->xp_reward : round($this->quiz->xp_reward * ($percentage / 100)) }}</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4">
                <button
                    type="button"
                    wire:click="retry"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl border-2 border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold text-[#52525B] transition-all cursor-pointer"
                >
                    🔄 Ulangi Kuis
                </button>
                <a
                    href="{{ route('latihan.index') }}"
                    wire:navigate
                    class="w-full sm:w-auto px-8 py-3 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] transition-all shadow-xs"
                >
                    Pilih Kuis Lain →
                </a>
            </div>
        </div>
    @else
        {{-- Player Soal Kuis Aktif --}}
        @php
            $q = $this->currentQuestion;
            $totalQuestions = $this->quiz->questions->count();
            $progressPercent = $totalQuestions > 0 ? round((($currentIndex + 1) / $totalQuestions) * 100) : 0;
            $media = $q?->getEffectiveMediaUrl();
            $isImgOrGif = $media ? preg_match('/\.(gif|png|jpe?g|webp|svg)$/i', $media) : false;
        @endphp

        {{-- Progress Bar --}}
        <div class="space-y-1.5">
            <div class="flex items-center justify-between text-xs font-extrabold text-[#71717A]">
                <span>Pertanyaan {{ $currentIndex + 1 }} dari {{ $totalQuestions }}</span>
                <span>{{ $progressPercent }}%</span>
            </div>
            <div class="w-full h-3 rounded-full bg-[#E4E4E7] overflow-hidden p-0.5">
                <div class="h-full rounded-full bg-[#F9B92F] transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>

        {{-- Kartu Soal --}}
        <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xs">
            {{-- Pertanyaan Header --}}
            <div class="space-y-2">
                <span class="inline-block px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wide bg-[#F4F4F5] text-[#52525B]">
                    @if ($q->question_type === 'sign_to_word')
                        🎬 Tebak Kata dari Isyarat
                    @elseif ($q->question_type === 'word_to_sign')
                        🔤 Cari Gerakan Isyarat
                    @elseif ($q->question_type === 'media_to_sign')
                        🖼️ Tebak Isyarat dari Gambar
                    @else
                        Pilihan Ganda
                    @endif
                </span>

                <h2 class="text-lg sm:text-xl font-extrabold text-[#1C1C1E] leading-snug">
                    {{ $q->question_text }}
                </h2>
            </div>

            {{-- Media Soal (Jika Tipe A atau B atau ada media URL) --}}
            @if (!empty($media))
                <div class="rounded-2xl overflow-hidden bg-[#18181B] aspect-video max-h-72 flex items-center justify-center p-2 shadow-inner">
                    @if ($isImgOrGif)
                        <img src="{{ $media }}" alt="Peraga Kuis" class="h-full object-contain" />
                    @else
                        <video src="{{ $media }}" controls autoplay loop muted playsinline class="h-full object-contain"></video>
                    @endif
                </div>
            @endif

            {{-- Pilihan Jawaban A, B, C, D --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                @foreach ($q->options as $idx => $opt)
                    @php
                        $isSelected = $selectedOptionId === $opt->id;
                        $optText = $opt->getEffectiveText();
                        $optMedia = $opt->getEffectiveMediaUrl();
                        $optIsImg = $optMedia ? preg_match('/\.(gif|png|jpe?g|webp|svg)$/i', $optMedia) : false;

                        // State styling saat jawaban dicek
                        $borderClass = 'border-[#E4E4E7] hover:border-[#F9B92F] bg-white';
                        if ($isAnswerChecked) {
                            if ($opt->is_correct) {
                                $borderClass = 'border-[#22B05B] bg-[#F0FDF4] text-[#15803D] ring-2 ring-[#22B05B]';
                            } elseif ($isSelected && ! $opt->is_correct) {
                                $borderClass = 'border-[#EF4444] bg-[#FEF2F2] text-[#B91C1C] ring-2 ring-[#EF4444]';
                            } else {
                                $borderClass = 'border-[#E4E4E7] bg-zinc-50 opacity-50';
                            }
                        } elseif ($isSelected) {
                            $borderClass = 'border-[#F9B92F] bg-[#FFF8E6] text-[#1C1C1E] ring-2 ring-[#F9B92F] shadow-xs';
                        }
                    @endphp

                    <button
                        type="button"
                        wire:click="selectOption({{ $opt->id }})"
                        class="p-4 rounded-2xl border-2 text-left transition-all cursor-pointer flex flex-col justify-between gap-3 {{ $borderClass }}"
                        {{ $isAnswerChecked ? 'disabled' : '' }}
                    >
                        @if ($optMedia)
                            {{-- Jika Opsi Jawaban Berupa Media (Tipe C atau A) --}}
                            <div class="w-full aspect-video rounded-xl overflow-hidden bg-[#18181B] flex items-center justify-center p-1">
                                @if ($optIsImg)
                                    <img src="{{ $optMedia }}" class="h-full object-contain" />
                                @else
                                    <video src="{{ $optMedia }}" controls muted loop class="h-full object-contain"></video>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-extrabold {{ $isSelected ? 'bg-[#F9B92F] text-[#1C1C1E]' : 'bg-[#F4F4F5] text-[#52525B]' }}">
                                {{ chr(65 + $idx) }}
                            </span>
                            <span class="text-sm font-extrabold flex-1 leading-snug">
                                {{ $optText }}
                            </span>

                            @if ($isAnswerChecked && $opt->is_correct)
                                <span class="text-base text-[#22B05B]">✓</span>
                            @elseif ($isAnswerChecked && $isSelected && ! $opt->is_correct)
                                <span class="text-base text-[#EF4444]">✕</span>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>

            {{-- Action Bottom Bar --}}
            <div class="pt-4 border-t border-[#E4E4E7] flex items-center justify-between">
                @if ($isAnswerChecked)
                    <div class="flex items-center gap-2">
                        @if ($isCurrentCorrect)
                            <span class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-extrabold text-[#15803D]">
                                <span>🎉</span> <span>Jawaban Benar!</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-extrabold text-[#B91C1C]">
                                <span>✕</span> <span>Kurang tepat, perhatikan kunci jawaban di atas.</span>
                            </span>
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="nextQuestion"
                        class="px-6 py-2.5 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all cursor-pointer shadow-xs ml-auto"
                    >
                        {{ $currentIndex + 1 < $totalQuestions ? 'Soal Berikutnya →' : 'Lihat Hasil Kuis →' }}
                    </button>
                @else
                    <div class="text-xs text-[#71717A] font-medium">
                        Pilih satu opsi jawaban untuk melanjutkan.
                    </div>

                    <button
                        type="button"
                        wire:click="checkAnswer"
                        @disabled(! $selectedOptionId)
                        class="px-6 py-2.5 rounded-xl text-xs sm:text-sm font-extrabold transition-all cursor-pointer shadow-xs ml-auto {{ $selectedOptionId ? 'bg-[#22B05B] hover:bg-[#1ea052] border-2 border-b-4 border-[#15803D] active:translate-y-0.5 active:border-b-2 text-white' : 'bg-zinc-100 text-zinc-400 border-2 border-zinc-200 cursor-not-allowed' }}"
                    >
                        Periksa Jawaban
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
