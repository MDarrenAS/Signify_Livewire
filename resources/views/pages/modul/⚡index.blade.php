<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Modul Belajar')] class extends Component
{
    public function with(): array
    {
        $user = auth()->user();

        return [
            'streak' => $user->streak ?? 0,
            'xp'     => $user->xp ?? 420,
            // TODO (Backend): Ganti $this->mockUnits() dengan query Eloquent / ModulService
            'units'  => $this->mockUnits(),
        ];
    }

    private function mockUnits(): array
    {
        return [
            [
                'id'          => 1,
                'badge'       => 'UNIT 1 • TINGKAT DASAR',
                'title'       => 'Pondasi Komunikasi BISINDO',
                'description' => 'Kuasai abjad visual, angka dasar, dan sapaan penting untuk memulai percakapan.',
                'is_locked'   => false,
                'modules'     => [
                    [
                        'slug'           => 'abjad-dan-ejaan',
                        'title'          => 'Abjad & Ejaan',
                        'description'    => 'Kuasai 26 isyarat huruf dasar BISINDO (6 Bagian).',
                        'xp_reward'      => 40,
                        'visual_content' => 'A-Z',
                        'is_expanded'    => false,
                        'parts'          => [
                            ['slug' => 'huruf-vokal', 'title' => 'Bagian 1 • Huruf Vokal (A, I, U, E, O)', 'badge_text' => '5 Isyarat', 'type' => 'lesson', 'status' => 'completed'],
                            ['slug' => 'konsonan-awal', 'title' => 'Bagian 2 • Konsonan Awal (B, C, D, F, G)', 'badge_text' => '5 Isyarat', 'type' => 'lesson', 'status' => 'completed'],
                            ['slug' => 'konsonan-tengah-1', 'title' => 'Bagian 3 • Konsonan Tengah I (H, J, K, L, M)', 'badge_text' => '5 Isyarat', 'type' => 'lesson', 'status' => 'completed'],
                            ['slug' => 'konsonan-tengah-2', 'title' => 'Bagian 4 • Konsonan Tengah II (N, P, Q, R, S)', 'badge_text' => '5 Isyarat', 'type' => 'lesson', 'status' => 'completed'],
                            ['slug' => 'konsonan-akhir', 'title' => 'Bagian 5 • Konsonan Akhir (T, V, W, X, Y, Z)', 'badge_text' => '6 Isyarat', 'type' => 'lesson', 'status' => 'completed'],
                            ['slug' => 'kuis-evaluasi-abjad', 'title' => 'Bagian 6 • Kuis Evaluasi Abjad (8 Soal)', 'badge_text' => 'Skor 100 • Sempurna', 'type' => 'quiz', 'status' => 'completed'],
                        ],
                    ],
                    [
                        'slug'           => 'angka-dan-hitungan',
                        'title'          => 'Angka & Hitungan',
                        'description'    => 'Belajar angka satuan hingga ribuan dalam isyarat BISINDO.',
                        'tag'            => 'SEDANG AKTIF',
                        'visual_content' => '1 2 3',
                        'is_expanded'    => true,
                        'parts'          => [
                            ['slug' => 'angka-satuan', 'title' => 'Bagian 1 • Angka Satuan (0–9)', 'badge_text' => '10 Isyarat', 'type' => 'lesson', 'status' => 'completed'],
                            ['slug' => 'angka-belasan-puluhan', 'title' => 'Bagian 2 • Angka Belasan & Puluhan', 'badge_text' => '8 Isyarat', 'type' => 'lesson', 'status' => 'completed'],
                            ['slug' => 'angka-ratusan-ribuan', 'title' => 'Bagian 3 • Angka Ratusan & Ribuan', 'badge_text' => '6 Isyarat', 'type' => 'lesson', 'status' => 'not_started'],
                            ['slug' => 'kuis-angka', 'title' => 'Bagian 4 • Kuis Hitungan Harian (6 Soal)', 'badge_text' => 'Kuis', 'type' => 'quiz', 'status' => 'not_started'],
                        ],
                    ],
                    [
                        'slug'        => 'kata-ganti-sapaan',
                        'title'       => 'Kata Ganti & Sapaan Dasar',
                        'description' => 'Terkunci • Selesaikan Modul Angka terlebih dahulu',
                        'status'      => 'locked',
                        'total_parts' => 3,
                        'parts'       => [],
                    ],
                ],
            ],
        ];
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