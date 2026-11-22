<?php

use App\Models\Module;
use App\Models\ModuleLesson;
use App\Models\UserLessonProgress;
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

        $module = Module::where('slug', $modul)
            ->with([
                'lessons' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
                'lessons.libraryItems.media',
                'lessons.quiz.questions.options',
            ])
            ->first();

        abort_if(! $module, 404);

        $lessons = $module->lessons;
        $lessonIndex = $lessons->search(fn ($l) => $l->slug === $bagian);
        abort_if($lessonIndex === false, 404);

        $lesson = $lessons->get($lessonIndex);

        $allModules = Module::where('status', 'published')->orderBy('sort_order')->orderBy('id')->get();
        $currentModIndex = $allModules->search(fn ($m) => $m->id === $module->id);
        $nextModulModel = $currentModIndex !== false ? $allModules->get($currentModIndex + 1) : null;

        $this->modulData = [
            'id'    => $module->id,
            'slug'  => $module->slug,
            'title' => $module->title,
        ];

        $this->partData = [
            'id'            => $lesson->id,
            'slug'          => $lesson->slug,
            'order'         => $lesson->sort_order,
            'type'          => $lesson->type,
            'title'         => $lesson->title,
            'subtitle'      => $lesson->subtitle,
            'category'      => $lesson->category,
            'badge_text'    => $lesson->badge_text,
            'xp_reward'     => $lesson->xp_reward,
            'passing_score' => $lesson->passing_score,
        ];

        $this->items = $lesson->items;
        $this->questions = $lesson->questions;

        $this->partOrder  = $lesson->sort_order ?? ($lessonIndex + 1);
        $this->totalParts = $lessons->count();
        $nextLesson       = $lessons->get($lessonIndex + 1);
        $this->nextPart   = $nextLesson ? [
            'slug'  => $nextLesson->slug,
            'order' => $nextLesson->sort_order,
        ] : null;

        if ($nextModulModel) {
            $nextFirstLesson = $nextModulModel->lessons()->first();
            $this->nextModul = [
                'slug'  => $nextModulModel->slug,
                'parts' => $nextFirstLesson ? [['slug' => $nextFirstLesson->slug]] : [],
            ];
        } else {
            $this->nextModul = null;
        }
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

        if ($user = auth()->user()) {
            $lessonId = $this->partData['id'] ?? null;
            if (! $lessonId) {
                $lessonId = ModuleLesson::where('slug', $this->bagianSlug)->value('id');
            }

            if ($lessonId) {
                UserLessonProgress::updateOrCreate(
                    [
                        'user_id'   => $user->id,
                        'lesson_id' => $lessonId,
                    ],
                    [
                        'status'           => 'completed',
                        'progress_percent' => $quizScore !== null ? $quizScore : 100,
                        'completed_at'     => now(),
                        'last_accessed_at' => now(),
                    ]
                );
            }
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