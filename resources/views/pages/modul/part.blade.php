<?php

use App\Models\UserModuleProgress;
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
    public ?array $nextModul = null;

    public bool $isCompletedView = false;
    public bool $isPassed = true;
    public int $finalScore = 0;
    public int $correctCount = 0;

    public function mount(string $modul, string $bagian): void
    {
        $this->modulSlug  = $modul;
        $this->bagianSlug = $bagian;

        $allModules = collect(MockCurriculum::getUnits())
            ->flatMap(fn (array $unit) => $unit['modules'] ?? [])
            ->values();

        $modulIndex = $allModules->search(fn (array $m) => ($m['slug'] ?? '') === $modul);
        abort_if($modulIndex === false, 404);

        $foundModul = $allModules->get($modulIndex);
        $parts      = collect($foundModul['parts'] ?? [])->values();
        $partIndex  = $parts->search(fn (array $p) => ($p['slug'] ?? '') === $bagian);

        abort_if($partIndex === false, 404);

        $foundPart = $parts->get($partIndex);

        $this->modulData = $foundModul;
        $this->partData  = $foundPart;
        $this->items     = array_values($foundPart['items'] ?? []);

        $this->questions = collect($foundPart['questions'] ?? [])
            ->map(function (array $q): array {
                $options      = array_values($q['options'] ?? []);
                $correctIndex = $q['correct_index'] ?? $q['correct'] ?? $q['answer'] ?? 0;

                if (is_string($correctIndex) && ! is_numeric($correctIndex)) {
                    $foundIdx = collect($options)->search(function ($opt) use ($correctIndex) {
                        $label = is_array($opt) ? ($opt['label'] ?? $opt['text'] ?? '') : (string) $opt;
                        return strcasecmp(trim($label), trim($correctIndex)) === 0;
                    });
                    $correctIndex = $foundIdx !== false ? $foundIdx : 0;
                }

                $q['options']       = $options;
                $q['correct_index'] = (int) $correctIndex;

                return $q;
            })
            ->values()
            ->all();

        $this->partOrder  = $foundPart['order'] ?? ($partIndex + 1);
        $this->totalParts = $parts->count();
        $this->nextPart   = $parts->get($partIndex + 1);
        $this->nextModul  = $allModules->get($modulIndex + 1);
    }

    public function complete(): void
    {
        $this->markSessionCompleted();
        $this->isPassed        = true;
        $this->isCompletedView = true;
    }

    public function completeQuiz(int $score, int $correctCount = 0): void
    {
        $passingScore       = (int) ($this->partData['passing_score'] ?? 80);
        $this->finalScore   = max(0, min(100, $score));
        $this->correctCount = max(0, $correctCount);
        $this->isPassed     = $this->finalScore >= $passingScore;

        if ($this->isPassed) {
            $this->markSessionCompleted($this->finalScore);
        }

        $this->isCompletedView = true;
    }

    public function retryLesson(): void
    {
        $this->isCompletedView = false;
    }

    public function retryQuiz(): void
    {
        $this->isCompletedView = false;
        $this->finalScore      = 0;
        $this->correctCount    = 0;
    }

    private function markSessionCompleted(?int $quizScore = null): void
    {
        $partKey = $this->modulSlug . ':' . $this->bagianSlug;

        // 1. Simpan ke database jika user sudah login
        if (auth()->check()) {
            UserModuleProgress::updateOrCreate(
                [
                    'user_id'    => auth()->id(),
                    'modul_slug' => $this->modulSlug,
                    'part_slug'  => $this->bagianSlug,
                ],
                [
                    'part_type'    => $this->partData['type'] ?? 'lesson',
                    'score'        => $quizScore,
                    'is_completed' => true,
                    'completed_at' => now(),
                ]
            );
        }

        // 2. Simpan juga ke session untuk reaktivitas UI instan
        $completedParts = (array) session('completed_parts', []);

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
    $headerTitle = strtoupper(($partData['title'] ?? '') . (! empty($partData['subtitle']) ? ' (' . $partData['subtitle'] . ')' : ''));
    $alreadyDone = in_array($modulSlug . ':' . $bagianSlug, (array) session('completed_parts', []), true);

    $nextModulFirstPart = $nextModul['parts'][0] ?? null;

    if ($nextPart) {
        $nextUrl   = route('modul.part', ['modul' => $modulSlug, 'bagian' => $nextPart['slug']]);
        $nextLabel = 'Lanjut Bagian ' . ($nextPart['order'] ?? ($partOrder + 1)) . ' →';
    } elseif ($nextModul && $nextModulFirstPart) {
        $nextUrl   = route('modul.part', ['modul' => $nextModul['slug'], 'bagian' => $nextModulFirstPart['slug']]);
        $nextLabel = 'Lanjut Modul Berikutnya →';
    } else {
        $nextUrl   = $modulUrl;
        $nextLabel = 'Kembali ke Daftar Modul →';
    }
@endphp

<div class="min-h-screen flex flex-col bg-[#FAFAFA]">
    @if ($isCompletedView)
        <div wire:key="part-completed-{{ $modulSlug }}-{{ $bagianSlug }}" class="min-h-screen flex flex-col">
            <x-lessons.header
                :close-url="$modulUrl"
                :part-order="$partOrder"
                :total-parts="$totalParts"
                :part-title="$headerTitle"
                :xp-reward="$partData['xp_reward'] ?? 0"
                :is-completed="$isPassed"
            />

            <x-lessons.completed-view
                :is-quiz="$isQuiz"
                :is-passed="$isPassed"
                :part-order="$partOrder"
                :part-title="$partData['title'] ?? ''"
                :xp-reward="(int) ($partData['xp_reward'] ?? 0)"
                :items-count="count($items)"
                :final-score="$finalScore"
                :passing-score="(int) ($partData['passing_score'] ?? 80)"
                :correct-count="$correctCount"
                :total-questions="count($questions)"
                :modul-url="$modulUrl"
                :next-url="$nextUrl"
                :next-label="$nextLabel"
            />
        </div>
    @elseif (! $isQuiz)
        <div
            wire:key="part-lesson-{{ $modulSlug }}-{{ $bagianSlug }}"
            x-data="lessonPlayer({
                items: @js($items),
                xpReward: {{ (int) ($partData['xp_reward'] ?? 0) }},
                completedIndices: {{ $alreadyDone ? json_encode(array_keys($items)) : '[]' }}
            })"
            class="min-h-screen flex flex-col"
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
            wire:key="part-quiz-{{ $modulSlug }}-{{ $bagianSlug }}"
            x-data="quizPlayer({ questions: @js($questions) })"
            class="min-h-screen flex flex-col"
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
</div>