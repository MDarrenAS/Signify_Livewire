@props([
    'show' => false,
    'mediaFile' => null,
    'mediaUrl' => '',
    'correctIndex' => 0,
])

<x-admin.modal
    :show="$show"
    title="Tambah Soal Kuis Baru"
    close-action="$set('showAddQuestionModal', false)"
    max-width="max-w-lg"
>
    <form wire:submit="saveQuestion" class="space-y-4">
        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Teks Pertanyaan</label>
            <textarea
                wire:model="questionText"
                rows="2"
                placeholder="Contoh: Perhatikan gerakan isyarat pada media di atas. Manakah arti atau huruf yang tepat?"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs sm:text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
            ></textarea>
            @error('questionText') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        {{-- Uploader Media Soal: Video, GIF, atau Gambar --}}
        <x-admin.file-uploader
            model="questionMediaFile"
            url-model="questionMediaUrl"
            :current-url="$mediaUrl"
            accept="video/mp4,video/webm,image/*,.gif"
            label="Media Soal Kuis (Video / GIF / Gambar)"
            hint="Unggah file MP4, GIF, PNG, atau JPG peragaan isyarat soal kuis (Maks. 30MB)"
        >
            @if ($mediaFile)
                @php $isImg = str_contains($mediaFile->getMimeType(), 'image'); @endphp
                <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-44 flex items-center justify-center p-2">
                    @if ($isImg)
                        <img src="{{ $mediaFile->temporaryUrl() }}" class="h-full object-contain" />
                    @else
                        <video src="{{ $mediaFile->temporaryUrl() }}" controls muted loop class="h-full object-contain"></video>
                    @endif
                </div>
            @elseif (trim($mediaUrl) !== '')
                @php $isImageOrGif = preg_match('/\.(gif|png|jpe?g|webp|svg)$/i', $mediaUrl); @endphp
                <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-44 flex items-center justify-center p-2">
                    @if ($isImageOrGif)
                        <img src="{{ $mediaUrl }}" class="h-full object-contain" />
                    @else
                        <video src="{{ $mediaUrl }}" controls muted loop class="h-full object-contain"></video>
                    @endif
                </div>
            @endif
        </x-admin.file-uploader>

        {{-- Pilihan Jawaban A, B, C, D --}}
        <div class="space-y-2 pt-1">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-extrabold text-[#1C1C1E]">
                    Pilihan Jawaban (Pilih bulatan untuk Kunci Jawaban Benar)
                </label>
                <span class="text-[11px] font-extrabold text-[#22B05B]">
                    Kunci: Opsi {{ chr(65 + $correctIndex) }}
                </span>
            </div>

            @foreach ([0, 1, 2, 3] as $optIdx)
                <div class="flex items-center gap-2.5 p-1 rounded-xl border border-[#E4E4E7] bg-[#FAFAFA] hover:bg-white transition-colors">
                    <label class="flex items-center gap-2 pl-2.5 cursor-pointer select-none">
                        <input
                            type="radio"
                            wire:model.live="questionCorrectIndex"
                            value="{{ $optIdx }}"
                            name="correct_answer_radio_pkg"
                            class="text-[#22B05B] focus:ring-[#22B05B] size-4 cursor-pointer"
                        />
                        <span class="w-5 text-xs font-extrabold {{ (int) $correctIndex === $optIdx ? 'text-[#22B05B]' : 'text-[#71717A]' }}">
                            {{ chr(65 + $optIdx) }}.
                        </span>
                    </label>
                    <input
                        type="text"
                        wire:model="questionOptions.{{ $optIdx }}"
                        placeholder="Teks Opsi {{ chr(65 + $optIdx) }}"
                        class="flex-1 px-3 py-2 rounded-lg border border-transparent hover:border-[#E4E4E7] focus:border-[#F9B92F] text-xs sm:text-sm font-semibold bg-white focus:outline-none"
                    />
                </div>
            @endforeach
            @error('questionOptions.*') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E4E4E7]">
            <button
                type="button"
                wire:click="$set('showAddQuestionModal', false)"
                class="px-4 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold text-[#52525B] hover:bg-zinc-100 cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="px-5 py-2 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
            >
                Simpan Soal
            </button>
        </div>
    </form>
</x-admin.modal>
