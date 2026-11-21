<main class="flex-1 max-w-[1200px] w-full mx-auto px-4 sm:px-8 py-5 sm:py-7 flex items-center">
    <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        {{-- KOLOM KIRI: VIDEO PERAGAAN ISYARAT --}}
        <div class="lg:col-span-6 bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden">
            <div
                x-on:click="toggleQuizPlay()"
                class="relative aspect-video bg-[#18181B] flex items-center justify-center overflow-hidden cursor-pointer select-none"
            >
                <video
                    x-ref="quizPlayer"
                    x-show="currentQuestion.video_url && !quizVideoError"
                    :src="currentQuestion.video_url"
                    loop
                    muted
                    playsinline
                    x-on:loadedmetadata="quizDuration = $event.target.duration || 4; $event.target.playbackRate = quizSpeed"
                    x-on:timeupdate="quizCurrentTime = $event.target.currentTime"
                    x-on:play="quizIsPlaying = true"
                    x-on:pause="quizIsPlaying = false"
                    x-on:error="quizVideoError = true"
                    class="w-full h-full object-cover"
                ></video>

                {{-- Overlay Tombol Play --}}
                <div
                    x-show="currentQuestion.video_url && !quizVideoError && !quizIsPlaying"
                    class="absolute inset-0 bg-black/25 flex items-center justify-center"
                >
                    <div class="w-14 h-14 rounded-2xl bg-[#F9B92F] border-2 border-b-4 border-[#C98A06] flex items-center justify-center text-[#1C1C1E] shadow-lg">
                        <svg class="w-6 h-6 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </div>
                </div>

                {{-- Fallback Jika Video Tidak Tersedia --}}
                <div
                    x-show="!currentQuestion.video_url || quizVideoError"
                    class="w-full h-full bg-[#F4F4F5] flex flex-col items-center justify-center gap-2 p-6 text-center"
                >
                    <div class="w-20 h-20 rounded-3xl bg-[#FFF8E6] border-2 border-b-4 border-[#F9B92F] flex items-center justify-center text-4xl">
                        🤟
                    </div>
                    <p class="text-sm font-extrabold text-[#1C1C1E] mt-1">Peragaan Isyarat Soal</p>
                    <span class="text-xs font-semibold text-[#71717A]">Pilih jawaban yang paling tepat di samping</span>
                </div>
            </div>

            {{-- Kontrol Mini Pemutar Video --}}
            <div class="px-4 py-3 bg-white border-t-2 border-[#E4E4E7] flex items-center justify-between gap-3">
                <button
                    type="button"
                    x-on:click="toggleQuizPlay()"
                    :disabled="!currentQuestion.video_url || quizVideoError"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F4F4F5] hover:bg-[#E4E4E7] disabled:opacity-40 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
                >
                    <span x-text="quizIsPlaying ? '⏸ Jeda' : '▶ Putar'"></span>
                </button>

                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold text-[#71717A] mr-1">Kecepatan:</span>
                    <template x-for="rate in [0.5, 1.0]" :key="rate">
                        <button
                            type="button"
                            x-on:click="setQuizSpeed(rate)"
                            :class="quizSpeed === rate
                                ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#C98A06]'
                                : 'bg-white border-[#E4E4E7] text-[#71717A] hover:text-[#1C1C1E]'"
                            class="px-2.5 py-1 rounded-lg border text-xs font-extrabold transition-colors cursor-pointer"
                            x-text="rate + 'x'"
                        ></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: PERTANYAAN & PILIHAN JAWABAN --}}
        <div class="lg:col-span-6 space-y-4">
            <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-5 sm:p-6 space-y-4">
                {{-- Header Nomor Soal --}}
                <div class="flex items-center justify-between gap-2">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full bg-[#FFF8E6] border border-[#F9B92F] text-[11px] font-extrabold uppercase tracking-wider text-[#C98A06]"
                        x-text="'Pertanyaan ' + (quizIndex + 1) + ' dari ' + questions.length"
                    ></span>
                    <span
                        class="text-xs font-extrabold text-[#15803D]"
                        x-text="'Skor Sementara: ' + correctCount + '/' + questions.length"
                    ></span>
                </div>

                {{-- Teks Pertanyaan --}}
                <h2
                    class="text-lg sm:text-xl font-extrabold text-[#1C1C1E] leading-snug"
                    x-text="currentQuestion.question ?? 'Perhatikan video isyarat ini. Apakah arti dari gerakan tersebut?'"
                ></h2>

                {{-- Daftar Opsi Jawaban --}}
                <div class="space-y-2.5 pt-1">
                    <template x-for="(option, oIdx) in currentQuestion.options" :key="quizIndex + '-' + oIdx">
                        <button
                            type="button"
                            x-on:click="selectOption(oIdx)"
                            :disabled="isCurrentAnswered"
                            :class="isCurrentAnswered
                                ? (oIdx === Number(currentQuestion.correct_index)
                                    ? 'bg-[#F0FDF4] border-[#22B05B] border-b-[#15803D] text-[#15803D]'
                                    : (currentSelected === oIdx
                                        ? 'bg-[#FEF2F2] border-[#EF4444] border-b-[#B91C1C] text-[#B91C1C]'
                                        : 'bg-white border-[#E4E4E7] border-b-[#D4D4D8] text-[#71717A] opacity-60'))
                                : (currentSelected === oIdx
                                    ? 'bg-[#FFF8E6] border-[#F9B92F] border-b-[#C98A06] text-[#1C1C1E]'
                                    : 'bg-white border-[#E4E4E7] border-b-[#D4D4D8] text-[#1C1C1E] hover:bg-zinc-50 hover:border-[#F9B92F] cursor-pointer')"
                            class="w-full border-2 border-b-[3px] rounded-xl px-4 py-3 text-left font-extrabold text-sm sm:text-[15px] transition-all flex items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-3 min-w-0 pointer-events-none">
                                <span
                                    :class="isCurrentAnswered
                                        ? (oIdx === Number(currentQuestion.correct_index)
                                            ? 'bg-[#22B05B] text-white border-[#15803D]'
                                            : (currentSelected === oIdx
                                                ? 'bg-[#EF4444] text-white border-[#B91C1C]'
                                                : 'bg-[#F4F4F5] text-[#71717A] border-[#E4E4E7]'))
                                        : (currentSelected === oIdx
                                            ? 'bg-[#F9B92F] text-[#1C1C1E] border-[#C98A06]'
                                            : 'bg-[#F4F4F5] text-[#52525B] border-[#E4E4E7]')"
                                    class="w-7 h-7 rounded-lg border text-xs font-extrabold flex items-center justify-center shrink-0"
                                    x-text="String.fromCharCode(65 + oIdx)"
                                ></span>
                                <span class="truncate" x-text="typeof option === 'object' ? (option.label ?? option.text ?? '') : option"></span>
                            </div>

                            <span
                                x-show="isCurrentAnswered && oIdx === Number(currentQuestion.correct_index)"
                                class="text-[#15803D] font-extrabold text-base shrink-0 pointer-events-none"
                            >✓</span>
                            <span
                                x-show="isCurrentAnswered && currentSelected === oIdx && oIdx !== Number(currentQuestion.correct_index)"
                                class="text-[#B91C1C] font-extrabold text-base shrink-0 pointer-events-none"
                            >✕</span>
                        </button>
                    </template>
                </div>

                {{-- Banner Feedback Jawaban --}}
                <div
                    x-show="isCurrentAnswered"
                    x-transition.opacity.duration.150ms
                    :class="currentSelected === Number(currentQuestion.correct_index)
                        ? 'bg-[#F0FDF4] border-[#86EFAC] text-[#15803D]'
                        : 'bg-[#FEF2F2] border-[#FCA5A5] text-[#B91C1C]'"
                    class="border-2 rounded-xl px-4 py-3 text-xs sm:text-sm font-bold flex items-center justify-between gap-2"
                >
                    <span x-text="currentSelected === Number(currentQuestion.correct_index)
                        ? '🎉 Jawaban benar! Pertahankan.'
                        : 'Kurang tepat. Jawaban yang benar adalah opsi ' + String.fromCharCode(65 + Number(currentQuestion.correct_index ?? 0)) + '.'"
                    ></span>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- FOOTER NAVIGASI KUIS --}}
<footer class="sticky bottom-0 z-20 bg-white border-t-2 border-[#E4E4E7]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-8 py-3.5 sm:py-4 flex items-center justify-between gap-3">
        <x-ui.button
            variant="secondary"
            x-on:click="prevQuestion()"
            x-bind:disabled="quizIndex === 0"
        >
            ← Sebelumnya
        </x-ui.button>

        <x-ui.button
            variant="primary"
            x-show="!isCurrentAnswered"
            x-on:click="checkAnswer()"
            x-bind:disabled="currentSelected === null"
            class="ml-auto"
        >
            Periksa Jawaban
        </x-ui.button>

        <x-ui.button
            variant="success"
            x-show="isCurrentAnswered"
            x-on:click="nextQuestion()"
            x-text="quizIndex < questions.length - 1 ? 'Pertanyaan Berikutnya →' : 'Selesaikan Kuis ✓'"
            class="ml-auto"
        >
        </x-ui.button>
    </div>
</footer>