<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleLesson;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserLessonProgress;

class CurriculumService
{
    /**
     * Get units structured for the user-facing learning pages with progress computed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUnitsForUser(?User $user): array
    {
        $units = Unit::query()
            ->where('is_active', true)
            ->with([
                'modules' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id'),
                'modules.lessons' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
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

            $completedLessonIds = $userProgress->pluck('module_lesson_id')->all();
            $quizScores = $userProgress->pluck('score', 'module_lesson_id')->all();
        }

        $sessionCompletedKeys = (array) session('completed_parts', []);
        $sessionQuizScores = (array) session('quiz_scores', []);

        $prevModulDone = true;

        return $units->map(function (Unit $unit) use (
            $completedLessonIds,
            $quizScores,
            $sessionCompletedKeys,
            $sessionQuizScores,
            &$prevModulDone
        ) {
            $modules = $unit->modules->map(function (Module $modul) use (
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
                    if ($isDone && $lesson->type === 'quiz' && $score !== null) {
                        $badgeText = (int) $score >= 100 ? 'Skor 100 • Sempurna' : "Skor {$score}";
                    }

                    return [
                        'id' => $lesson->id,
                        'order' => $lesson->sort_order,
                        'type' => $lesson->type,
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

                $wasLocked = ! $modul->is_active;
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
                    'icon' => $modul->icon_emoji ?? '🤟',
                    'icon_url' => $modul->icon_url,
                    'subtitle' => $modul->subtitle,
                    'description' => $isLocked
                        ? ($modul->description ?? 'Terkunci • Selesaikan modul sebelumnya terlebih dahulu')
                        : ($modul->description ?? 'Kuasai materi '.$modul->title.' dalam isyarat BISINDO.'),
                    'xp_reward' => $modul->xp_reward,
                    'visual_content' => $modul->visual_content,
                    'tag' => (! $isLocked && ! $isModulDone) ? ($modul->tag ?? 'SEDANG AKTIF') : ($modul->tag ?? null),
                    'status' => $isLocked ? 'locked' : ($isModulDone ? 'completed' : null),
                    'total_parts' => $totalParts,
                    'is_expanded' => $isInProgress || (! $isLocked && ! $isModulDone),
                    'parts' => $isLocked ? [] : $parts->all(),
                ];
            });

            return [
                'id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'badge' => $unit->badge ?? ('UNIT '.$unit->unit_number.' • '.$unit->level),
                'title' => $unit->title,
                'description' => $unit->description,
                'is_locked' => $modules->isNotEmpty() && $modules->every(fn (array $m) => ($m['status'] ?? '') === 'locked'),
                'modules' => $modules->all(),
            ];
        })->all();
    }

    /**
     * Get units structured for the admin management panel.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUnitsForAdmin(): array
    {
        $units = Unit::query()
            ->with([
                'modules' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'modules.lessons' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $units->map(function (Unit $unit) {
            $modules = $unit->modules->map(function (Module $modul) {
                $parts = $modul->lessons->map(function (ModuleLesson $lesson) {
                    return [
                        'id' => $lesson->id,
                        'order' => $lesson->sort_order,
                        'type' => $lesson->type,
                        'slug' => $lesson->slug,
                        'title' => $lesson->title,
                        'subtitle' => $lesson->subtitle,
                        'category' => $lesson->category,
                        'badge_text' => $lesson->badge_text,
                        'status' => 'available',
                        'xp_reward' => $lesson->xp_reward,
                        'passing_score' => $lesson->passing_score,
                    ];
                });

                return [
                    'id' => $modul->id,
                    'slug' => $modul->slug,
                    'title' => $modul->title,
                    'subtitle' => $modul->subtitle,
                    'description' => $modul->description,
                    'icon' => $modul->icon_emoji ?? '🤟',
                    'icon_url' => $modul->icon_url ?? '',
                    'xp_reward' => $modul->xp_reward,
                    'status' => $modul->is_active ? 'active' : 'locked',
                    'total_parts' => $parts->count(),
                    'parts' => $parts->all(),
                ];
            });

            return [
                'id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'badge' => $unit->badge,
                'title' => $unit->title,
                'description' => $unit->description,
                'is_locked' => ! $unit->is_active,
                'modules' => $modules->all(),
            ];
        })->all();
    }

    /**
     * Get module with parts, items, and questions for editing in admin.
     *
     * @return array<string, mixed>|null
     */
    public function getModuleWithParts(string $modulSlug): ?array
    {
        $module = Module::query()
            ->where('slug', $modulSlug)
            ->with([
                'lessons' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'lessons.items' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'lessons.items.libraryItem.media',
                'lessons.questions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->first();

        if (! $module) {
            return null;
        }

        $parts = $module->lessons->map(function (ModuleLesson $lesson) {
            $items = $lesson->items->map(function ($item) {
                $videoUrl = $item->video_url;
                if ($item->libraryItem && $item->libraryItem->media->isNotEmpty()) {
                    try {
                        $videoUrl = $item->libraryItem->media->first()->temporaryUrl();
                    } catch (\Throwable) {
                        $videoUrl = $item->video_url;
                    }
                }

                return [
                    'id' => $item->id,
                    'library_item_id' => $item->library_item_id,
                    'order' => $item->sort_order,
                    'symbol' => $item->symbol,
                    'title' => $item->title,
                    'hand_type' => $item->hand_type,
                    'video_url' => $videoUrl,
                    'instruction' => $item->instruction ?? $item->libraryItem?->movement_description,
                    'description' => $item->instruction ?? $item->libraryItem?->movement_description,
                    'una_tip' => $item->tip,
                    'tip' => $item->tip,
                ];
            })->all();

            $questions = $lesson->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'order' => $q->sort_order,
                    'question' => $q->question,
                    'video_url' => $q->video_url,
                    'media_url' => $q->video_url,
                    'symbol_hint' => $q->symbol_hint,
                    'options' => $q->options ?? [],
                    'correct_index' => $q->correct_index,
                    'explanation' => $q->explanation,
                ];
            })->all();

            return [
                'id' => $lesson->id,
                'slug' => $lesson->slug,
                'order' => $lesson->sort_order,
                'type' => $lesson->type,
                'title' => $lesson->title,
                'subtitle' => $lesson->subtitle,
                'category' => $lesson->category,
                'badge_text' => $lesson->badge_text,
                'xp_reward' => $lesson->xp_reward,
                'passing_score' => $lesson->passing_score,
                'items' => $items,
                'questions' => $questions,
            ];
        })->all();

        return [
            'id' => $module->id,
            'unit_id' => $module->unit_id,
            'slug' => $module->slug,
            'title' => $module->title,
            'subtitle' => $module->subtitle,
            'description' => $module->description,
            'icon' => $module->icon_emoji ?? '🤟',
            'icon_url' => $module->icon_url ?? '',
            'xp_reward' => $module->xp_reward,
            'status' => $module->is_active ? 'active' : 'locked',
            'parts' => $parts,
        ];
    }

    /**
     * Record lesson completion and calculate score for user.
     */
    public function recordLessonProgress(User $user, ModuleLesson $lesson, ?int $score = null): UserLessonProgress
    {
        return UserLessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_lesson_id' => $lesson->id,
            ],
            [
                'status' => 'completed',
                'score' => $score !== null ? max($score, (int) optional(UserLessonProgress::where('user_id', $user->id)->where('module_lesson_id', $lesson->id)->first())->score) : null,
                'completed_at' => now(),
            ]
        );
    }
}
