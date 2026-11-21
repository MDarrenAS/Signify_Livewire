<div
    x-data="videoPlayer()"
    class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden"
>
    <div
        @click="togglePlay()"
        class="relative aspect-video bg-[#18181B] flex items-center justify-center overflow-hidden cursor-pointer select-none"
    >
        <video
            x-ref="player"
            x-show="!videoError && currentItem.video_url"
            :src="currentItem.video_url"
            :loop="isLooping"
            muted
            playsinline
            @loadedmetadata="duration = $event.target.duration || 4; $event.target.playbackRate = speed"
            @timeupdate="currentTime = $event.target.currentTime"
            @play="isPlaying = true"
            @pause="isPlaying = false"
            @ended="if (!isLooping) isPlaying = false"
            x-on:error="videoError = true"
            class="w-full h-full object-cover"
        ></video>

        <div
            x-show="!videoError && currentItem.video_url && !isPlaying"
            class="absolute inset-0 bg-black/25 flex items-center justify-center transition-opacity"
        >
            <div class="w-16 h-16 rounded-2xl bg-[#F9B92F] border-2 border-b-4 border-[#C98A06] flex items-center justify-center text-[#1C1C1E] shadow-lg">
                <svg class="w-7 h-7 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </div>
        </div>

        <div
            x-show="videoError || !currentItem.video_url"
            class="w-full h-full bg-[#F4F4F5] flex flex-col items-center justify-center gap-2 p-6 text-center"
        >
            <div class="w-24 h-24 rounded-3xl bg-[#FFF8E6] border-2 border-b-4 border-[#F9B92F] flex items-center justify-center">
                <span class="text-5xl font-extrabold text-[#1C1C1E]" x-text="currentItem.symbol"></span>
            </div>
            <p class="text-sm font-extrabold text-[#1C1C1E] mt-1" x-text="currentItem.title"></p>
            <span class="text-xs font-semibold text-[#71717A]">Peragaan Isyarat BISINDO</span>
        </div>
    </div>

    <div class="p-3.5 sm:p-4 bg-white border-t-2 border-[#E4E4E7] flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 flex-1 min-w-[180px]">
            <button
                type="button"
                @click="togglePlay()"
                :disabled="videoError || !currentItem.video_url"
                class="w-9 h-9 rounded-xl bg-[#F4F4F5] hover:bg-[#E4E4E7] disabled:opacity-40 flex items-center justify-center text-[#1C1C1E] shrink-0 cursor-pointer"
                :aria-label="isPlaying ? 'Jeda video' : 'Putar video'"
            >
                <svg x-show="!isPlaying" class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z" />
                </svg>
                <svg x-show="isPlaying" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                </svg>
            </button>

            <div
                @click="seek($event)"
                class="flex-1 h-2.5 bg-[#F4F4F5] rounded-full overflow-hidden border border-[#E4E4E7] cursor-pointer"
            >
                <div
                    class="h-full bg-[#F9B92F] rounded-full transition-all duration-100"
                    :style="'width: ' + videoProgress + '%'"
                ></div>
            </div>

            <span class="text-[11px] font-bold text-[#71717A] tabular-nums shrink-0" x-text="formattedTime"></span>
        </div>

        <div class="flex items-center gap-1.5">
            <template x-for="rate in [0.5, 0.75, 1.0]" :key="rate">
                <button
                    type="button"
                    @click="setSpeed(rate)"
                    :class="speed === rate
                        ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#C98A06]'
                        : 'bg-white border-[#E4E4E7] text-[#71717A] hover:text-[#1C1C1E]'"
                    class="px-2.5 py-1 rounded-lg border text-xs font-extrabold transition-colors cursor-pointer"
                    x-text="rate + 'x'"
                ></button>
            </template>

            <button
                type="button"
                @click="isLooping = !isLooping"
                :class="isLooping
                    ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#C98A06]'
                    : 'bg-white border-[#E4E4E7] text-[#71717A]'"
                title="Ulangi otomatis"
                class="px-2.5 py-1 rounded-lg border text-xs font-extrabold transition-colors cursor-pointer"
            >
                Loop
            </button>
        </div>
    </div>
</div>