<?php

use App\Support\MockCurriculum;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Modul Belajar')] class extends Component
{
    public function with(): array
    {
        $user = auth()->user();

        return [
            'streak' => $user->streak ?? 0,
            'xp'     => ($user->xp ?? 420) + (int) session('session_xp', 0),
            // TODO (Backend): Ganti $this->getUnitsWithSessionProgress() dengan query Eloquent / ModulService
            'units'  => $this->getUnitsWithSessionProgress(),
        ];
    }

    private function getUnitsWithSessionProgress(): array
    {
        $completedKeys = (array) session('completed_parts', []);
        $quizScores    = (array) session('quiz_scores', []);
        $prevModulDone = true;

        return collect(MockCurriculum::getUnits())->map(function (array $unit) use ($completedKeys, $quizScores, &$prevModulDone) {
            $modules = collect($unit['modules'] ?? [])->map(function (array $modul) use ($completedKeys, $quizScores, &$prevModulDone) {
                $rawParts = collect($modul['parts'] ?? []);

                $parts = $rawParts->map(function (array $part) use ($modul, $completedKeys, $quizScores) {
                    $key    = $modul['slug'] . ':' . $part['slug'];
                    $isDone = in_array($key, $completedKeys, true) || ($part['status'] ?? '') === 'completed';

                    if ($isDone) {
                        $part['status'] = 'completed';

                        if (($part['type'] ?? 'lesson') === 'quiz' && isset($quizScores[$key])) {
                            $score = (int) $quizScores[$key];
                            $part['badge_text'] = $score >= 100 ? 'Skor 100 • Sempurna' : "Skor {$score}";
                        }
                    }

                    return $part;
                });

                $wasLocked    = ($modul['status'] ?? '') === 'locked';
                $isLocked     = $wasLocked && ! $prevModulDone;
                $totalParts   = $parts->count();
                $doneCount    = $parts->where('status', 'completed')->count();
                $isModulDone  = $totalParts > 0 && $doneCount === $totalParts;
                $isInProgress = ! $isLocked && $doneCount > 0 && ! $isModulDone;

                $prevModulDone = ! $isLocked && $isModulDone;

                return array_merge($modul, [
                    'status'      => $isLocked ? 'locked' : ($isModulDone ? 'completed' : null),
                    'tag'         => (! $isLocked && ! $isModulDone) ? ($modul['tag'] ?? 'SEDANG AKTIF') : ($modul['tag'] ?? null),
                    'description' => $isLocked
                        ? ($modul['description'] ?? 'Terkunci • Selesaikan modul sebelumnya terlebih dahulu')
                        : (str_starts_with($modul['description'] ?? '', 'Terkunci')
                            ? 'Kuasai materi ' . $modul['title'] . ' dalam isyarat BISINDO.'
                            : ($modul['description'] ?? '')),
                    'xp_reward'   => $modul['xp_reward'] ?? $parts->sum('xp_reward'),
                    'total_parts' => $modul['total_parts'] ?? $totalParts,
                    'is_expanded' => $isInProgress || ($wasLocked && ! $isLocked) || (! $isLocked && ! $isModulDone && ($modul['is_expanded'] ?? false)),
                    'parts'       => $isLocked ? [] : $parts->all(),
                ]);
            });

            return array_merge($unit, [
                'is_locked' => $modules->isNotEmpty() && $modules->every(fn (array $m) => ($m['status'] ?? '') === 'locked'),
                'modules'   => $modules->all(),
            ]);
        })->all();
    }
};
?>

