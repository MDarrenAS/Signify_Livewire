@props([
    'model' => 'mediaFile',
    'urlModel' => 'mediaUrl',
    'currentUrl' => '',
    'accept' => 'image/*,video/mp4,video/webm,.gif',
    'label' => 'Upload Media (Gambar / GIF / Video)',
    'hint' => 'Format: PNG, JPG, GIF, atau MP4 (Maks. 20MB)',
    'allowUrl' => true,
])

<div class="space-y-2" x-data="{ mode: '{{ !empty($currentUrl) ? 'preview' : 'upload' }}' }">
    <div class="flex items-center justify-between">
        <label class="block text-xs font-extrabold text-[#1C1C1E]">{{ $label }}</label>
        @if ($allowUrl)
            <div class="flex items-center gap-2 text-[11px] font-bold">
                <button
                    type="button"
                    @click="mode = 'upload'"
                    :class="mode === 'upload' ? 'text-[#C98A06] underline' : 'text-[#71717A] hover:text-[#1C1C1E]'"
                    class="cursor-pointer"
                >
                    Unggah File
                </button>
                <span class="text-[#E4E4E7]">•</span>
                <button
                    type="button"
                    @click="mode = 'url'"
                    :class="mode === 'url' ? 'text-[#C98A06] underline' : 'text-[#71717A] hover:text-[#1C1C1E]'"
                    class="cursor-pointer"
                >
                    Input URL
                </button>
            </div>
        @endif
    </div>

    {{-- Mode Upload File --}}
    <div x-show="mode === 'upload'" class="space-y-2">
        <div class="relative border-2 border-dashed border-[#E4E4E7] hover:border-[#F9B92F] rounded-2xl p-4 sm:p-5 text-center transition-colors bg-[#FAFAFA] hover:bg-[#FFFDF7]">
            <input
                type="file"
                wire:model.live="{{ $model }}"
                accept="{{ $accept }}"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
            />
            
            <div wire:loading.remove wire:target="{{ $model }}" class="flex flex-col items-center justify-center gap-1.5 pointer-events-none">
                <div class="w-10 h-10 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] flex items-center justify-center text-[#C98A06] text-lg">
                    📁
                </div>
                <p class="text-xs font-extrabold text-[#1C1C1E]">
                    Klik atau seret file ke sini untuk mengunggah
                </p>
                <p class="text-[11px] font-medium text-[#71717A]">
                    {{ $hint }}
                </p>
            </div>

            {{-- Loading State saat Upload --}}
            <div wire:loading.flex wire:target="{{ $model }}" class="flex-col items-center justify-center gap-2 py-3">
                <div class="w-7 h-7 border-3 border-[#F9B92F] border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-extrabold text-[#C98A06]">Sedang mengunggah media...</span>
            </div>
        </div>
        @error($model)
            <p class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</p>
        @enderror
    </div>

    {{-- Mode Input URL --}}
    @if ($allowUrl)
        <div x-show="mode === 'url'" style="display: none;" class="space-y-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="{{ $urlModel }}"
                placeholder="Contoh: /storage/signs/abjad/a.mp4 atau https://..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
            />
            <p class="text-[10px] text-[#71717A] font-medium">Bisa gunakan path lokal seperti <code>/storage/signs/...</code></p>
        </div>
    @endif

    {{-- Live Preview Slot atau Prop --}}
    {{ $slot }}
</div>
