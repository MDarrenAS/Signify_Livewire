@props([
    'show' => false,
    'editingQuestionId' => null,
    'mediaFile' => null,
    'mediaUrl' => '',
    'correctIndex' => 0,
    'questionType' => 'sign_to_word',
    'kamusItems' => [],
    'selectedLibraryItemId' => null,
    'optionMediaFiles' => [],
    'optionMediaUrls' => [],
])

<x-admin.modal
    :show="$show"
    :title="$editingQuestionId ? 'Edit Soal Kuis' : 'Tambah Soal Kuis Baru'"
    close-action="$set('showAddQuestionModal', false)"
    max-width="max-w-xl"
>
    <form wire:submit="saveQuestion" class="space-y-4">
        {{-- 1. Pilihan Model Soal / Tipe Kuis --}}
        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1.5">Model / Tipe Soal</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all {{ $questionType === 'sign_to_word' ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E] font-extrabold' : 'border-[#E4E4E7] text-[#52525B] hover:bg-zinc-50' }}">
                    <input type="radio" wire:model.live="questionType" value="sign_to_word" class="text-[#F9B92F] focus:ring-[#F9B92F]" />
                    <span class="text-xs">🎬 Isyarat ➔ Kata</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all {{ $questionType === 'word_to_sign' ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E] font-extrabold' : 'border-[#E4E4E7] text-[#52525B] hover:bg-zinc-50' }}">
                    <input type="radio" wire:model.live="questionType" value="word_to_sign" class="text-[#F9B92F] focus:ring-[#F9B92F]" />
                    <span class="text-xs">🔤 Kata ➔ Isyarat</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all {{ $questionType === 'media_to_sign' ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E] font-extrabold' : 'border-[#E4E4E7] text-[#52525B] hover:bg-zinc-50' }}">
                    <input type="radio" wire:model.live="questionType" value="media_to_sign" class="text-[#F9B92F] focus:ring-[#F9B92F]" />
                    <span class="text-xs">🖼️ Media ➔ Isyarat</span>
                </label>
            </div>
        </div>

        {{-- 2. Pilih Kata Acuan dari Kamus BISINDO (Opsional / Praktis) --}}
        @if (count($kamusItems) > 0)
            <div class="p-3 bg-[#F4F4F5] rounded-xl border border-[#E4E4E7] space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-extrabold text-[#1C1C1E]">
                        Ambil dari Kosakata Kamus (Otomatis Isi)
                    </label>
                    <span class="text-[10px] text-[#71717A]">Opsional</span>
                </div>
                <select
                    wire:change="applyKamusItem($event.target.value)"
                    class="w-full px-3 py-2 rounded-lg border border-[#E4E4E7] text-xs font-semibold bg-white focus:outline-none focus:border-[#F9B92F]"
                >
                    <option value="">-- Pilih Kosakata Kamus Sebagai Kunci Jawaban --</option>
                    @foreach ($kamusItems as $kItem)
                        <option value="{{ $kItem->id }}" {{ (int) $selectedLibraryItemId === $kItem->id ? 'selected' : '' }}>
                            {{ $kItem->title }} ({{ $kItem->category->name ?? 'Kamus' }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-[#71717A]">Memilih kata dari kamus akan otomatis mengisi media peraga isyarat atau teks kunci jawaban.</p>
            </div>
        @endif

        {{-- 3. Teks Pertanyaan --}}
        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Teks Pertanyaan / Petunjuk</label>
            <textarea
                wire:model="questionText"
                rows="2"
                placeholder="Contoh: Perhatikan gerakan isyarat pada media di atas. Manakah arti atau huruf yang tepat?"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs sm:text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
            ></textarea>
            @error('questionText') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        {{-- 4. Uploader / Preview Media Soal (Jika Tipe A atau B) --}}
        @if ($questionType !== 'word_to_sign')
            <x-admin.file-uploader
                model="questionMediaFile"
                url-model="questionMediaUrl"
                :current-url="$mediaUrl"
                accept="video/mp4,video/webm,image/*,.gif"
                label="{{ $questionType === 'media_to_sign' ? 'Media Objek Riil (Foto/Gambar yang Ditebak)' : 'Media Video Isyarat Soal' }}"
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
        @endif

        {{-- 5. Pilihan Jawaban A, B, C, D --}}
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
                @php
                    $optionMediaFile = $optionMediaFiles[$optIdx] ?? null;
                    $optionMediaUrl = $optionMediaUrls[$optIdx] ?? '';
                @endphp
                <div wire:key="question-option-{{ $optIdx }}" class="space-y-2 p-2.5 rounded-xl border border-[#E4E4E7] bg-[#FAFAFA]">
                    <div class="flex items-center gap-2.5">
                        <label class="flex items-center gap-2 pl-1 cursor-pointer select-none">
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
                            placeholder="{{ $questionType === 'sign_to_word' ? 'Teks Kosakata ' . chr(65 + $optIdx) : 'Keterangan/Label Isyarat ' . chr(65 + $optIdx) }}"
                            class="flex-1 px-3 py-2 rounded-lg border border-transparent hover:border-[#E4E4E7] focus:border-[#F9B92F] text-xs sm:text-sm font-semibold bg-white focus:outline-none"
                        />
                    </div>

                    <x-admin.file-uploader
                        model="questionOptionMediaFiles.{{ $optIdx }}"
                        url-model="questionOptionMediaUrls.{{ $optIdx }}"
                        :current-url="$optionMediaUrl"
                        accept="video/mp4,video/webm,image/jpeg,image/png,image/webp,.gif"
                        label="Media Pilihan {{ chr(65 + $optIdx) }} (Opsional)"
                        hint="Unggah gambar atau video untuk pilihan ini (Maks. 30MB)"
                    >
                        @if ($optionMediaFile)
                            @php $isOptionImage = str_contains($optionMediaFile->getMimeType(), 'image'); @endphp
                            <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-32 flex items-center justify-center p-2">
                                @if ($isOptionImage)
                                    <img src="{{ $optionMediaFile->temporaryUrl() }}" class="h-full object-contain" />
                                @else
                                    <video src="{{ $optionMediaFile->temporaryUrl() }}" controls muted loop class="h-full object-contain"></video>
                                @endif
                            </div>
                        @elseif (filled($optionMediaUrl))
                            @php $isOptionImageUrl = preg_match('/\.(gif|png|jpe?g|webp|svg)$/i', $optionMediaUrl); @endphp
                            <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-32 flex items-center justify-center p-2">
                                @if ($isOptionImageUrl)
                                    <img src="{{ $optionMediaUrl }}" class="h-full object-contain" />
                                @else
                                    <video src="{{ $optionMediaUrl }}" controls muted loop class="h-full object-contain"></video>
                                @endif
                            </div>
                        @endif
                    </x-admin.file-uploader>
                    @error("questionOptions.{$optIdx}") <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
                    @error("questionOptionMediaFiles.{$optIdx}") <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
                </div>
            @endforeach
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
                Simpan {{ $editingQuestionId ? 'Perubahan' : 'Soal' }}
            </button>
        </div>
    </form>
</x-admin.modal>
