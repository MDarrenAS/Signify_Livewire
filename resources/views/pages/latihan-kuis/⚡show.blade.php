
<?php

use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kerjakan Kuis')] class extends Component
{
    public string $slug = '';
    public array $package = [];
    public int $questionIndex = 0;
    public ?int $selectedOption = null;
    public bool $answered = false;
    public bool $finished = false;
    public int $correctAnswers = 0;

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $packages = session('admin_quiz_packages', []);

        $package = collect($packages)->first(function ($item) use ($slug) {
            return Str::slug($item['title'] ?? '') === $slug
                && ($item['is_active'] ?? false)
                && count($item['questions'] ?? []) > 0;
        });

        abort_if(!$package, 404);

        $this->package = $package;
    }

    public function selectOption(int $index): void
    {
        if ($this->answered || $this->finished) {
            return;
        }

        if (!isset($this->package['questions'][$this->questionIndex]['options'][$index])) {
            return;
        }

        $this->selectedOption = $index;
    }

    public function checkAnswer(): void
    {
        if ($this->selectedOption === null || $this->answered) {
            return;
        }

        $question = $this->package['questions'][$this->questionIndex];

        if ($this->selectedOption === (int) ($question['correct_index'] ?? -1)) {
            $this->correctAnswers++;
        }

        $this->answered = true;
    }

    public function nextQuestion(): void
    {
        if (!$this->answered) {
            return;
        }

        if ($this->questionIndex < count($this->package['questions']) - 1) {
            $this->questionIndex++;
            $this->selectedOption = null;
            $this->answered = false;
        } else {
            $this->finished = true;
        }
    }

    public function getScoreProperty(): int
    {
        $total = count($this->package['questions'] ?? []);

        return $total > 0
            ? (int) round(($this->correctAnswers / $total) * 100)
            : 0;
    }

    public function restartQuiz(): void
    {
        $this->questionIndex = 0;
        $this->selectedOption = null;
        $this->answered = false;
        $this->finished = false;
        $this->correctAnswers = 0;
    }
};
?>

