<div
    x-data="{
        toasts: [],
        add(type, message) {
            const id = Date.now() + Math.random().toString(36).substr(2, 4);
            this.toasts.push({ id, type, message });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    @notify.window="add($event.detail.type || ($event.detail[0] && $event.detail[0].type) || 'success', $event.detail.message || ($event.detail[0] && $event.detail[0].message) || '')"
    class="fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 pointer-events-none max-w-sm w-full px-3"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-[-10px] opacity-0 scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90 translate-y-[-10px]"
            :class="{
                'bg-[#F0FDF4] border-[#22B05B] text-[#15803D]': toast.type === 'success',
                'bg-[#FEF2F2] border-[#EF4444] text-[#B91C1C]': toast.type === 'error',
                'bg-[#FFF8E6] border-[#F9B92F] text-[#B45309]': toast.type === 'warning' || toast.type === 'info',
            }"
            class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border-2 border-b-4 shadow-lg backdrop-blur-xs select-none"
        >
            {{-- Icon --}}
            <div
                :class="{
                    'bg-[#22B05B] text-white': toast.type === 'success',
                    'bg-[#EF4444] text-white': toast.type === 'error',
                    'bg-[#F9B92F] text-[#1C1C1E]': toast.type === 'warning' || toast.type === 'info',
                }"
                class="w-7 h-7 rounded-lg flex items-center justify-center font-extrabold text-sm shrink-0 mt-0.5"
            >
                <span x-show="toast.type === 'success'">✓</span>
                <span x-show="toast.type === 'error'">✕</span>
                <span x-show="toast.type === 'warning' || toast.type === 'info'">!</span>
            </div>

            {{-- Text --}}
            <div class="flex-1 min-w-0">
                <h4
                    class="text-xs font-extrabold uppercase tracking-wide"
                    x-text="toast.type === 'success' ? 'Berhasil' : (toast.type === 'error' ? 'Terjadi Kesalahan' : 'Pemberitahuan')"
                ></h4>
                <p class="text-xs font-semibold leading-relaxed mt-0.5" x-text="toast.message"></p>
            </div>

            {{-- Close Button --}}
            <button
                type="button"
                @click="remove(toast.id)"
                class="text-zinc-400 hover:text-zinc-700 font-extrabold text-sm p-1 leading-none shrink-0 cursor-pointer"
            >
                ✕
            </button>
        </div>
    </template>
</div>
