<?php

namespace App\Services;

use App\Models\LibraryItem;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\User;
use App\Models\UserLessonProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AdminDashboardService
{
    /**
     * @return array<string, int>
     */
    public function getStats(): array
    {
        $learners = User::query()->where('role', 'user');
        $weekStart = now()->startOfWeek();

        return [
            'units' => Module::query()
                ->where('status', 'published')
                ->distinct()
                ->count('level'),
            'modules' => Module::query()
                ->where('status', '!=', 'archived')
                ->count(),
            'signs' => LibraryItem::query()->count(),
            'quizzes' => Quiz::query()->count(),
            'users' => (clone $learners)->count(),
            'new_users_this_week' => (clone $learners)
                ->where('created_at', '>=', $weekStart)
                ->where('created_at', '<', $weekStart->copy()->addWeek())
                ->count(),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     level: string,
     *     total_xp: int,
     *     completed_parts: int,
     *     status: string,
     *     last_active: string
     * }>
     */
    public function getUsers(string $search, string $status): LengthAwarePaginator
    {
        $query = User::query()
            ->where('role', 'user')
            ->withSum('quizAttempts as quiz_points', 'points_earned')
            ->withCount([
                'lessonProgress as completed_parts' => fn (Builder $query) => $query->where('status', 'completed'),
            ])
            ->withMax('lessonProgress as last_lesson_accessed_at', 'last_accessed_at')
            ->withMax('lessonProgress as last_lesson_completed_at', 'completed_at')
            ->withMax('quizAttempts as last_quiz_submitted_at', 'submitted_at')
            ->when(trim($search) !== '', function (Builder $query) use ($search): void {
                $term = '%'.strtolower(trim($search)).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                });
            })
            ->when($status === 'Aktif', fn (Builder $query) => $query->where('is_active', true))
            ->when($status === 'Nonaktif', fn (Builder $query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(10);

        $userIds = $query->getCollection()->modelKeys();
        $levelRanks = UserLessonProgress::query()
            ->join('module_lessons', 'module_lessons.id', '=', 'user_lesson_progress.lesson_id')
            ->join('modules', 'modules.id', '=', 'module_lessons.module_id')
            ->whereIn('user_lesson_progress.user_id', $userIds)
            ->where('user_lesson_progress.status', 'completed')
            ->select('user_lesson_progress.user_id')
            ->selectRaw("MAX(CASE WHEN modules.level = 'advanced' THEN 3 WHEN modules.level = 'intermediate' THEN 2 ELSE 1 END) AS level_rank")
            ->groupBy('user_lesson_progress.user_id')
            ->pluck('level_rank', 'user_id');

        $query->setCollection($query->getCollection()->map(function (User $user) use ($levelRanks): array {
            $lastActiveAt = collect([
                $user->last_lesson_accessed_at,
                $user->last_lesson_completed_at,
                $user->last_quiz_submitted_at,
            ])
                ->filter()
                ->map(fn ($timestamp) => Carbon::parse($timestamp))
                ->max();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'level' => match ((int) $levelRanks->get($user->id, 0)) {
                    3 => 'Tingkat 3 • Mahir',
                    2 => 'Tingkat 2 • Menengah',
                    1 => 'Tingkat 1 • Pemula',
                    default => 'Belum ada progres',
                },
                'total_xp' => (int) $user->quiz_points + ((int) $user->completed_parts * 10),
                'completed_parts' => (int) $user->completed_parts,
                'status' => $user->is_active ? 'Aktif' : 'Nonaktif',
                'last_active' => $lastActiveAt?->diffForHumans() ?? 'Belum ada aktivitas',
            ];
        }));

        return $query;
    }
}
