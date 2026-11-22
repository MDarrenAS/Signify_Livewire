@props(['modul'])

@php
    $rawParts   = $modul['parts'] ?? [];
    $totalParts = count($rawParts) ?: max(1, (int) ($modul['total_parts'] ?? 1));

    $completedParts = count($rawParts) > 0
        ? collect($rawParts)->where('status', 'completed')->count()
        : (int) ($modul['completed_parts'] ?? 0);

    $progress = count($rawParts) > 0
        ? (int) round(($completedParts / $totalParts) * 100)
        : min(100, max(0, (int) ($modul['progress'] ?? 0)));

    $hasCurrentPart = collect($rawParts)->contains(fn ($p) => ($p['status'] ?? '') === 'current');

    $status = match (true) {
        ($modul['status'] ?? null) === 'locked'                         => 'locked',
        count($rawParts) > 0 && $completedParts >= $totalParts          => 'completed',
        count($rawParts) > 0 && ($completedParts > 0 || $hasCurrentPart) => 'in_progress',
        default                                                         => $modul['status'] ?? 'not_started',
    };

    $defaultOpen = (bool) ($modul['is_expanded'] ?? ($status === 'in_progress'));

    // Tandai bagian aktif pertama secara otomatis jika belum ditentukan eksplisit
    $assignedCurrent = false;
    $activePartSlug  = null;

    $parts = collect($rawParts)->map(function ($part) use ($status, $hasCurrentPart, &$assignedCurrent, &$activePartSlug) {
        $partStatus = $part['status'] ?? 'not_started';

        if ($status === 'completed') {
            $partStatus = 'completed';
        } elseif ($status === 'in_progress' && ! $hasCurrentPart && ! $assignedCurrent && $partStatus === 'not_started') {
            $partStatus = 'current';
            $assignedCurrent = true;
        }

        if ($partStatus === 'current' && $activePartSlug === null) {
            $activePartSlug = $part['slug'] ?? null;
        }

        $part['resolved_status'] = $partStatus;
        return $part;
    })->all();

    $modulSlug   = $modul['slug'] ?? '';
    $modulUrl    = ($modulSlug !== '' && Route::has('modul.show')) ? route('modul.show', $modulSlug) : '#';
    $continueUrl = ($status === 'in_progress' && $modulSlug !== '' && $activePartSlug && Route::has('modul.part'))
        ? route('modul.part', ['modul' => $modulSlug, 'bagian' => $activePartSlug])
        : $modulUrl;

    $cardClasses = match ($status) {
        'in_progress' => 'bg-[#FFF8E6] hover:bg-[#FFF2CC] border-2 border-[#F9B92F] hover:border-[#D99B06] border-b-4 border-b-[#C98A06] hover:-translate-y-1 hover:shadow-[0_10px_24px_rgba(249,185,47,0.24)]',
        'locked'      => 'bg-[#EFEFEF]/75 border-2 border-[#D4D4D8] opacity-80',
        default       => 'bg-white border-2 border-[#E4E4E7] hover:border-[#F9B92F] border-b-4 border-b-[#D4D4D8] hover:border-b-[#C98A06] hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(0,0,0,0.06)]',
    };
@endphp

<article
    x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }"
    x-show="activeFilter === 'all' || activeFilter === '{{ $status }}'"
    x-transition.opacity.duration.200ms
    class="group/card {{ $cardClasses }} rounded-2xl overflow-hidden transition-all duration-200"
