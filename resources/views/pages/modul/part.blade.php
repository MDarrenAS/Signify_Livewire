<?php

use App\Support\MockCurriculum;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::lessons')] class extends Component
{
    public string $modulSlug;
    public string $bagianSlug;

    public array $modulData = [];
    public array $partData = [];
    public array $items = [];
    public array $questions = [];

    public int $partOrder = 1;
    public int $totalParts = 1;
    public ?array $nextPart = null;

    public bool $isResultModalOpen = false;
    public bool $isPassed = true;
    public int $finalScore = 0;

    public function mount(string $modul, string $bagian): void
    {
        $this->modulSlug  = $modul;
        $this->bagianSlug = $bagian;

        $foundModul = collect(MockCurriculum::getUnits())
            ->flatMap(fn (array $unit) => $unit['modules'] ?? [])
            ->firstWhere('slug', $modul);

        abort_if(! $foundModul, 404);

        $parts     = collect($foundModul['parts'] ?? [])->values();
        $partIndex = $parts->search(fn (array $p) => ($p['slug'] ?? '') === $bagian);

        abort_if($partIndex === false, 404);

        $foundPart = $parts->get($partIndex);

        $this->modulData  = $foundModul;
        $this->partData   = $foundPart;
        $this->items      = array_values($foundPart['items'] ?? []);
        $this->questions  = array_values($foundPart['questions'] ?? []);
        $this->partOrder  = $foundPart['order'] ?? ($partIndex + 1);
        $this->totalParts = $parts->count();
        $this->nextPart   = $parts->get($partIndex + 1);
    }

    public function complete(): void
    {
        $this->markSessionCompleted();
        $this->isPassed          = true;
        $this->isResultModalOpen = true;
    }

    public function completeQuiz(int $score): void
    {
        $passingScore     = (int) ($this->partData['passing_score'] ?? 80);
        $this->finalScore = max(0, min(100, $score));
        $this->isPassed   = $this->finalScore >= $passingScore;

        if ($this->isPassed) {
            $this->markSessionCompleted($this->finalScore);
        }

        $this->isResultModalOpen = true;
    }

    public function retryQuiz(): void
    {
        $this->isResultModalOpen = false;
    }

    private function markSessionCompleted(?int $quizScore = null): void
    {
        $completedParts = (array) session('completed_parts', []);
        $partKey        = $this->modulSlug . ':' . $this->bagianSlug;

        if (! in_array($partKey, $completedParts, true)) {
            $completedParts[] = $partKey;
            $xpGain           = (int) ($this->partData['xp_reward'] ?? 0);

            session([
                'completed_parts' => $completedParts,
                'session_xp'      => (int) session('session_xp', 0) + $xpGain,
            ]);
        }

        if ($quizScore !== null) {
            $quizScores           = (array) session('quiz_scores', []);
            $quizScores[$partKey] = max((int) ($quizScores[$partKey] ?? 0), $quizScore);

            session(['quiz_scores' => $quizScores]);
        }
    }
};
?>

@php
    $isQuiz      = ($partData['type'] ?? 'lesson') === 'quiz';
    $modulUrl    = route('modul.index');
    $nextPartUrl = $nextPart
        ? route('modul.part', ['modul' => $modulSlug, 'bagian' => $nextPart['slug']])
        : $modulUrl;

    $headerTitle = strtoupper(($partData['title'] ?? '') . (! empty($partData['subtitle']) ? ' (' . $partData['subtitle'] . ')' : ''));
    $alreadyDone = in_array($modulSlug . ':' . $bagianSlug, (array) session('completed_parts', []), true);
@endphp

@if (! $isQuiz)
    <div
        x-data="{
            items: @js($items),
            xpReward: {{ (int) ($partData['xp_reward'] ?? 0) }},
            activeIndex: 0,
            completedIndices: {{ $alreadyDone ? json_encode(array_keys($items)) : '[]' }},

            get currentItem() {
                return this.items[this.activeIndex] || {};
            },
            get isLastItem() {
                return this.activeIndex >= this.items.length - 1;
            },
            get progressPercent() {
                return this.items.length
                    ? Math.round(((this.activeIndex + 1) / this.items.length) * 100)
                    : 100;
            },
            get nextButtonLabel() {
                if (this.isLastItem) {
                    return 'Selesai & Klaim +' + this.xpReward + ' XP ✓';
                }
                const nextTitle = this.items[this.activeIndex + 1]?.title ?? 'Berikutnya';
                return 'Lanjut ' + nextTitle + ' →';
            },
            selectItem(idx) {
                this.activeIndex = idx;
            },
            prevItem() {
                if (this.activeIndex > 0) {
                    this.activeIndex--;
                }
            },
            nextOrComplete() {
                if (!this.completedIndices.includes(this.activeIndex)) {
                    this.completedIndices.push(this.activeIndex);
                }

                if (!this.isLastItem) {
                    this.activeIndex++;
                } else {
                    $wire.complete();
                }
            }
        }"
        class="min-h-screen flex flex-col bg-[#FAFAFA]"
    >
        <x-lessons.header
            :close-url="$modulUrl"
            :part-order="$partOrder"
            :total-parts="$totalParts"
            :part-title="$headerTitle"
            :xp-reward="$partData['xp_reward'] ?? 0"
        />

        <x-lessons.sign-tabs />

        <main class="flex-1 max-w-[1200px] w-full mx-auto px-4 sm:px-8 py-5 sm:py-7">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                <div class="lg:col-span-7">
                    <x-lessons.video-player />
                </div>

                <div class="lg:col-span-5 space-y-4">
                    <x-lessons.instruction-card :category="$partData['category'] ?? 'BISINDO'" />
                    <x-lessons.mascot-tip />
                </div>
            </div>
        </main>

        <x-lessons.footer />
    </div>
