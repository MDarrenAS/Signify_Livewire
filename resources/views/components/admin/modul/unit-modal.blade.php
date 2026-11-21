@props([
    'show' => false,
    'editIndex' => null,
])

<x-admin.modal
    :show="$show"
    title="{{ $editIndex !== null ? 'Edit Unit Pembelajaran' : 'Tambah Unit Baru' }}"
    close-action="$set('showUnitModal', false)"
    max-width="max-w-lg"
>
    <form wire:submit="saveUnit" class="space-y-4">
        {{-- Pilihan Nomor Unit & Tingkat Kesulitan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Nomor Unit</label>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-[#71717A]">Unit ke-</span>
                    <input
                        type="number"
                        wire:model.live="unitNumber"
                        min="1"
                        max="99"
                        class="w-20 px-3 py-2 rounded-xl border-2 border-[#E4E4E7] text-sm font-extrabold text-center focus:outline-none focus:border-[#F9B92F]"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Pilih Tingkat (Level)</label>
                <select
                    wire:model.live="unitLevel"
                    class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs font-extrabold bg-white text-[#1C1C1E] focus:outline-none focus:border-[#F9B92F] cursor-pointer"
                >
                    <option value="TINGKAT DASAR">Tingkat Dasar (Pemula)</option>
                    <option value="TINGKAT MENENGAH">Tingkat Menengah (Intermediate)</option>
                    <option value="TINGKAT MAHIR">Tingkat Mahir (Advanced)</option>
                    <option value="CUSTOM">Kustom (Tulis Sendiri...)</option>
                </select>
            </div>
        </div>

        {{-- Jika pilih kustom tingkat --}}
        @if ($unitLevel === 'CUSTOM')
            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Tingkat Kustom</label>
                <input
                    type="text"
                    wire:model.live.debounce.200ms="customLevel"
                    placeholder="Contoh: KHUSUS TUNA RUNGU / KOMUNIKASI KERJA"
                    class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-bold uppercase focus:outline-none focus:border-[#F9B92F]"
                />
            </div>
        @endif

        {{-- Badge Unit (Preview & Bisa Diedit) --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-extrabold text-[#1C1C1E]">Label Badge Unit</label>
                <span class="text-[10px] font-bold text-[#71717A]">Tampil di atas judul unit</span>
            </div>
            <input
                type="text"
                wire:model="unitBadge"
                placeholder="Contoh: UNIT 2 • TINGKAT MENENGAH"
                class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs font-extrabold uppercase text-[#C98A06] bg-[#FFFDF7] focus:outline-none focus:border-[#F9B92F]"
            />
            @error('unitBadge') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        {{-- Judul Unit --}}
        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Judul Unit</label>
            <input
                type="text"
                wire:model="unitTitle"
                placeholder="Contoh: Komunikasi Sehari-hari & Lingkungan"
                class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
            />
            @error('unitTitle') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        {{-- Deskripsi Unit --}}
        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Deskripsi Ringkas</label>
            <textarea
                wire:model="unitDescription"
                rows="3"
                placeholder="Kuasai kosakata aktivitas harian, keluarga, dan waktu dalam percakapan BISINDO."
                class="w-full px-3.5 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
            ></textarea>
            @error('unitDescription') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E4E4E7]">
            <button
                type="button"
                wire:click="$set('showUnitModal', false)"
                class="px-4 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold text-[#52525B] hover:bg-zinc-100 cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="px-5 py-2 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
            >
                Simpan Unit
            </button>
        </div>
    </form>
</x-admin.modal>
