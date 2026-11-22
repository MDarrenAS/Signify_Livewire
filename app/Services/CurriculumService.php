<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleLesson;
use App\Models\User;
use App\Models\UserLessonProgress;

class CurriculumService
{
    /**
     * Predefined unit / level metadata.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $levels = [
        'beginner' => [
            'id' => 1,
            'unit_number' => 1,
            'level' => 'beginner',
            'badge' => 'UNIT 1 • TINGKAT DASAR',
            'title' => 'Pondasi Komunikasi BISINDO',
            'description' => 'Kuasai abjad visual, angka dasar, dan sapaan penting untuk memulai percakapan.',
        ],
        'intermediate' => [
            'id' => 2,
            'unit_number' => 2,
            'level' => 'intermediate',
            'badge' => 'UNIT 2 • TINGKAT MENENGAH',
            'title' => 'Percakapan Sehari-hari',
            'description' => 'Perluas kosakata untuk komunikasi di lingkungan sosial dan publik.',
        ],
        'advanced' => [
            'id' => 3,
            'unit_number' => 3,
            'level' => 'advanced',
            'badge' => 'UNIT 3 • TINGKAT MAHIR',
            'title' => 'Kefasihan & Kecepatan Berisyarat',
            'description' => 'Tingkatkan akurasi ekspresi wajah, struktur kalimat, dan kecepatan gerak.',
        ],
    ];

    /**
     * Get units structured for the user-facing learning pages with progress computed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUnitsForUser(?User $user): array
    {
        $modules = Module::query()
            ->where('status', '!=', 'archived')
            ->with([
                'lessons' => fn ($query) => $query->where('status', '!=', 'archived')->orderBy('sort_order')->orderBy('id'),
                'lessons.libraryItems.media',
                'lessons.quiz.questions.options',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $completedLessonIds = [];
        $quizScores = [];

        if ($user) {
            $userProgress = UserLessonProgress::query()
                ->where('user_id', $user->id)
                ->get();

            $completedLessonIds = $userProgress->where('status', 'completed')->pluck('lesson_id')->all();
            $quizScores = $userProgress->pluck('progress_percent', 'lesson_id')->all();
        }

        $sessionCompletedKeys = (array) session('completed_parts', []);
        $sessionQuizScores = (array) session('quiz_scores', []);

        $grouped = $modules->groupBy(fn (Module $m) => strtolower($m->level ?? 'beginner'));

        $units = [];
        $prevModulDone = true;

        foreach ($this->levels as $levelKey => $meta) {
            $levelModules = $grouped->get($levelKey, collect());
            if ($levelModules->isEmpty()) {
                continue;
            }

            $mappedModules = $levelModules->map(function (Module $modul) use (
                $completedLessonIds,
                $quizScores,
                $sessionCompletedKeys,
                $sessionQuizScores,
                &$prevModulDone
            ) {
                $lessons = $modul->lessons;

                $parts = $lessons->map(function (ModuleLesson $lesson) use (
                    $modul,
                    $completedLessonIds,
                    $quizScores,
                    $sessionCompletedKeys,
                    $sessionQuizScores
                ) {
                    $sessionKey = $modul->slug.':'.$lesson->slug;
                    $isDone = in_array($lesson->id, $completedLessonIds, true)
                        || in_array($sessionKey, $sessionCompletedKeys, true);

                    $score = $quizScores[$lesson->id] ?? ($sessionQuizScores[$sessionKey] ?? null);

                    $badgeText = $lesson->badge_text;
                    if ($isDone && $lesson->lesson_type === 'quiz' && $score !== null) {
                        $badgeText = (int) $score >= 100 ? 'Skor 100 • Sempurna' : "Skor {$score}";
                    }

                    return [
                        'id' => $lesson->id,
                        'order' => $lesson->sort_order,
                        'type' => $lesson->lesson_type,
                        'slug' => $lesson->slug,
                        'title' => $lesson->title,
                        'subtitle' => $lesson->subtitle,
                        'category' => $lesson->category,
                        'badge_text' => $badgeText,
                        'status' => $isDone ? 'completed' : 'available',
                        'xp_reward' => $lesson->xp_reward,
                        'passing_score' => $lesson->passing_score,
                    ];
                });

                $wasLocked = $modul->status !== 'published';
                $isLocked = $wasLocked || ! $prevModulDone;
                $totalParts = $parts->count();
                $doneCount = $parts->where('status', 'completed')->count();
                $isModulDone = $totalParts > 0 && $doneCount === $totalParts;
                $isInProgress = ! $isLocked && $doneCount > 0 && ! $isModulDone;

                $prevModulDone = ! $isLocked && $isModulDone;

                return [
                    'id' => $modul->id,
                    'slug' => $modul->slug,
                    'title' => $modul->title,
                    'icon' => $modul->icon,
                    'icon_url' => $modul->thumbnail_path,
                    'subtitle' => $modul->description,
                    'description' => $isLocked
                        ? ($modul->description ?? 'Terkunci • Selesaikan modul sebelumnya terlebih dahulu')
                        : ($modul->description ?? 'Kuasai materi '.$modul->title.' dalam isyarat BISINDO.'),
                    'xp_reward' => 70,
                    'visual_content' => $modul->title,
                    'tag' => (! $isLocked && ! $isModulDone) ? 'SEDANG AKTIF' : null,
                    'status' => $isLocked ? 'locked' : ($isModulDone ? 'completed' : null),
                    'total_parts' => $totalParts,
                    'is_expanded' => $isInProgress || (! $isLocked && ! $isModulDone),
                    'parts' => $isLocked ? [] : $parts->all(),
                ];
            });

            $units[] = [
                'id' => $meta['id'],
                'unit_number' => $meta['unit_number'],
                'level' => $meta['level'],
                'badge' => $meta['badge'],
                'title' => $meta['title'],
                'description' => $meta['description'],
                'is_locked' => $mappedModules->isNotEmpty() && $mappedModules->every(fn (array $m) => ($m['status'] ?? '') === 'locked'),
                'modules' => $mappedModules->all(),
            ];
        }

        return $units;
    }

    /**
     * Get units structured for the admin management panel.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUnitsForAdmin(): array
    {
        $modules = Module::query()
            ->with([
                'lessons' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'lessons.libraryItems.media',
                'lessons.quiz.questions.options',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $grouped = $modules->groupBy(fn (Module $m) => strtolower($m->level ?? 'beginner'));

        $units = [];
        foreach ($this->levels as $levelKey => $meta) {
            $levelModules = $grouped->get($levelKey, collect());

            $mappedModules = $levelModules->map(function (Module $modul) {
                return [
                    'id' => $modul->id,
                    'slug' => $modul->slug,
                    'title' => $modul->title,
                    'icon' => $modul->icon,
                    'icon_url' => $modul->thumbnail_path,
                    'subtitle' => $modul->description,
                    'description' => $modul->description,
                    'xp_reward' => 70,
                    'level' => $modul->level,
                    'status' => $modul->status === 'published' ? 'published' : 'draft',
                    'is_active' => $modul->status === 'published',
                    'total_parts' => $modul->lessons->count(),
                    'parts' => $modul->lessons->map(fn (ModuleLesson $l) => [
                        'id' => $l->id,
                        'order' => $l->sort_order,
                        'type' => $l->lesson_type,
                        'slug' => $l->slug,
                        'title' => $l->title,
                        'subtitle' => $l->subtitle,
                        'category' => $l->category,
                        'badge_text' => $l->badge_text,
                        'status' => $l->status,
                        'xp_reward' => $l->xp_reward,
                        'passing_score' => $l->passing_score,
                    ])->all(),
                ];
            });

            $units[] = [
                'id' => $meta['id'],
                'unit_number' => $meta['unit_number'],
                'level' => $meta['level'],
                'badge' => $meta['badge'],
                'title' => $meta['title'],
                'description' => $meta['description'],
                'modules' => $mappedModules->all(),
            ];
        }

        return $units;
    }

    /**
     * Get a single module with its lessons, items, and questions.
     *
     * @return array<string, mixed>|null
     */
    public function getModuleWithParts(string $slug): ?array
    {
        $module = Module::query()
            ->where('slug', $slug)
            ->with([
                'lessons' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
                'lessons.libraryItems.media',
                'lessons.quiz.questions.options',
            ])
            ->first();

        if (! $module) {
            return null;
        }

        return [
            'id' => $module->id,
            'title' => $module->title,
            'slug' => $module->slug,
            'description' => $module->description,
            'level' => $module->level,
            'icon' => $module->icon,
            'icon_url' => $module->thumbnail_path,
            'xp_reward' => 70,
            'status' => $module->status,
            'parts' => $module->lessons->map(function (ModuleLesson $lesson) {
                return [
                    'id' => $lesson->id,
                    'order' => $lesson->sort_order,
                    'type' => $lesson->lesson_type,
                    'slug' => $lesson->slug,
                    'title' => $lesson->title,
                    'subtitle' => $lesson->subtitle,
                    'category' => $lesson->category,
                    'badge_text' => $lesson->badge_text,
                    'xp_reward' => $lesson->xp_reward,
                    'passing_score' => $lesson->passing_score,
                    'items' => $lesson->items,
                    'questions' => $lesson->questions,
                ];
            })->all(),
        ];
    }
}
