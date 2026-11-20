@props([
    'streak' => 0,
    'poin' => 0,
])

<div class="flex items-center gap-2.5 sm:gap-3 self-start shrink-0">
    <!-- Streak Pill -->
    <div class="bg-white border-2 border-b-[3px] border-[#E4E4E7] rounded-full px-3.5 py-1.5 flex items-center gap-2 shadow-xs transition hover:scale-105 select-none" title="Streak {{ $streak }} Hari">
        <img src="{{ asset('images/dashboard/icon-fire.svg') }}" alt="Streak" class="w-3.5 h-3.5" />
        <span class="font-bold text-[#FF7A00] text-xs sm:text-sm tracking-wide uppercase">{{ (int) $streak }} HARI</span>
    </div>

    <!-- Poin Pill -->
    <div class="bg-white border-2 border-b-[3px] border-[#E4E4E7] rounded-full px-3.5 py-1.5 flex items-center gap-2 shadow-xs transition hover:scale-105 select-none" title="Total {{ $poin }} Poin">
        <img src="{{ asset('images/dashboard/icon-coin-sm.svg') }}" alt="Poin" class="w-4.5 h-4.5" />
        <span class="font-bold text-[#C98A06] text-xs sm:text-sm tracking-wide">{{ (int) $poin }} Poin</span>
    </div>
</div>