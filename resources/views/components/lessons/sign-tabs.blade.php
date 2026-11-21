<div class="bg-[#FAFAFA] border-b border-[#E4E4E7]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-8 py-3">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <template x-for="(item, idx) in items" :key="idx">
                <button
                    type="button"
                    @click="selectItem(idx)"
                    :class="{
                        'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E]': activeIndex === idx,
                        'bg-[#E9F9EF] border-[#22B05B] text-[#15803D]': activeIndex !== idx && completedIndices.includes(idx),
                        'bg-white border-[#E4E4E7] text-[#52525B] hover:border-[#F9B92F]/70': activeIndex !== idx && !completedIndices.includes(idx)
                    }"
                    class="relative min-w-[48px] h-11 px-3.5 rounded-xl border-2 border-b-4 font-extrabold text-sm flex items-center justify-center gap-1.5 shrink-0 transition-all cursor-pointer active:translate-y-0.5"
                >
                    <span x-text="item.symbol"></span>

                    <svg
                        x-show="completedIndices.includes(idx)"
                        class="w-3.5 h-3.5 text-[#22B05B] shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="3"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </template>
        </div>
    </div>
</div>