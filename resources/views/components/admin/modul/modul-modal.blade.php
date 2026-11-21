@props([
    'show' => false,
    'editIndex' => null,
    'units' => [],
    'iconFile' => null,
    'iconUrl' => '',
])

<x-admin.modal
    :show="$show"
    title="{{ $editIndex !== null ? 'Edit Modul Belajar' : 'Tambah Modul Belajar Baru' }}"
    close-action="$set('showModulModal', false)"
    max-width="max-w-lg"
>
    <form wire:submit="saveModul" class="space-y-4">
        {{-- Pilihan Unit --}}
        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Tempatkan pada Unit</label>
            <select
                wire:model="formUnitIdx"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-extrabold bg-white text-[#1C1C1E]"
            >
                @foreach ($units as $idx => $unit)
                    <option value="{{ $idx }}">{{ $unit['badge'] ?? 'Unit ' . ($idx+1) }} — {{ $unit['title'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Judul Modul</label>
                <input
                    type="text"
                    wire:model="formTitle"
                    placeholder="Contoh: Keluarga & Kekerabatan"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
                />
                @error('formTitle') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Ikon Emoji</label>
                <input
                    type="text"
                    wire:model="formIcon"
                    placeholder="🤟"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-extrabold text-center focus:outline-none focus:border-[#F9B92F]"
                />
            </div>
        </div>

        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Subjudul / Ringkasan Isi</label>
            <input
                type="text"
                wire:model="formSubtitle"
                placeholder="Contoh: Ayah, Ibu, Kakak, Adik (4 Bagian)"
                class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
            />
        </div>

        {{-- Komponen Upload Ikon / Gambar / GIF Modul --}}
        <x-admin.file-uploader
            model="formIconFile"
            url-model="formIconUrl"
            :current-url="$iconUrl"
            accept="image/*,.gif,.svg"
            label="Cover / Ikon Modul (Gambar atau GIF)"
            hint="Unggah file JPG, PNG, GIF, atau SVG sebagai thumbnail visual modul"
        >
            @if ($iconFile)
                <div class="flex items-center gap-3 p-2 bg-[#F0FDF4] border border-[#86EFAC] rounded-xl">
                    <img src="{{ $iconFile->temporaryUrl() }}" class="w-12 h-12 rounded-lg object-cover border border-[#22B05B]" />
                    <div class="text-xs">
                        <span class="font-bold text-[#15803D] block">File Siap Disimpan</span>
                        <span class="text-[#71717A] text-[10px]">{{ $iconFile->getClientOriginalName() }}</span>
                    </div>
                </div>
            @elseif (!empty($iconUrl))
                <div class="flex items-center gap-3 p-2 bg-[#FFF8E6] border border-[#F9B92F] rounded-xl">
                    <img src="{{ $iconUrl }}" class="w-12 h-12 rounded-lg object-cover border border-[#C98A06]" />
                    <span class="text-xs font-bold text-[#C98A06]">Thumbnail Aktif</span>
                </div>
            @endif
        </x-admin.file-uploader>

        <div class="grid grid-cols-2 gap-3 pt-1">
            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Total Reward XP</label>
                <input
                    type="number"
                    wire:model="formXpReward"
                    class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold focus:outline-none focus:border-[#F9B92F]"
                />
            </div>

            <div class="flex flex-col justify-end">
                <label class="inline-flex items-center gap-2 cursor-pointer pb-2">
                    <input type="checkbox" wire:model="formIsActive" class="rounded text-[#22B05B] focus:ring-[#22B05B] size-4" />
                    <span class="text-xs font-extrabold text-[#1C1C1E]">Buka Modul (Aktif)</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E4E4E7]">
            <button
                type="button"
                wire:click="$set('showModulModal', false)"
                class="px-4 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold text-[#52525B] hover:bg-zinc-100 cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="px-5 py-2 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
            >
                Simpan Modul
            </button>
        </div>
    </form>
</x-admin.modal>
