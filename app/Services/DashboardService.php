<?php

namespace App\Services;

use App\Models\Module;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserLessonProgress;
use App\Models\UserModuleProgress;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get all structured data for the user dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(User $user): array
    {
        $streak = $this->calculateStreak($user);
        $totalXp = $this->calculateTotalXp($user);
        $modules = $this->getUserModules($user);
        $missions = $this->getDailyMissions($user);
        [$leaderboard, $userRank] = $this->getLeaderboard($user, $totalXp);

        return [
            'streak' => $streak,
            'xp' => $totalXp,
            'poin' => $totalXp,
            'userRank' => $userRank,
            'modules' => $modules,
            'missions' => $missions,
            'leaderboard' => $leaderboard,
        ];
    }

    /**
     * Calculate active day streak for user.
     */
    public function calculateStreak(User $user): int
    {
        $dates = collect();

        // Ambil tanggal dari user_lesson_progress
        $lessonDates = UserLessonProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->pluck('completed_at')
            ->map(fn ($d) => Carbon::parse($d)->toDateString());
        $dates = $dates->merge($lessonDates);

        // Ambil tanggal dari user_module_progress jika ada
        $moduleDates = UserModuleProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereNotNull('completed_at')
            ->pluck('completed_at')
            ->map(fn ($d) => Carbon::parse($d)->toDateString());
        $dates = $dates->merge($moduleDates);

        // Ambil tanggal dari quiz_attempts
        $quizDates = QuizAttempt::where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->pluck('submitted_at')
            ->map(fn ($d) => Carbon::parse($d)->toDateString());
        $dates = $dates->merge($quizDates);

        $uniqueDates = $dates->unique()->sortDesc()->values();

        if ($uniqueDates->isEmpty()) {
            return 0;
        }

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $firstDate = $uniqueDates->first();
        if ($firstDate !== $today && $firstDate !== $yesterday) {
            return 0;
        }

        $streak = 0;
        $currentExpected = Carbon::parse($firstDate);

        foreach ($uniqueDates as $dateStr) {
            $date = Carbon::parse($dateStr);
            if ($date->equalTo($currentExpected)) {
                $streak++;
                $currentExpected->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calculate total XP / poin earned by user.
     */
    public function calculateTotalXp(User $user): int
    {
        $quizPoints = (int) QuizAttempt::where('user_id', $user->id)->sum('points_earned');

        $completedLessonsCount = UserLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // 10 XP per lesson + poin dari kuis
        $lessonXp = $completedLessonsCount * 10;

        return $quizPoints + $lessonXp;
    }

    /**
     * Get module learning cards with progress and lock states.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUserModules(User $user): array
    {
        $modules = Module::query()
            ->where('status', '!=', 'archived')
            ->with(['lessons' => fn ($q) => $q->where('status', '!=', 'archived')->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($modules->isEmpty()) {
            return [];
        }

        $userCompletedLessonIds = UserLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->all();

        $result = [];
        $prevModuleCompleted = true;

        foreach ($modules as $modul) {
            $totalLessons = $modul->lessons->count();
            $completedLessons = $modul->lessons->filter(fn ($l) => in_array($l->id, $userCompletedLessonIds, true))->count();

            $progress = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;
            $isCompleted = $totalLessons > 0 && $completedLessons >= $totalLessons;

            $isLocked = ! $prevModuleCompleted || $modul->status !== 'published';
            $prevModuleCompleted = ! $isLocked && $isCompleted;

            // Tentukan tipe visual (teks atau ikon)
            $visualType = 'text';
            $visualContent = 'A – Z';

            $titleLower = strtolower($modul->title);
            if (str_contains($titleLower, 'angka') || str_contains($titleLower, 'hitung')) {
                $visualType = 'text';
                $visualContent = '123';
            } elseif (str_contains($titleLower, 'kata ganti') || str_contains($titleLower, 'sapaan') || str_contains($titleLower, 'orang')) {
                $visualType = 'icon';
                $visualContent = 'images/dashboard/icon-hands.svg';
            } elseif (! empty($modul->thumbnail_path)) {
                $visualType = 'icon';
                $visualContent = $modul->thumbnail_path;
            }

            $levelLabel = match (strtolower($modul->level ?? 'beginner')) {
                'intermediate' => 'Tingkat Menengah',
                'advanced' => 'Tingkat Mahir',
                default => 'Tingkat Dasar',
            };

            $result[] = [
                'id' => $modul->id,
                'slug' => $modul->slug,
                'title' => $modul->title,
                'level' => $levelLabel,
                'description' => $modul->description ?? 'Kuasai materi '.$modul->title.' dalam isyarat BISINDO.',
                'progress' => $progress,
                'is_locked' => $isLocked,
                'visual_type' => $visualType,
                'visual_content' => $visualContent,
            ];
        }

        return $result;
    }

    /**
     * Get daily missions progress.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDailyMissions(User $user): array
    {
        $today = Carbon::today();

        // XP yang didapat hari ini
        $todayQuizPoints = (int) QuizAttempt::where('user_id', $user->id)
            ->whereDate('submitted_at', $today)
            ->sum('points_earned');

        $todayCompletedLessons = UserLessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->count();

        $todayXp = $todayQuizPoints + ($todayCompletedLessons * 10);

        return [
            [
                'title' => 'Dapatkan 10 xp',
                'reward' => 100,
                'current' => min(10, $todayXp),
                'target' => 10,
            ],
            [
                'title' => 'Tuntaskan 1 materi',
                'reward' => 100,
                'current' => min(1, $todayCompletedLessons),
                'target' => 1,
            ],
        ];
    }

    /**
     * Get leaderboard ranking.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: int}
     */
    public function getLeaderboard(User $user, int $userTotalXp): array
    {
        // Standar kompetisi ramah pemula
        $benchmarkBoard = [
            ['name' => 'Anis Baswedan', 'xp' => 100],
            ['name' => 'Prabowo Subianto', 'xp' => 80],
            ['name' => 'Ganjar Pranowo', 'xp' => 70],
        ];

        // Hitung perkiraan rank pengguna
        $higherScores = 0;
        foreach ($benchmarkBoard as $bench) {
            if ($bench['xp'] > $userTotalXp) {
                $higherScores++;
            }
        }

        $userRank = $userTotalXp > 0 ? ($higherScores + 1) : 99;

        $leaderboard = [];
        $rankNum = 1;
        $userInserted = false;

        foreach ($benchmarkBoard as $bench) {
            if (! $userInserted && $userTotalXp >= $bench['xp'] && $userTotalXp > 0) {
                $leaderboard[] = [
                    'rank' => $rankNum++,
                    'name' => $user->name,
                    'xp' => $userTotalXp,
                    'is_current_user' => true,
                ];
                $userInserted = true;
            }

            $leaderboard[] = [
                'rank' => $rankNum++,
                'name' => $bench['name'],
                'xp' => $bench['xp'],
                'is_current_user' => false,
            ];
        }

        if (! $userInserted) {
            $leaderboard[] = [
                'rank' => $userRank,
                'name' => $user->name,
                'xp' => $userTotalXp,
                'is_current_user' => true,
            ];
        }

        return [$leaderboard, $userRank];
    }
}