<div class="mx-auto max-w-4xl space-y-6 pb-10">

    {{-- HEADER --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('latihan.index') }}" wire:navigate
            class="inline-flex items-center gap-2 text-sm font-bold text-[#828282] transition hover:text-[#1C1C1E]">
            <span>←</span>
            Kembali
        </a>

        <span class="rounded-full bg-[#FFF3D5] px-3 py-1.5 text-xs font-extrabold text-[#C98A06]">
            +{{ $package['xp_reward'] ?? 0 }} XP
        </span>
    </div>

    {{-- QUIZ CONTENT --}}
    @if (!$finished)

        @php
            $questions = $package['questions'];
            $question = $questions[$questionIndex];
            $totalQuestions = count($questions);
            $progress = (($questionIndex + 1) / $totalQuestions) * 100;
            $mediaUrl = $question['video_url'] ?? '';
            $extension = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $isVideo = in_array($extension, ['mp4', 'webm']);
        @endphp

        {{-- PACKAGE INFO --}}
        <div class="space-y-2">
            <p class="text-xs font-bold uppercase tracking-wider text-[#C98A06]">
                {{ $package['level'] ?? 'Pemula' }}
            </p>

            <h1 class="text-xl font-extrabold text-[#1C1C1E] sm:text-2xl">
                {{ $package['title'] }}
            </h1>

            <p class="text-sm text-[#828282]">
                {{ $package['subtitle'] ?? '' }}
            </p>
        </div>

        {{-- PROGRESS --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="text-[#52525B]">
                    Pertanyaan {{ $questionIndex + 1 }} dari {{ $totalQuestions }}
                </span>

                <span class="text-[#C98A06]">
                    {{ (int) $progress }}%
                </span>
            </div>

            <div class="h-2.5 overflow-hidden rounded-full bg-[#F0F0F0]">
                <div class="h-full rounded-full bg-[#F9B92F] transition-all duration-300"
                    style="width: {{ $progress }}%">
                </div>
            </div>
        </div>

        {{-- QUESTION CARD --}}
        <section class="space-y-6 rounded-2xl border-2 border-b-4 border-[#E4E4E7] bg-white p-5 sm:p-8">

            <div class="space-y-3">
                <span class="text-xs font-extrabold uppercase tracking-wider text-[#C98A06]">
                    Pertanyaan
                </span>

                <h2 class="text-base font-extrabold leading-relaxed text-[#1C1C1E] sm:text-lg">
                    {{ $question['question'] }}
                </h2>
            </div>

            {{-- MEDIA --}}
            @if ($mediaUrl)
                <div class="overflow-hidden rounded-2xl border-2 border-[#E4E4E7] bg-[#FAFAFA]">
                    @if ($isVideo)
                        <video controls preload="metadata" class="max-h-[350px] w-full object-contain">
                            <source src="{{ $mediaUrl }}" type="video/{{ $extension }}">
                            Browser tidak mendukung video.
                        </video>
                    @else
                        <img src="{{ $mediaUrl }}"
                            alt="Media pertanyaan"
                            class="mx-auto max-h-[350px] w-full object-contain">
                    @endif
                </div>
            @endif

            {{-- ANSWER OPTIONS --}}
            <div class="space-y-3">
                <p class="text-sm font-extrabold text-[#52525B]">
                    Pilih jawaban yang benar:
                </p>

                @foreach ($question['options'] as $index => $option)
                    @php
                        $isCorrect = $index === (int) ($question['correct_index'] ?? -1);
                        $isSelected = $selectedOption === $index;
                    @endphp

                    <button type="button"
                        wire:click="selectOption({{ $index }})"
                        @disabled($answered)
                        @class([
                            'flex w-full items-center gap-3 rounded-xl border-2 p-4 text-left transition-all',
                            'border-[#F9B92F] bg-[#FFF8E6]' => $isSelected && !$answered,
                            'border-[#E4E4E7] bg-white hover:border-[#F9B92F]' => !$isSelected && !$answered,
                            'border-[#86EFAC] bg-[#F0FDF4]' => $answered && $isCorrect,
                            'border-[#FCA5A5] bg-[#FEF2F2]' => $answered && $isSelected && !$isCorrect,
                            'opacity-70' => $answered && !$isCorrect && !$isSelected,
                            'cursor-pointer' => !$answered,
                            'cursor-default' => $answered,
                        ])>

                        <span @class([
                            'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-extrabold',
                            'bg-[#F9B92F] text-[#1C1C1E]' => $isSelected && !$answered,
                            'bg-[#F4F4F5] text-[#52525B]' => !$isSelected && !$answered,
                            'bg-[#DCFCE7] text-[#15803D]' => $answered && $isCorrect,
                            'bg-[#FEE2E2] text-[#B91C1C]' => $answered && $isSelected && !$isCorrect,
                        ])>
                            {{ chr(65 + $index) }}
                        </span>

                        <span class="flex-1 text-sm font-bold text-[#1C1C1E]">
                            {{ $option }}
                        </span>

                        @if ($answered && $isCorrect)
                            <span class="font-extrabold text-[#15803D]">✓</span>
                        @elseif ($answered && $isSelected && !$isCorrect)
                            <span class="font-extrabold text-[#B91C1C]">✕</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- FEEDBACK --}}
            @if ($answered)
                @php
                    $correct = $selectedOption === (int) ($question['correct_index'] ?? -1);
                @endphp

                <div @class([
                    'rounded-xl border-2 p-4',
                    'border-[#86EFAC] bg-[#F0FDF4]' => $correct,
                    'border-[#FCA5A5] bg-[#FEF2F2]' => !$correct,
                ])>
                    <p @class([
                        'text-sm font-extrabold',
                        'text-[#15803D]' => $correct,
                        'text-[#B91C1C]' => !$correct,
                    ])>
                        {{ $correct ? 'Jawaban kamu benar!' : 'Belum tepat, yuk pelajari lagi!' }}
                    </p>

                    @if (!$correct)
                        <p class="mt-1 text-sm text-[#52525B]">
                            Jawaban yang benar:
                            <strong>{{ $question['options'][$question['correct_index']] ?? '-' }}</strong>
                        </p>
                    @endif
                </div>
            @endif

            {{-- ACTION --}}
            <div class="flex justify-end border-t border-[#E4E4E7] pt-5">
                @if (!$answered)
                    <button type="button"
                        wire:click="checkAnswer"
                        @disabled($selectedOption === null)
                        class="w-full rounded-xl border-2 border-b-4 border-[#C98A06] bg-[#F9B92F] px-6 py-3 text-sm font-extrabold text-[#1C1C1E] transition hover:bg-[#FAD078] disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        Periksa Jawaban
                    </button>
                @else
                    <button type="button"
                        wire:click="nextQuestion"
                        class="w-full rounded-xl border-2 border-b-4 border-[#C98A06] bg-[#F9B92F] px-6 py-3 text-sm font-extrabold text-[#1C1C1E] transition hover:bg-[#FAD078] sm:w-auto">
                        {{ $questionIndex === $totalQuestions - 1 ? 'Lihat Hasil' : 'Soal Berikutnya →' }}
                    </button>
                @endif
            </div>

        </section>

    @else

        {{-- FINAL RESULT --}}
        <section class="rounded-2xl border-2 border-b-4 border-[#E4E4E7] bg-white px-6 py-10 text-center sm:px-10">

            <div class="mx-auto mb-5 flex h-24 w-24 items-center justify-center rounded-full bg-[#FFF3D5] text-5xl">
                {{ $this->score >= 70 ? '🏆' : '💪' }}
            </div>

            <p class="text-xs font-extrabold uppercase tracking-widest text-[#C98A06]">
                Kuis Selesai
            </p>

            <h1 class="mt-2 text-2xl font-extrabold text-[#1C1C1E] sm:text-3xl">
                {{ $this->score >= 70 ? 'Kerja Bagus!' : 'Tetap Semangat!' }}
            </h1>

            <p class="mt-2 text-sm text-[#828282]">
                Kamu sudah menyelesaikan {{ $package['title'] }}.
            </p>

            <div class="my-8 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-[#FFF8E6] p-5">
                    <p class="text-3xl font-extrabold text-[#C98A06]">
                        {{ $this->score }}%
                    </p>
                    <p class="mt-1 text-xs font-bold text-[#828282]">Skor Akhir</p>
                </div>

                <div class="rounded-2xl bg-[#F0FDF4] p-5">
                    <p class="text-3xl font-extrabold text-[#15803D]">
                        {{ $correctAnswers }}/{{ count($package['questions']) }}
                    </p>
                    <p class="mt-1 text-xs font-bold text-[#828282]">Jawaban Benar</p>
                </div>
            </div>

            <p class="mb-6 text-sm text-[#828282]">
                {{ $this->score >= 70
                    ? 'Hebat! Kamu sudah memahami materi dengan baik.'
                    : 'Jangan menyerah. Coba pelajari kembali materi dan ulangi kuisnya.' }}
            </p>

            <div class="flex flex-col justify-center gap-3 sm:flex-row">
                <button type="button"
                    wire:click="restartQuiz"
                    class="rounded-xl border-2 border-b-4 border-[#C98A06] bg-[#F9B92F] px-6 py-3 text-sm font-extrabold text-[#1C1C1E] hover:bg-[#FAD078]">
                    Ulangi Kuis
                </button>

                <a href="{{ route('latihan.index') }}" wire:navigate
                    class="rounded-xl border-2 border-[#E4E4E7] bg-white px-6 py-3 text-sm font-extrabold text-[#52525B] hover:bg-[#FAFAFA]">
                    Kembali ke Paket
                </a>
            </div>

        </section>

    @endif

</div>