>
    {{-- HEADER KARTU MODUL --}}
    <div class="p-4 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-5">
        <div class="flex items-start sm:items-center gap-4 flex-1 min-w-0">
            {{-- Kotak Ikon --}}
            @if ($status === 'completed')
                <div class="relative w-13 h-13 sm:w-14 sm:h-14 shrink-0 bg-[#E9F9EF] border-2 border-[#22B05B] border-b-[3px] border-b-[#15803D] rounded-[14px] flex items-center justify-center transition-transform duration-200 group-hover/card:scale-105 group-hover/card:-rotate-2">
                    <span class="text-base font-extrabold text-[#15803D] tracking-tight select-none">{{ $modul['visual_content'] ?? 'A-Z' }}</span>
                    <span class="absolute -bottom-1.5 -right-1.5 w-[18px] h-[18px] bg-[#22B05B] border-2 border-white rounded-full flex items-center justify-center text-white text-[10px] font-bold leading-none">✓</span>
                </div>
            @elseif ($status === 'in_progress')
                <div class="w-13 h-13 sm:w-14 sm:h-14 shrink-0 bg-white border-2 border-[#F9B92F] group-hover/card:border-[#D99B06] border-b-[3px] border-b-[#C98A06] rounded-[14px] flex items-center justify-center transition-all duration-200 group-hover/card:scale-105 group-hover/card:-rotate-2">
                    <span class="text-base font-extrabold text-[#C98A06] tracking-tight select-none">{{ $modul['visual_content'] ?? '1 2 3' }}</span>
                </div>
            @elseif ($status === 'locked')
                <div class="w-13 h-13 sm:w-14 sm:h-14 shrink-0 bg-[#E4E4E7]/70 border-2 border-[#D4D4D8] rounded-[14px] flex items-center justify-center text-[#71717A]">
                    <flux:icon.lock-closed variant="mini" class="size-5" />
                </div>
            @else
                <div class="w-13 h-13 sm:w-14 sm:h-14 shrink-0 bg-[#F4F4F5] group-hover/card:bg-[#FFF8E6] border-2 border-[#E4E4E7] group-hover/card:border-[#F9B92F] border-b-[3px] border-b-[#D4D4D8] group-hover/card:border-b-[#C98A06] rounded-[14px] flex items-center justify-center transition-all duration-200 group-hover/card:scale-105 group-hover/card:-rotate-2">
                    <span class="text-sm font-extrabold text-[#52525B] group-hover/card:text-[#C98A06] select-none">{{ $modul['visual_content'] ?? '0' . ($modul['order'] ?? '') }}</span>
                </div>
            @endif

            {{-- Judul, Deskripsi & Progress Bar --}}
            <div class="flex flex-col gap-1 min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base sm:text-lg font-extrabold {{ $status === 'locked' ? 'text-[#71717A]' : 'text-[#1C1C1E]' }}">
                        {{ $modul['title'] ?? 'Judul Modul' }}
                    </h3>

                    @if ($status === 'completed')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[#E9F9EF] border-2 border-[#22B05B] text-[11px] font-extrabold text-[#15803D] tracking-wide uppercase">
                            <span>✓ TUNTAS</span>
                            @if (isset($modul['average_score']))
                                <span>• NILAI: {{ $modul['average_score'] }}%</span>
                            @endif
                        </span>
                    @elseif ($status === 'in_progress')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-white border-2 border-[#F9B92F] group-hover/card:border-[#D99B06] text-[11px] font-extrabold text-[#C98A06] tracking-wide uppercase transition-colors">
                            {{ $modul['tag'] ?? 'SEDANG AKTIF' }}
                        </span>
                    @endif
                </div>

                <p class="text-xs sm:text-sm {{ $status === 'locked' ? 'text-[#A1A1AA]' : 'text-[#52525B]' }} font-medium">
                    {{ $modul['description'] ?? '' }}
                </p>

                @if ($status !== 'locked')
                    <div class="flex flex-wrap items-center gap-3 mt-1">
                        <div class="w-40 sm:w-52 h-2 bg-[#E4E4E7] rounded-full overflow-hidden">
                            @if ($progress > 0)
                                <div class="h-full {{ $status === 'completed' ? 'bg-[#22B05B]' : 'bg-[#F9B92F]' }} rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                            @endif
                        </div>

                        <span class="text-xs font-extrabold {{ $status === 'completed' ? 'text-[#15803D]' : 'text-[#1C1C1E]' }}">
                            @if ($status === 'completed')
                                Tuntas 100% {{ isset($modul['average_score']) ? '• Nilai: ' . $modul['average_score'] . '%' : '' }} {{ !empty($modul['xp_reward']) ? '• +' . (int) $modul['xp_reward'] . ' XP' : '' }}
                            @elseif ($status === 'in_progress')
                                {{ $progress }}% Selesai ({{ $completedParts }}/{{ $totalParts }} Bagian) {{ isset($modul['average_score']) ? '• Nilai Kuis: ' . $modul['average_score'] . '%' : '' }}
                            @else
                                0/{{ $totalParts }} Bagian • Belum Dimulai
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tombol Aksi Header --}}
        <div class="flex flex-wrap items-center gap-2.5 self-end lg:self-center shrink-0">
            @if ($status !== 'locked' && ! empty($parts))
                <button
                    type="button"
                    @click="open = !open"
                    class="cursor-pointer bg-white hover:bg-[#FFF8E6] active:translate-y-0.5 active:border-b-2 border-2 border-b-[3px] {{ $status === 'in_progress' ? 'border-[#F9B92F] border-b-[#C98A06]' : 'border-[#E4E4E7] hover:border-[#F9B92F] border-b-[#D4D4D8] hover:border-b-[#C98A06]' }} rounded-[10px] px-3.5 py-2 text-xs font-extrabold text-[#1C1C1E] inline-flex items-center gap-1.5 transition-all"
                >
                    <span x-text="open ? 'Sembunyikan Bagian' : 'Lihat {{ count($parts) }} Bagian'"></span>
                    <span class="text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''">▾</span>
                </button>
            @endif

            @if ($status === 'completed')
                <a href="{{ $modulUrl }}" @if ($modulUrl !== '#') wire:navigate @endif class="group/btn bg-white hover:bg-zinc-50 active:translate-y-0.5 active:border-b-2 border-2 border-[#E4E4E7] border-b-4 border-b-[#D4D4D8] rounded-xl px-4 py-2 text-xs sm:text-sm font-extrabold uppercase text-[#1C1C1E] transition-all inline-flex items-center gap-1.5">
                    <span class="inline-block transition-transform duration-200 group-hover/btn:-rotate-45">↺</span>
                    <span>Ulangi Semua</span>
                </a>
            @elseif ($status === 'in_progress')
                <a href="{{ $continueUrl }}" @if ($continueUrl !== '#') wire:navigate @endif class="group/btn bg-[#F9B92F] hover:bg-[#fabf45] active:translate-y-0.5 active:border-b-2 border-2 border-[#C98A06] border-b-4 border-b-[#C98A06] rounded-xl px-5 py-2 text-xs sm:text-sm font-extrabold uppercase text-[#1C1C1E] transition-all inline-flex items-center gap-1.5">
                    <span>Lanjutkan</span>
                    <span class="inline-block transition-transform duration-200 group-hover/btn:translate-x-1">→</span>
                </a>
            @elseif ($status === 'not_started')
                <a href="{{ $modulUrl }}" @if ($modulUrl !== '#') wire:navigate @endif class="group/btn bg-white group-hover/card:bg-[#F9B92F] hover:bg-[#fabf45] active:translate-y-0.5 active:border-b-2 border-2 border-[#E4E4E7] group-hover/card:border-[#C98A06] border-b-4 border-b-[#D4D4D8] group-hover/card:border-b-[#C98A06] rounded-xl px-5 py-2 text-xs sm:text-sm font-extrabold uppercase text-[#1C1C1E] transition-all inline-flex items-center gap-1.5">
                    <span>Mulai</span>
                    <span class="inline-block transition-transform duration-200 group-hover/btn:translate-x-1">→</span>
                </a>
            @else
                <div class="inline-flex items-center gap-1.5 bg-[#E4E4E7] border-2 border-[#D4D4D8] rounded-xl px-4 py-2 text-xs sm:text-sm font-extrabold uppercase text-[#71717A] cursor-not-allowed select-none">
                    <span>Terkunci</span>
                    <flux:icon.lock-closed variant="micro" class="size-3.5" />
                </div>
            @endif
        </div>
    </div>

    {{-- LACI DAFTAR BAGIAN --}}
    @if ($status !== 'locked' && ! empty($parts))
        <div x-show="open" x-transition.opacity.duration.200ms class="bg-[#FAFAFA] border-t-2 border-[#E4E4E7] p-4 sm:p-6">
            <div class="text-xs font-extrabold text-[#71717A] tracking-widest uppercase mb-3">
                Daftar Materi &amp; Latihan ({{ count($parts) }} Bagian)
            </div>

            <div class="flex flex-col gap-2.5">
                @foreach ($parts as $part)
                    <x-modul-part-item
                        :part="$part"
                        :modul-slug="$modul['slug'] ?? ''"
                        :index="$loop->iteration"
                    />
                @endforeach
            </div>
        </div>
    @endif
</article>