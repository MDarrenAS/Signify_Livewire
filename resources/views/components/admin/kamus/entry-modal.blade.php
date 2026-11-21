@props([
    'show' => false,
    'editIndex' => null,
    'mediaFile' => null,
    'mediaUrl' => '',
])

<x-admin.modal
    :show="$show"
    title="{{ $editIndex !== null ? 'Edit Kosakata Kamus' : 'Tambah Kosakata Kamus Baru' }}"
    close-action="$set('showModal', false)"
    max-width="max-w-lg"
>
    <form wire:submit="saveEntry" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Kata / Frasa</label>
                <input
                    type="text"
                    wire:model="word"
                    placeholder="Contoh: Selamat Pagi"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-bold focus:outline-none focus:border-[#F9B92F]"
                />
                @error('word') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Jenis Tangan</label>
                <select
                    wire:model="handType"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-bold bg-white text-[#1C1C1E]"
                >
                    <option value="1 Tangan">1 Tangan</option>
                    <option value="2 Tangan">2 Tangan</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Kategori Kamus</label>
            <select
                wire:model="category"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-bold bg-white text-[#1C1C1E]"
            >
                <option value="Abjad & Ejaan">Abjad & Ejaan</option>
                <option value="Angka & Waktu">Angka & Waktu</option>
                <option value="Sapaan & Sehari-hari">Sapaan & Sehari-hari</option>
                <option value="Keluarga & Hubungan">Keluarga & Hubungan</option>
                <option value="Aktivitas & Kata Kerja">Aktivitas & Kata Kerja</option>
                <option value="Benda & Lingkungan">Benda & Lingkungan</option>
            </select>
        </div>

        {{-- Uploader Media Isyarat Kamus: Video, GIF, atau Gambar --}}
        <x-admin.file-uploader
            model="mediaFile"
            url-model="mediaUrl"
            :current-url="$mediaUrl"
            accept="video/mp4,video/webm,image/*,.gif"
            label="Media Peragaan Kamus (Video / GIF / Gambar)"
            hint="Unggah file MP4, WEBM, GIF, PNG, atau JPG peragaan kamus (Maks. 30MB)"
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

        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Panduan Peragaan Gerakan</label>
            <textarea
                wire:model="description"
                rows="3"
                placeholder="Tuliskan runtutan cara menggerakkan tangan secara bertahap..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
            ></textarea>
            @error('description') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Tips Gerakan (Opsional)</label>
            <input
                type="text"
                wire:model="tip"
                placeholder="Contoh: Fokus pada kelenturan pergelangan tangan."
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
            />
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E4E4E7]">
            <button
                type="button"
                wire:click="$set('showModal', false)"
                class="px-4 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold text-[#52525B] hover:bg-zinc-100 cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="px-5 py-2 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
            >
                Simpan Kosakata
            </button>
        </div>
    </form>
</x-admin.modal>
