<footer class="sticky bottom-0 z-20 bg-white border-t-2 border-[#E4E4E7]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-8 py-3.5 sm:py-4 flex items-center justify-between gap-3">
        <x-ui.button
            variant="secondary"
            @click="prevItem()"
            x-bind:disabled="activeIndex === 0"
        >
            ← Sebelumnya
        </x-ui.button>

        <x-ui.button
            variant="primary"
            @click="nextOrComplete()"
        >
            <span x-text="nextButtonLabel"></span>
        </x-ui.button>
    </div>
</footer>