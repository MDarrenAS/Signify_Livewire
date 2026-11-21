@props([
    'isQuiz' => false,
    'isPassed' => true,
    'partOrder' => 1,
    'partTitle' => '',
    'xpReward' => 0,
    'itemsCount' => 0,
    'finalScore' => 0,
    'passingScore' => 80,
    'correctCount' => 0,
    'totalQuestions' => 0,
    'modulUrl' => '#',
    'nextUrl' => '#',
    'nextLabel' => 'Lanjut Modul Berikutnya →',
])

<main class="flex-1 flex items-center justify-center px-4 sm:px-6 py-8 sm:py-12">
    <div class="w-full max-w-[540px] bg-white border-2 border-b-4 border-[#E4E4E7] rounded-3xl p-6 sm:p-10 text-center">
        {{-- Ikon Status --}}
        @if ($isPassed)
            <div class="w-16 h-16 mx-auto rounded-full bg-[#22B05B] border-2 border-b-4 border-[#15803D] flex items-center justify-center text-white text-2xl font-extrabold shadow-xs">
                ✓
            </div>
        @else
            <div class="w-16 h-16 mx-auto rounded-full bg-[#EF4444] border-2 border-b-4 border-[#B91C1C] flex items-center justify-center text-white text-2xl font-extrabold shadow-xs">
                !
            </div>
        @endif

        {{-- Judul & Subjudul --}}
        <h1 class="text-2xl sm:text-[28px] font-extrabold text-[#1C1C1E] tracking-tight mt-5">
            @if (! $isPassed)
                Belum Lulus Kuis
            @elseif ($isQuiz)
                Kuis Selesai!
            @else
                Materi Selesai!
            @endif
        </h1>

        <p class="text-sm sm:text-[15px] text-[#71717A] font-medium mt-1.5">
            @if (! $isPassed)
                Skor kamu belum mencapai batas minimum Bagian {{ $partOrder }}: {{ $partTitle }}.
            @else
                Kamu telah menuntaskan Bagian {{ $partOrder }}: {{ $partTitle }}.
            @endif
        </p>

        {{-- 2 Kotak Statistik --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4 mt-6">
            @if ($isQuiz)
                {{-- Kotak Kiri Kuis: Skor Akhir --}}
                <div class="{{ $isPassed ? 'bg-[#F0FDF4] border-[#86EFAC]' : 'bg-[#FEF2F2] border-[#FCA5A5]' }} border-2 rounded-2xl p-4 sm:p-5 text-center">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider {{ $isPassed ? 'text-[#15803D]' : 'text-[#B91C1C]' }}">
                        Skor Akhir
                    </p>
                    <p class="text-3xl sm:text-[34px] font-extrabold {{ $isPassed ? 'text-[#15803D]' : 'text-[#B91C1C]' }} mt-1 leading-tight">
                        {{ $finalScore }}%
                    </p>
                    <p class="text-xs font-bold {{ $isPassed ? 'text-[#16A34A]' : 'text-[#DC2626]' }} mt-1">
                        {{ $correctCount }} dari {{ $totalQuestions }} Benar
                    </p>
                </div>

                {{-- Kotak Kanan Kuis: XP / Syarat Lulus --}}
                <div class="bg-[#FFF8E6] border-2 border-[#F9B92F] rounded-2xl p-4 sm:p-5 text-center">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#C98A06]">
                        {{ $isPassed ? 'XP Diperoleh' : 'Target Kelulusan' }}
                    </p>
                    <p class="text-3xl sm:text-[34px] font-extrabold text-[#C98A06] mt-1 leading-tight">
                        {{ $isPassed ? '+' . $xpReward . ' XP' : $passingScore . '%' }}
                    </p>
                    <p class="text-xs font-bold text-[#B45309] mt-1">
                        Status: {{ $isPassed ? 'Lulus' : 'Belum Lulus' }} (Min. {{ $passingScore }}%)
                    </p>
                </div>
            @else
                {{-- Kotak Kiri Materi: Jumlah Isyarat --}}
                <div class="bg-[#F0FDF4] border-2 border-[#86EFAC] rounded-2xl p-4 sm:p-5 text-center">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#15803D]">
                        Materi Dipelajari
                    </p>
                    <p class="text-3xl sm:text-[34px] font-extrabold text-[#15803D] mt-1 leading-tight">
                        {{ $itemsCount }} Isyarat
                    </p>
                    <p class="text-xs font-bold text-[#16A34A] mt-1">
                        100% Tuntas
                    </p>
                </div>

                {{-- Kotak Kanan Materi: XP Diperoleh --}}
                <div class="bg-[#FFF8E6] border-2 border-[#F9B92F] rounded-2xl p-4 sm:p-5 text-center">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-[#C98A06]">
                        XP Diperoleh
                    </p>
                    <p class="text-3xl sm:text-[34px] font-extrabold text-[#C98A06] mt-1 leading-tight">
                        +{{ $xpReward }} XP
                    </p>
                    <p class="text-xs font-bold text-[#B45309] mt-1">
                        Progres Tersimpan
                    </p>
                </div>
            @endif
        </div>

        {{-- Garis Pemisah --}}
        <hr class="border-t-2 border-[#F4F4F5] my-6" />

        {{-- Tombol Aksi Bawah --}}
        <div class="flex flex-col sm:flex-row items-center gap-3">
            @if ($isQuiz)
                <x-ui.button
                    variant="secondary"
                    wire:click="retryQuiz"
                    class="w-full sm:flex-1"
                >
                    <span>↺</span>
                    <span>Ulangi Kuis</span>
                </x-ui.button>
            @else
                <x-ui.button
                    variant="secondary"
                    wire:click="retryLesson"
                    class="w-full sm:flex-1"
                >
                    <span>↺</span>
                    <span>Ulangi Materi</span>
                </x-ui.button>
            @endif

            @if ($isPassed)
                <x-ui.button
                    variant="primary"
                    href="{{ $nextUrl }}"
                    wire:navigate
                    class="w-full sm:flex-1"
                >
                    <span>{{ $nextLabel }}</span>
                </x-ui.button>
            @else
                <x-ui.button
                    variant="secondary"
                    href="{{ $modulUrl }}"
                    wire:navigate
                    class="w-full sm:flex-1"
                >
                    <span>Daftar Modul</span>
                </x-ui.button>
            @endif
        </div>
    </div>
</main>