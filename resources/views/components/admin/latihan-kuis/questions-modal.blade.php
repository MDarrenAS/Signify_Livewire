@props([
    'show' => false,
    'activePackage' => null,
])

@if ($activePackage)
    <x-admin.modal
        :show="$show"
        title="Kelola Soal: {{ $activePackage['title'] }}"
        close-action="$set('showQuestionsModal', false)"
        max-width="max-w-2xl"
    >
        <div class="space-y-4">
            {{-- Header Rincian Paket --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-[#FFF8E6] border border-[#F9B92F] rounded-2xl">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-[#C98A06]">
                        Tingkat {{ $activePackage['level'] }} • +{{ $activePackage['xp_reward'] }} XP
                    </span>
                    <h4 class="text-sm font-extrabold text-[#1C1C1E]">Daftar Butir Pertanyaan Kuis</h4>
                    <p class="text-xs text-[#71717A]">{{ count($activePackage['questions'] ?? []) }} pertanyaan terdaftar saat ini</p>
                </div>

                <button
                    type="button"
                    wire:click="openAddQuestion"
                    class="px-4 py-2.5 rounded-xl bg-[#22B05B] hover:bg-[#1ea052] border-2 border-b-4 border-[#15803D] active:translate-y-0.5 active:border-b-2 text-white text-xs font-extrabold cursor-pointer transition-all self-start sm:self-auto shadow-xs"
                >
                    + Tambah Soal Baru
                </button>
            </div>

            {{-- Daftar Butir Soal --}}
            <div class="space-y-3 max-h-[50vh] overflow-y-auto pr-1">
                @forelse ($activePackage['questions'] ?? [] as $qIdx => $q)
                    @php
                        $media = $q['video_url'] ?? '';
                        $isImgOrGif = preg_match('/\.(gif|png|jpe?g|webp|svg)$/i', $media);
                    @endphp
                    <div class="p-4 rounded-2xl border-2 border-[#E4E4E7] bg-white hover:border-[#F9B92F] transition-all space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1">
                                {{-- Media Preview Kecil --}}
                                @if (!empty($media))
                                    <div class="w-12 h-12 rounded-xl bg-[#18181B] overflow-hidden flex items-center justify-center shrink-0">
                                        @if ($isImgOrGif)
                                            <img src="{{ $media }}" class="w-full h-full object-cover" />
                                        @else
                                            <video src="{{ $media }}" muted class="w-full h-full object-cover"></video>
                                        @endif
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-[#FFF8E6] border border-[#F9B92F] flex items-center justify-center text-xs font-extrabold text-[#C98A06] shrink-0">
                                        Q{{ $qIdx + 1 }}
                                    </div>
                                @endif

                                <div>
                                    <span class="text-[10px] font-extrabold uppercase text-[#C98A06]">Soal #{{ $qIdx + 1 }}</span>
                                    <p class="text-xs sm:text-sm font-extrabold text-[#1C1C1E] leading-snug">{{ $q['question'] }}</p>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="removeQuestion({{ $qIdx }})"
                                wire:confirm="Yakin ingin menghapus soal ini?"
                                class="p-1.5 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 text-xs font-bold cursor-pointer"
                                title="Hapus Soal"
                            >
                                ✕
                            </button>
                        </div>

                        {{-- Pilihan Opsi & Kunci --}}
                        <div class="grid grid-cols-2 gap-1.5 pt-1">
                            @foreach ($q['options'] ?? [] as $oIdx => $opt)
                                @php $isCorrect = (int) ($q['correct_index'] ?? 0) === $oIdx; @endphp
                                <div class="px-2.5 py-1.5 rounded-lg text-[11px] font-semibold border flex items-center gap-1.5 {{ $isCorrect ? 'bg-[#F0FDF4] border-[#22B05B] text-[#15803D] font-extrabold' : 'bg-zinc-50 border-[#E4E4E7] text-[#52525B]' }}">
                                    <span class="font-bold">{{ chr(65 + $oIdx) }}.</span>
                                    <span class="truncate">{{ $opt }}</span>
                                    @if ($isCorrect) <span class="ml-auto text-[#22B05B]">✓</span> @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center border-2 border-dashed border-[#E4E4E7] rounded-2xl space-y-2">
                        <span class="text-3xl block">🎯</span>
                        <p class="text-xs font-extrabold text-[#1C1C1E]">Belum Ada Soal dalam Paket Ini</p>
                        <p class="text-[11px] text-[#71717A]">Klik tombol "+ Tambah Soal Baru" di atas untuk menambahkan pertanyaan.</p>
                    </div>
                @endforelse
            </div>

            <div class="flex justify-end pt-2 border-t border-[#E4E4E7]">
                <button
                    type="button"
                    wire:click="$set('showQuestionsModal', false)"
                    class="px-5 py-2 rounded-xl bg-white border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </x-admin.modal>
@endif
