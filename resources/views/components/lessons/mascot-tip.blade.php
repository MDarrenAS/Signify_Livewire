<div
    x-show="currentItem.una_tip"
    x-transition.opacity
    class="bg-[#FFF8E6] border-2 border-b-4 border-[#F9B92F]/60 rounded-2xl p-4 sm:p-5 flex items-start gap-3.5"
>
    <div class="w-11 h-11 rounded-2xl bg-[#F9B92F] border-2 border-b-4 border-[#C98A06] flex items-center justify-center text-xl shrink-0">
        <img src="{{ asset('images/una-mascot.svg') }}" alt="Una Mascot" class="w-5 h-5" />
    </div>

    <div class="space-y-1 min-w-0">
        <span class="text-xs font-extrabold uppercase tracking-wider text-[#C98A06] block">
            Tips dari Una
        </span>
        <p class="text-sm text-[#1C1C1E] font-medium leading-relaxed" x-text="currentItem.una_tip"></p>
    </div>
</div>