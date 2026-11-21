@props([
    'closeUrl',
    'partOrder'  => 1,
    'totalParts' => 1,
    'partTitle'  => '',
    'xpReward'   => 0,
])

<header class="sticky top-0 z-30 bg-white border-b-2 border-[#E4E4E7]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-8 h-16 sm:h-20 flex items-center gap-3 sm:gap-6">
        <a
            href="{{ $closeUrl }}"
            wire:navigate
            aria-label="Kembali ke daftar modul"
            class="w-10 h-10 rounded-xl border-2 border-b-4 border-[#E4E4E7] bg-white hover:bg-zinc-50 active:translate-y-0.5 flex items-center justify-center text-[#71717A] hover:text-[#1C1C1E] transition-all shrink-0"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>

        <div class="flex-1 flex flex-col gap-1.5 min-w-0">
            <div class="flex items-center justify-between gap-2 text-[11px] sm:text-xs font-extrabold tracking-wide uppercase">
                <span class="text-[#71717A] truncate">
                    Bagian {{ $partOrder }} dari {{ $totalParts }}
                    @if ($partTitle)
                        <span class="text-[#1C1C1E]">• {{ $partTitle }}</span>
                    @endif
                </span>
                <span class="text-[#C98A06] shrink-0" x-text="progressPercent + '%'"></span>
            </div>

            <div class="w-full h-3 bg-[#F4F4F5] rounded-full overflow-hidden border border-[#E4E4E7]">
                <div
                    class="h-full bg-[#F9B92F] rounded-full transition-all duration-300 ease-out"
                    :style="'width: ' + progressPercent + '%'"
                ></div>
            </div>
        </div>

        <div class="inline-flex items-center gap-1.5 bg-[#FFF8E6] border-2 border-[#F9B92F]/50 rounded-full px-3 py-1.5 shrink-0">
            <span class="text-sm leading-none">⚡</span>
            <span class="text-xs sm:text-sm font-extrabold text-[#C98A06]">+{{ $xpReward }} XP</span>
        </div>
    </div>
</header>