@props([
    'part',
    'modulSlug' => '',
    'index'     => 1,
])

@php
    $partStatus  = $part['resolved_status'] ?? $part['status'] ?? 'not_started';
    $isQuiz      = ($part['type'] ?? 'lesson') === 'quiz';
    $isCurrent   = $partStatus === 'current';
    $isCompleted = $partStatus === 'completed';

    $partUrl = match (true) {
        Route::has('modul.part') => route('modul.part', ['modul' => $modulSlug, 'bagian' => $part['slug'] ?? '']),
        Route::has('modul.show') => route('modul.show', $modulSlug),
        default                  => '#',
    };

    $rowClasses = match (true) {
        $isCurrent   => 'bg-[#FFF8E6] hover:bg-[#FFF0C2] border-2 border-[#F9B92F] hover:border-[#D99B06] border-b-4 border-b-[#C98A06] hover:-translate-y-0.5 hover:shadow-[0_6px_14px_rgba(201,138,6,0.18)]',
        $isCompleted => 'bg-white hover:bg-[#FFF8E6] border-2 border-[#E4E4E7] hover:border-[#F9B92F] border-b-[3px] border-b-[#D4D4D8] hover:border-b-[#C98A06] hover:-translate-y-0.5',
        default      => 'bg-white hover:bg-zinc-50 border-2 border-[#E4E4E7] hover:border-[#D4D4D8] border-b-[3px] border-b-[#D4D4D8] hover:-translate-y-0.5',
    };
@endphp

<div class="group/part {{ $rowClasses }} rounded-xl px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all duration-200">
    <div class="flex items-start sm:items-center gap-3 min-w-0">
        {{-- Ikon Status Bagian --}}
        @if ($isQuiz)
            <div class="w-6 h-6 rounded-full bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-xs shrink-0 mt-0.5 sm:mt-0 transition-transform duration-200 group-hover/part:scale-110">
                🏆
            </div>
        @elseif ($isCompleted)
            <div class="w-6 h-6 rounded-full bg-[#22B05B] flex items-center justify-center text-white text-[11px] font-extrabold shrink-0 mt-0.5 sm:mt-0 transition-transform duration-200 group-hover/part:scale-110">
                ✓
            </div>
        @elseif ($isCurrent)
            <div class="w-6 h-6 rounded-full bg-[#F9B92F] group-hover/part:bg-[#E09B08] flex items-center justify-center text-[#1C1C1E] text-[10px] font-extrabold shrink-0 mt-0.5 sm:mt-0 transition-all duration-200 group-hover/part:scale-110">
                ▶
            </div>
        @else
            <div class="w-6 h-6 rounded-full bg-[#F4F4F5] border-2 border-[#D4D4D8] flex items-center justify-center text-[#71717A] text-[11px] font-extrabold shrink-0 mt-0.5 sm:mt-0">
                {{ $index }}
            </div>
        @endif

        {{-- Judul & Badge --}}
        <div class="flex flex-wrap items-center gap-2 min-w-0">
            <span class="text-sm {{ $isCurrent ? 'font-extrabold' : 'font-bold group-hover/part:font-extrabold' }} text-[#1C1C1E] transition-all">
                {{ $part['title'] ?? 'Bagian ' . $index }}
            </span>

            @if (! empty($part['badge_text']))
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs transition-colors duration-200 {{ match (true) {
                    $isQuiz && $isCompleted => 'bg-[#E9F9EF] border border-[#22B05B] font-extrabold text-[#15803D]',
                    $isCurrent              => 'bg-white border-2 border-[#F9B92F] group-hover/part:border-[#D99B06] font-extrabold text-[#C98A06]',
                    $isCompleted            => 'bg-[#F4F4F5] group-hover/part:bg-white border border-[#E4E4E7] group-hover/part:border-[#F9B92F] font-medium group-hover/part:font-bold text-[#52525B] group-hover/part:text-[#C98A06]',
                    default                 => 'bg-[#F4F4F5] border border-[#E4E4E7] font-medium text-[#52525B]',
                } }}">
                    {{ $part['badge_text'] }}
                </span>
            @endif
        </div>
    </div>

    {{-- Tombol Aksi Bagian --}}
    <a
        href="{{ $partUrl }}"
        @if ($partUrl !== '#') wire:navigate @endif
        class="self-end sm:self-auto shrink-0 rounded-lg font-extrabold uppercase transition-all duration-200 active:translate-y-0.5 active:border-b-2 {{ match (true) {
            $isCurrent   => 'bg-[#F9B92F] group-hover/part:bg-[#fabf45] border-2 border-[#C98A06] border-b-[3px] border-b-[#C98A06] px-4 py-2 text-xs text-[#1C1C1E] group-hover/part:shadow-xs',
            $isCompleted => 'bg-white group-hover/part:bg-[#F9B92F] border-2 border-[#E4E4E7] group-hover/part:border-[#C98A06] border-b-[3px] border-b-[#D4D4D8] group-hover/part:border-b-[#C98A06] px-3 group-hover/part:px-4 py-1.5 group-hover/part:py-2 text-[11px] group-hover/part:text-xs text-[#1C1C1E]',
            default      => 'bg-white hover:bg-zinc-100 border-2 border-[#E4E4E7] border-b-[3px] border-b-[#D4D4D8] px-3 py-1.5 text-[11px] text-[#1C1C1E]',
        } }}"
    >
        @if ($isCurrent)
            <span class="inline-flex items-center gap-1">
                <span>{{ $isQuiz ? 'Mulai Kuis' : 'Mulai Bagian ' . $index }}</span>
                <span class="transition-transform duration-200 group-hover/part:translate-x-1">→</span>
            </span>
        @elseif ($isCompleted)
            <span class="group-hover/part:hidden">{{ $isQuiz ? 'Ulangi Kuis' : 'Ulangi' }}</span>
            <span class="hidden group-hover/part:inline-flex items-center gap-1">
                <span>{{ $isQuiz ? 'Ulangi Kuis' : 'Ulangi Bagian ' . $index }}</span>
                <span class="transition-transform duration-200 group-hover/part:translate-x-1">→</span>
            </span>
        @else
            <span>Mulai</span>
        @endif
    </a>
</div>