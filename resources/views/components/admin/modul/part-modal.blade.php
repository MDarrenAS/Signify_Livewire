@props([
    'show' => false,
    'partType' => 'lesson',
])

<x-admin.modal
    :show="$show"
    title="Tambah Bagian Baru pada Modul"
    close-action="$set('showPartModal', false)"
    max-width="max-w-md"
>
    <form wire:submit="savePart" class="space-y-4">
        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Tipe Bagian</label>
            <select
                wire:model.live="partType"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-bold bg-white text-[#1C1C1E]"
            >
                <option value="lesson">📖 Materi Belajar (Lesson / Peragaan Isyarat)</option>
                <option value="quiz">🎯 Kuis Evaluasi (Quiz Pilihan Ganda)</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Judul Bagian</label>
            <input
                type="text"
                wire:model="partTitle"
                placeholder="Contoh: Huruf Vokal A - O atau Evaluasi Isyarat Abjad"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
            />
            @error('partTitle') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Reward XP</label>
                <input
                    type="number"
                    wire:model="partXp"
                    class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold focus:outline-none focus:border-[#F9B92F]"
                />
            </div>

            @if ($partType === 'quiz')
                <div>
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Target Lulus (%)</label>
                    <input
                        type="number"
                        wire:model="partPassingScore"
                        class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold focus:outline-none focus:border-[#F9B92F]"
                    />
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E4E4E7]">
            <button
                type="button"
                wire:click="$set('showPartModal', false)"
                class="px-4 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold text-[#52525B] hover:bg-zinc-100 cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="px-5 py-2 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
            >
                Simpan Bagian
            </button>
        </div>
    </form>
</x-admin.modal>
