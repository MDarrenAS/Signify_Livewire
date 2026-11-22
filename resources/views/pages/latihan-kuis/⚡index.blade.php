<?php

use App\Models\Quiz;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Latihan & Kuis BISINDO')] class extends Component
{
    public string $levelFilter = 'all';

    #[Computed]
    public function quizzes(): Collection
    {
        $userId = auth()->id();

        return Quiz::published()
            ->withCount('questions')
            ->with(['attempts' => function ($query) use ($userId): void {
                if ($userId) {
                    $query->where('user_id', $userId)->orderByDesc('score');
                } else {
                    $query->whereRaw('1 = 0');
                }
            }])
            ->when($this->levelFilter !== 'all', fn ($q) => $q->where('level', $this->levelFilter))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }
};
?>

<div class="space-y-8">
    {{-- Header Banner --}}
    <section class="space-y-3">
        <div class="inline-flex items-center gap-2 rounded-full bg-[#FEF5E0] px-4 py-2 text-xs font-semibold text-[#A87919]">
            <span class="h-2 w-2 rounded-full bg-[#F9B92F]"></span>
            Tantangan & Evaluasi Mandiri
        </div>

        <h1 class="text-3xl font-bold text-[#252525]">
            Latihan & Kuis BISINDO
        </h1>

        <p class="max-w-2xl text-sm leading-relaxed text-[#7C7C7C]">
            Uji pemahaman bahasa isyaratmu melalui latihan visual interaktif, tebak isyarat video, dan kuis kosakata untuk mengumpulkan poin XP.
        </p>

        {{-- Filter Level --}}
        <div class="flex flex-wrap gap-2 pt-2">
            <button
                type="button"
                wire:click="$set('levelFilter', 'all')"
                class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer {{ $levelFilter === 'all' ? 'bg-[#F9B92F] text-[#1C1C1E] border-2 border-b-4 border-[#C98A06]' : 'bg-white text-[#71717A] border-2 border-[#E4E4E7] hover:bg-zinc-50' }}"
            >
                Semua Tingkat
            </button>
            <button
                type="button"
                wire:click="$set('levelFilter', 'Pemula')"
                class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer {{ $levelFilter === 'Pemula' ? 'bg-[#F9B92F] text-[#1C1C1E] border-2 border-b-4 border-[#C98A06]' : 'bg-white text-[#71717A] border-2 border-[#E4E4E7] hover:bg-zinc-50' }}"
            >
                Pemula
            </button>
            <button
                type="button"
                wire:click="$set('levelFilter', 'Menengah')"
                class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer {{ $levelFilter === 'Menengah' ? 'bg-[#F9B92F] text-[#1C1C1E] border-2 border-b-4 border-[#C98A06]' : 'bg-white text-[#71717A] border-2 border-[#E4E4E7] hover:bg-zinc-50' }}"
            >
                Menengah
            </button>
            <button
                type="button"
                wire:click="$set('levelFilter', 'Mahir')"
                class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all cursor-pointer {{ $levelFilter === 'Mahir' ? 'bg-[#F9B92F] text-[#1C1C1E] border-2 border-b-4 border-[#C98A06]' : 'bg-white text-[#71717A] border-2 border-[#E4E4E7] hover:bg-zinc-50' }}"
            >
                Mahir
            </button>
        </div>
    </section>

    {{-- Daftar Paket Kuis --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($this->quizzes as $quiz)
            @php
                $bestAttempt = $quiz->attempts->first();
                $isCompleted = $bestAttempt && $bestAttempt->passed;
            @endphp
            <div class="bg-white border-2 border-b-4 {{ $isCompleted ? 'border-[#86EFAC]' : 'border-[#E4E4E7]' }} hover:border-[#F9B92F] rounded-2xl p-5 flex flex-col justify-between transition-all group relative">
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        {{-- Icon Thumbnail --}}
                        <div class="w-14 h-14 rounded-2xl {{ $isCompleted ? 'bg-[#F0FDF4] border-2 border-[#86EFAC]' : 'bg-[#FFF8E6] border-2 border-[#F9B92F]' }} flex items-center justify-center text-3xl shrink-0 overflow-hidden shadow-2xs group-hover:scale-105 transition-transform">
                            @if (!empty($quiz->icon_url))
                                <img src="{{ $quiz->icon_url }}" alt="{{ $quiz->title }}" class="w-full h-full object-cover" />
                            @else
                                {{ $quiz->icon_emoji ?: '🎯' }}
                            @endif
                        </div>

                        {{-- Level & Status Selesai --}}
                        <div class="text-right space-y-1">
                            <span class="inline-block px-2.5 py-0.5 rounded-lg text-xs font-extrabold bg-[#F4F4F5] text-[#52525B]">
                                {{ $quiz->level }}
                            </span>
                            @if ($isCompleted)
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-[#F0FDF4] border border-[#86EFAC] text-[#15803D]">
                                        ✓ Selesai ({{ round($bestAttempt->score) }}%)
                                    </span>
                                </div>
                            @else
                                <div class="text-xs font-extrabold text-[#C98A06]">
                                    +{{ $quiz->xp_reward }} XP
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-extrabold text-[#1C1C1E] group-hover:text-[#A87919] transition-colors">
                            {{ $quiz->title }}
                        </h3>
                        @if (!empty($quiz->description))
                            <p class="text-xs text-[#71717A] font-medium leading-relaxed mt-1 line-clamp-2">
                                {{ $quiz->description }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-[#F4F4F5] flex items-center justify-between">
                    <span class="text-xs font-extrabold text-[#71717A]">
                        📝 {{ $quiz->questions_count }} Pertanyaan
                    </span>

                    <a
                        href="{{ route('latihan.show', $quiz->slug) }}"
                        wire:navigate
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl {{ $isCompleted ? 'bg-white hover:bg-zinc-50 border-2 border-[#E4E4E7] text-[#52525B]' : 'bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-[#1C1C1E]' }} text-xs font-extrabold transition-all cursor-pointer shadow-xs"
                    >
                        {{ $isCompleted ? '🔄 Ulangi Latihan' : 'Mulai Kuis →' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center border-2 border-dashed border-[#E4E4E7] rounded-3xl space-y-3 bg-white">
                <span class="text-4xl block">🎯</span>
                <h3 class="text-base font-extrabold text-[#1C1C1E]">Belum Ada Paket Kuis</h3>
                <p class="text-xs text-[#71717A] max-w-sm mx-auto">
                    Paket kuis yang dibuat oleh admin akan otomatis muncul di sini.
                </p>
            </div>
        @endforelse
    </div>
</div>
