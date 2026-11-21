<div class="max-w-[820px] mx-auto w-full px-4 sm:px-6 py-6 sm:py-8 flex-1 flex flex-col gap-6">
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-5 sm:p-6 space-y-5">
        <div class="flex items-center justify-between gap-2">
            <span class="inline-flex items-center bg-[#FFF8E6] border border-[#F9B92F]/40 rounded-md px-3 py-1 text-xs font-extrabold text-[#C98A06] uppercase">
                Soal <span class="mx-1" x-text="quizIndex + 1"></span> dari <span class="ml-1" x-text="questions.length"></span>
            </span>

            <span class="text-xs font-bold text-[#71717A]" x-text="'Skor Sementara: ' + scorePercent + '%'"></span>
        </div>

        <h1 class="text-lg sm:text-2xl font-extrabold text-[#1C1C1E] leading-snug" x-text="currentQuestion.question"></h1>

        <div class="aspect-video max-h-[280px] w-full bg-[#F4F4F5] border-2 border-[#E4E4E7] rounded-xl overflow-hidden flex items-center justify-center relative">
            <video
                x-show="!quizVideoError && currentQuestion.media_url"
                :src="currentQuestion.media_url"
                autoplay
                loop
                muted
                playsinline
                @error="quizVideoError = true"
                class="w-full h-full object-cover"
            ></video>

            <div x-show="quizVideoError || !currentQuestion.media_url" class="flex flex-col items-center justify-center gap-1.5">
                <span class="text-4xl sm:text-5xl font-extrabold text-[#1C1C1E]" x-text="currentQuestion.symbol_hint || '?'"></span>
                <span class="text-xs font-bold text-[#71717A]">Peragaan Isyarat BISINDO</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
            <template x-for="(option, optIdx) in currentQuestion.options" :key="optIdx">
                <button
                    type="button"
                    @click="selectOption(optIdx)"
                    :disabled="answered"
                    :class="{
                        'bg-[#E9F9EF] border-[#22B05B] text-[#15803D]': answered && optIdx === currentQuestion.correct_index,
                        'bg-[#FEF2F2] border-[#EF4444] text-[#B91C1C]': answered && selectedOption === optIdx && optIdx !== currentQuestion.correct_index,
                        'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E]': !answered && selectedOption === optIdx,
                        'bg-white border-[#E4E4E7] hover:border-[#F9B92F] hover:bg-[#FFF8E6]/50 text-[#1C1C1E]': !answered && selectedOption !== optIdx,
                        'opacity-60': answered && optIdx !== currentQuestion.correct_index && selectedOption !== optIdx
                    }"
                    class="border-2 border-b-4 rounded-xl p-4 text-left font-extrabold text-sm sm:text-base flex items-center gap-3 transition-all cursor-pointer"
                >
                    <span class="w-8 h-8 rounded-lg bg-[#F4F4F5] border border-[#E4E4E7] flex items-center justify-center text-xs font-extrabold text-[#52525B] shrink-0" x-text="['A', 'B', 'C', 'D'][optIdx]"></span>
                    <span x-text="option"></span>
                </button>
            </template>
        </div>

        <div
            x-show="answered && currentQuestion.explanation"
            x-transition.opacity
            :class="selectedOption === currentQuestion.correct_index ? 'bg-[#E9F9EF] border-[#22B05B] text-[#15803D]' : 'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E]'"
            class="border-2 rounded-xl p-4 text-sm font-medium leading-relaxed"
        >
            <strong class="font-extrabold block mb-1" x-text="selectedOption === currentQuestion.correct_index ? '✓ Tepat Sekali!' : '💡 Pembahasan:'"></strong>
            <span x-text="currentQuestion.explanation"></span>
        </div>
    </div>

    <div class="flex justify-end">
        <x-ui.button
            variant="primary"
            size="lg"
            x-show="!answered"
            @click="checkAnswer()"
            x-bind:disabled="selectedOption === null"
            class="w-full sm:w-auto"
        >
            Periksa Jawaban
        </x-ui.button>

        <x-ui.button
            variant="primary"
            size="lg"
            x-show="answered"
            @click="nextQuestion()"
            class="w-full sm:w-auto"
        >
            <span x-text="quizIndex < questions.length - 1 ? 'Soal Berikutnya →' : 'Selesai & Lihat Hasil ✓'"></span>
        </x-ui.button>
    </div>
</div>