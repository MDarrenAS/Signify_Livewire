@props([
    'show' => false,
    'editIndex' => null,
    'iconFile' => null,
    'iconUrl' => '',
])

<x-admin.modal
    :show="$show"
    title="{{ $editIndex !== null ? 'Edit Paket Kuis Mandiri' : 'Tambah Paket Kuis Mandiri' }}"
    close-action="$set('showModal', false)"
    max-width="max-w-lg"
>
    <form wire:submit="savePackage" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-3">
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Judul Paket Kuis</label>
                <input
                    type="text"
                    wire:model="title"
                    placeholder="Contoh: Tantangan Ekspresi Wajah & Emosi"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
                />
                @error('title') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Ikon Emoji</label>
                <input
                    type="text"
                    wire:model="iconEmoji"
                    placeholder="🎯"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-extrabold text-center focus:outline-none focus:border-[#F9B92F]"
                />
            </div>
        </div>

        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Deskripsi Singkat</label>
            <input
                type="text"
                wire:model="subtitle"
                placeholder="Contoh: Latihan mengenali ekspresi dan tata bahasa visual BISINDO."
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
            />
        </div>

        {{-- Uploader Ikon / Cover Kuis (Gambar atau GIF) --}}
        <x-admin.file-uploader
            model="iconFile"
            url-model="iconUrl"
            :current-url="$iconUrl"
            accept="image/png,image/jpeg,image/webp,image/gif"
            label="Ikon / Cover Visual Kuis (Gambar atau GIF)"
            hint="Unggah file PNG, JPG, WebP, atau GIF sebagai thumbnail paket kuis (Maks. 10MB)"
        >
            @if ($iconFile)
                <div class="flex items-center gap-3 p-2 bg-[#F0FDF4] border border-[#86EFAC] rounded-xl">
                    <img src="{{ $iconFile->temporaryUrl() }}" class="w-12 h-12 rounded-lg object-cover border border-[#22B05B]" />
                    <div class="text-xs">
                        <span class="font-bold text-[#15803D] block">File Ikon Terpilih</span>
                        <span class="text-[#71717A] text-[10px]">{{ $iconFile->getClientOriginalName() }}</span>
                    </div>
                </div>
            @elseif (!empty($iconUrl))
                <div class="flex items-center gap-3 p-2 bg-[#FFF8E6] border border-[#F9B92F] rounded-xl">
                    <img src="{{ $iconUrl }}" class="w-12 h-12 rounded-lg object-cover border border-[#C98A06]" />
                    <span class="text-xs font-bold text-[#C98A06]">Ikon Aktif</span>
                </div>
            @endif
        </x-admin.file-uploader>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Tingkat Kesulitan</label>
                <select
                    wire:model="level"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-bold bg-white text-[#1C1C1E]"
                >
                    <option value="Pemula">Pemula</option>
                    <option value="Menengah">Menengah</option>
                    <option value="Mahir">Mahir</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Reward XP</label>
                <input
                    type="number"
                    wire:model="xpReward"
                    class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold focus:outline-none focus:border-[#F9B92F]"
                />
            </div>
        </div>

        <div class="pt-1">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="isActive" class="rounded text-[#22B05B] focus:ring-[#22B05B] size-4" />
                <span class="text-xs font-extrabold text-[#1C1C1E]">Publikasikan Paket Kuis Ini (Bisa Dikerjakan Siswa)</span>
            </label>
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
                Simpan Paket Kuis
            </button>
        </div>
    </form>
</x-admin.modal>