<div x-data="{ activeFilter: 'all' }" class="space-y-6 pb-8">
    {{-- TOP HEADER BAR & GAMIFICATION PILLS --}}
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-[28px] font-extrabold tracking-tight text-[#1C1C1E] uppercase">
                Modul Belajar
            </h1>
            <p class="text-sm sm:text-[15px] text-[#52525B] mt-1 font-medium">
                Kuasai bahasa isyarat BISINDO langkah demi langkah.
            </p>
        </div>

        <x-gamified-badges :streak="$streak" :xp="$xp" />
    </header>

    {{-- FILTER CHIPS --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        @foreach ([
            'all'         => 'Semua Modul',
            'in_progress' => 'Sedang Aktif',
            'completed'   => 'Tuntas',
        ] as $filterKey => $filterLabel)
            <button
                type="button"
                @click="activeFilter = '{{ $filterKey }}'"
                :class="activeFilter === '{{ $filterKey }}'
                    ? 'bg-[#FFF8E6] border-[#F9B92F] border-b-[#C98A06] text-[#1C1C1E]'
                    : 'bg-white border-[#E4E4E7] border-b-[#D4D4D8] text-[#52525B] hover:bg-zinc-50'"
                class="border-2 border-b-[3px] rounded-xl px-4 py-2 text-xs sm:text-sm font-extrabold transition-all cursor-pointer whitespace-nowrap"
            >
                {{ $filterLabel }}
            </button>
        @endforeach
    </div>

    {{-- DAFTAR UNIT & KARTU MODUL --}}
    <div class="space-y-8">
        @forelse ($units as $unit)
            @php
                $unitLocked   = (bool) ($unit['is_locked'] ?? false);
                $unitModules  = collect($unit['modules'] ?? []);
                $totalModules = $unitModules->count();

                $completedModules = $unitModules->filter(function ($m) {
                    if (($m['status'] ?? '') === 'locked') {
                        return false;
                    }
                    $mParts = collect($m['parts'] ?? []);
                    return $mParts->isNotEmpty()
                        ? $mParts->every(fn ($p) => ($p['status'] ?? '') === 'completed')
                        : ($m['status'] ?? '') === 'completed';
                })->count();

                $unitStatusText = $unit['status_text'] ?? "{$completedModules} dari {$totalModules} Modul Selesai";
            @endphp

            <section class="space-y-5">
                {{-- Banner Unit --}}
                <div class="{{ $unitLocked
                    ? 'bg-[#EFEFEF] border-2 border-[#D4D4D8] border-b-4 border-b-[#D4D4D8]'
                    : 'bg-[#F9B92F] border-2 border-[#C98A06] border-b-4 border-b-[#C98A06]' }} rounded-2xl p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                    <div class="flex flex-col items-start">
                        <span class="inline-flex items-center bg-white border {{ $unitLocked ? 'border-[#D4D4D8] text-[#71717A]' : 'border-2 border-[#C98A06] text-[#C98A06]' }} rounded-full px-3 py-1 text-xs font-extrabold uppercase tracking-wider">
                            {{ $unit['badge'] }}
                        </span>
                        <h2 class="text-xl sm:text-[22px] font-extrabold {{ $unitLocked ? 'text-[#71717A]' : 'text-[#1C1C1E]' }} mt-2 tracking-tight">
                            {{ $unit['title'] }}
                        </h2>
                        @if (! empty($unit['description']))
                            <p class="text-sm sm:text-[15px] {{ $unitLocked ? 'text-[#71717A]' : 'text-[#1C1C1E]' }} font-medium mt-0.5">
                                {{ $unit['description'] }}
                            </p>
                        @endif
                    </div>

                    <div class="self-start md:self-auto shrink-0">
                        <div class="inline-flex items-center gap-1.5 bg-white {{ $unitLocked ? 'border border-[#D4D4D8] text-[#71717A]' : 'border-2 border-[#C98A06] text-[#1C1C1E]' }} rounded-full px-4 py-2 text-xs sm:text-sm font-extrabold">
                            <span>{{ $unitStatusText }}</span>
                            @unless ($unitLocked)
                                <span class="text-[#22B05B] font-bold">✓</span>
                            @endunless
                        </div>
                    </div>
                </div>

                {{-- Daftar Kartu Modul --}}
                <div class="space-y-4">
                    @forelse ($unit['modules'] ?? [] as $modul)
                        <x-modul-card :modul="$modul" />
                    @empty
                        <div class="bg-white border-2 border-dashed border-[#E4E4E7] rounded-2xl p-6 text-center text-sm text-[#71717A]">
                            Belum ada materi pada unit ini.
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="bg-white border-2 border-dashed border-[#E4E4E7] rounded-2xl p-10 text-center text-sm text-[#71717A]">
                Daftar unit pembelajaran belum tersedia.
            </div>
        @endforelse
    </div>
</div>