@props([
    'category' => 'BISINDO',
])

<div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-5 sm:p-6 space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1.5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center bg-[#FFF8E6] border border-[#F9B92F]/50 rounded-md px-2.5 py-0.5 text-[11px] font-extrabold text-[#C98A06] uppercase">
                    {{ $category }}
                </span>

                <span
                    class="inline-flex items-center bg-[#F4F4F5] border border-[#E4E4E7] rounded-md px-2.5 py-0.5 text-[11px] font-bold text-[#52525B]"
                    x-text="currentItem.hand_type"
                ></span>
            </div>

            <h1 class="text-xl sm:text-2xl font-extrabold text-[#1C1C1E]" x-text="currentItem.title"></h1>
        </div>

        <div class="w-12 h-12 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-xl font-extrabold text-[#1C1C1E] shrink-0">
            <span x-text="currentItem.symbol"></span>
        </div>
    </div>

    <div class="border-t border-[#E4E4E7] pt-3.5 space-y-1.5">
        <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#71717A]">Cara Memperagakan</h2>
        <p class="text-sm sm:text-base text-[#1C1C1E] font-medium leading-relaxed" x-text="currentItem.instruction"></p>
    </div>
</div>