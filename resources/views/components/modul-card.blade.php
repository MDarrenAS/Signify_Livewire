@props(['modul'])

@php
    $status = $modul['status'] ?? 'not_started'; // completed | in_progress | not_started | locked
    $progress = min(100, max(0, (int) ($modul['progress'] ?? 0)));
    $completedParts = (int) ($modul['completed_parts'] ?? 0);
    $totalParts = max(1, (int) ($modul['total_parts'] ?? 1));
    $modulUrl = Route::has('modul.show')
        ? route('modul.show', $modul['slug'] ?? '')
        : '#';

    $cardClasses = match ($status) {
        'in_progress' => 'bg-[#FFF8E6] border-2 border-b-4 border-[#F9B92F]',
        'locked'      => 'bg-[#EFEFEF]/75 border-2 border-[#D4D4D8] opacity-80',
        default       => 'bg-white border-2 border-b-4 border-[#E4E4E7] hover:border-[#D4D4D8]',
    };
@endphp

<article
    x-show="activeFilter === 'all' || activeFilter === '{{ $status }}'"
    x-transition.opacity.duration.200ms
    class="{{ $cardClasses }} rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all"
>
    <!-- Kiri & Tengah: Ikon + Detail + Progress -->
    <div class="flex items-start sm:items-center gap-4 flex-1 min-w-0 w-full">
        <!-- Kotak Ikon 64x64 -->
        @if ($status === 'completed')
            <div class="relative w-16 h-16 rounded-xl bg-[#F0FDF4] flex items-center justify-center shrink-0">
                <span class="font-bold text-xl text-[#22B05B] select-none">{{ $modul['visual_content'] ?? 'A-Z' }}</span>
                <span class="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-[#22B05B] border-2 border-white flex items-center justify-center text-white shadow-2xs">
                    <svg class="w-3 h-3" viewBox="0 0 12 10" fill="none">
                        <path d="M1 5L4.5 8.5L11 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
        @elseif ($status === 'in_progress')
            <div class="w-16 h-16 rounded-xl bg-[#F9B92F]/20 flex items-center justify-center shrink-0">
                <span class="font-bold text-[22px] text-[#C98A06] select-none">{{ $modul['visual_content'] ?? '123' }}</span>
            </div>
        @elseif ($status === 'locked')
            <div class="w-16 h-16 rounded-xl bg-white/50 border-2 border-[#D4D4D8] flex items-center justify-center shrink-0">
                <img src="{{ asset($modul['visual_content'] ?? 'images/dashboard/icon-hands.svg') }}" alt="" class="w-7 h-7 object-contain opacity-45 grayscale" />
            </div>
        @else
            <div class="w-16 h-16 rounded-xl bg-[#F4F4F5] flex items-center justify-center shrink-0">
                @if (($modul['visual_type'] ?? 'icon') === 'icon')
                    <img src="{{ asset($modul['visual_content'] ?? 'images/dashboard/icon-hands.svg') }}" alt="" class="w-7 h-7 object-contain opacity-75" />
                @else
                    <span class="font-bold text-xl text-[#71717A] select-none">{{ $modul['visual_content'] ?? '' }}</span>
                @endif
            </div>
        @endif

        <!-- Info Modul -->
        <div class="flex-1 min-w-0">
            @if (! empty($modul['tag']) && $status === 'in_progress')
                <span class="inline-block bg-[#C98A06]/15 text-[#C98A06] text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-sm mb-1">
                    {{ $modul['tag'] }}
                </span>
            @endif

            <h3 class="text-lg sm:text-xl font-bold {{ $status === 'locked' ? 'text-[#71717A]' : 'text-[#1C1C1E]' }} leading-snug">
                {{ $modul['title'] ?? 'Judul Modul' }}
            </h3>

            <p class="text-sm {{ $status === 'locked' ? 'text-[#A1A1AA]' : 'text-[#52525B]' }} mt-0.5 leading-relaxed">
                {{ $modul['description'] ?? '' }}
            </p>

            <!-- Tactile Progress Bar -->
            @if ($status !== 'locked')
                <div class="mt-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                    <div class="h-3 bg-[#E4E4E7] rounded-full flex-1 overflow-hidden">
                        @if ($progress > 0)
                            <div
                                class="h-full {{ $status === 'completed' ? 'bg-[#22B05B]' : 'bg-[#F9B92F]' }} rounded-full relative overflow-hidden transition-all duration-500"
                                style="width: {{ $progress }}%"
                            >
                                <div class="h-1 w-full bg-white/35 rounded-t-full"></div>
                            </div>
                        @endif
                    </div>

                    <span class="text-xs font-bold whitespace-nowrap {{ match ($status) {
                        'completed'   => 'text-[#15803D]',
                        'in_progress' => 'text-[#C98A06]',
                        default       => 'text-[#71717A]',
                    } }}">
                        @if ($status === 'completed')
                            {{ $completedParts }}/{{ $totalParts }} Bagian • Selesai
                        @elseif ($status === 'in_progress')
                            {{ $completedParts }}/{{ $totalParts }} Bagian • {{ $progress }}%
                        @else
                            0/{{ $totalParts }} Bagian • Belum Dimulai
                        @endif
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Kanan: Tombol Aksi 3D -->
    <div class="w-full sm:w-auto shrink-0">
        @if ($status === 'completed')
            <a href="{{ $modulUrl }}"
               class="w-full sm:w-auto inline-flex items-center justify-center bg-white hover:bg-zinc-50 active:translate-y-0.5 active:border-b-2 border-2 border-b-4 border-[#E4E4E7] rounded-xl px-6 py-3 text-sm font-bold uppercase tracking-wider text-[#52525B] transition-all">
                ULANGI
            </a>
        @elseif ($status === 'in_progress')
            <a href="{{ $modulUrl }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#F9B92F] hover:bg-[#e5a825] active:translate-y-0.5 active:border-b-2 border-2 border-b-4 border-[#C98A06] rounded-xl px-7 py-3.5 text-[15px] font-bold uppercase tracking-wider text-[#1C1C1E] transition-all">
                <span>LANJUTKAN</span>
                <img src="{{ asset('images/dashboard/icon-arrow-right.svg') }}" alt="" class="w-3 h-3" />
            </a>
        @elseif ($status === 'not_started')
            <a href="{{ $modulUrl }}"
               class="w-full sm:w-auto inline-flex items-center justify-center bg-white hover:bg-zinc-50 active:translate-y-0.5 active:border-b-2 border-2 border-b-4 border-[#E4E4E7] rounded-xl px-6 py-3 text-sm font-bold uppercase tracking-wider text-[#1C1C1E] transition-all">
                MULAI
            </a>
        @else
            <div class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-[#E4E4E7] border-2 border-[#D4D4D8] rounded-xl px-6 py-3 text-sm font-bold uppercase tracking-wider text-[#71717A] cursor-not-allowed select-none">
                <span>TERKUNCI</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                </svg>
            </div>
        @endif
    </div>
</article>