@else
    <div
        x-data="{
            questions: @js($questions),
            quizIndex: 0,
            selectedOption: null,
            answered: false,
            correctCount: 0,
            quizVideoError: false,

            get currentQuestion() {
                return this.questions[this.quizIndex] || { options: [] };
            },
            get progressPercent() {
                return this.questions.length
                    ? Math.round(((this.quizIndex + (this.answered ? 1 : 0)) / this.questions.length) * 100)
                    : 100;
            },
            get scorePercent() {
                return this.questions.length
                    ? Math.round((this.correctCount / this.questions.length) * 100)
                    : 0;
            },
            selectOption(idx) {
                if (!this.answered) this.selectedOption = idx;
            },
            checkAnswer() {
                if (this.selectedOption === null || this.answered) return;
                this.answered = true;
                if (this.selectedOption === this.currentQuestion.correct_index) {
                    this.correctCount++;
                }
            },
            nextQuestion() {
                if (this.quizIndex < this.questions.length - 1) {
                    this.quizIndex++;
                    this.selectedOption = null;
                    this.answered = false;
                    this.quizVideoError = false;
                } else {
                    $wire.completeQuiz(this.scorePercent);
                }
            },
            resetQuiz() {
                this.quizIndex = 0;
                this.selectedOption = null;
                this.answered = false;
                this.correctCount = 0;
                this.quizVideoError = false;
            }
        }"
        @quiz-retry.window="resetQuiz()"
        class="min-h-screen flex flex-col bg-[#FAFAFA]"
    >
        <x-lessons.header
            :close-url="$modulUrl"
            :part-order="$partOrder"
            :total-parts="$totalParts"
            :part-title="$headerTitle"
            :xp-reward="$partData['xp_reward'] ?? 0"
        />

        <x-quiz.view />
    </div>
@endif

{{-- MODAL HASIL BAGIAN / KUIS --}}
@if ($isResultModalOpen)
    <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-3xl max-w-md w-full p-6 sm:p-8 text-center space-y-5 shadow-xl">
            <div class="w-16 h-16 mx-auto rounded-2xl {{ $isPassed ? 'bg-[#FFF8E6] border-[#F9B92F]' : 'bg-[#FEF2F2] border-[#EF4444]' }} border-2 border-b-4 flex items-center justify-center text-3xl">
                {{ $isPassed ? '🎉' : '💪' }}
            </div>

            <div class="space-y-1.5">
                <h2 class="text-2xl font-extrabold text-[#1C1C1E]">
                    {{ $isPassed ? 'Bagian Selesai!' : 'Belum Lulus Kuis' }}
                </h2>

                @if ($isPassed)
                    <p class="text-sm text-[#52525B] font-medium">
                        Kamu menyelesaikan <strong class="text-[#1C1C1E]">{{ $partData['title'] ?? '' }}</strong>
                        @if ($isQuiz)
                            dengan skor <strong class="text-[#15803D]">{{ $finalScore }}%</strong>
                        @endif
                        dan meraih <strong class="text-[#C98A06]">+{{ $partData['xp_reward'] ?? 0 }} XP</strong>.
                    </p>
                @else
                    <p class="text-sm text-[#52525B] font-medium">
                        Skor kamu <strong class="text-[#B91C1C]">{{ $finalScore }}%</strong>
                        (minimal lulus <strong class="text-[#1C1C1E]">{{ $partData['passing_score'] ?? 80 }}%</strong>). Coba ulangi lagi!
                    </p>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <x-ui.button variant="secondary" :href="$modulUrl" wire:navigate class="flex-1">
                    Daftar Modul
                </x-ui.button>

                @if (! $isPassed)
                    <x-ui.button
                        variant="primary"
                        wire:click="retryQuiz"
                        x-on:click="$dispatch('quiz-retry')"
                        class="flex-1"
                    >
                        Coba Lagi ↻
                    </x-ui.button>
                @elseif ($nextPart)
                    <x-ui.button variant="primary" :href="$nextPartUrl" wire:navigate class="flex-1">
                        Bagian {{ $nextPart['order'] ?? ($partOrder + 1) }} →
                    </x-ui.button>
                @endif
            </div>
        </div>
    </div>
@endif