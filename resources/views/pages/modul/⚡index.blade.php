<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Modul Belajar')] class extends Component
{
    public function with(): array
    {
        // KONTRAK DATA FE -> BE:
        // Saat integrasi database, BE cukup mengganti isi array ini
        // (atau memanggil Service) dengan struktur key yang sama.
        return [
            'streak' => auth()->user()->streak ?? 0,
            'poin' => auth()->user()->poin ?? 0,
            'mascotMessage' => 'Ayo ' . (auth()->user()->name ?? 'Alifah') . '! Selesaikan 2 materi lagi di Angka & Hitungan untuk membuka Unit 2!',
            'units' => [
                [
                    'id' => 1,
                    'badge' => 'UNIT 1 • TINGKAT DASAR',
                    'title' => 'Fondasi Komunikasi BISINDO',
                    'subtitle' => null,
                    'is_locked' => false,
                    'status_text' => '1/3 Modul Selesai',
                    'modules' => [
                        [
                            'slug' => 'abjad-dan-ejaan',
                            'title' => 'Abjad & Ejaan',
                            'description' => 'Kuasai 26 isyarat huruf dasar BISINDO (6 Bagian).',
                            'status' => 'completed',
                            'tag' => null,
                            'completed_parts' => 6,
                            'total_parts' => 6,
                            'progress' => 100,
                            'visual_type' => 'text',
                            'visual_content' => 'A-Z',
                        ],
                        [
                            'slug' => 'angka-dan-hitungan',
                            'title' => 'Angka & Hitungan',
                            'description' => 'Isyarat angka satuan hingga ribuan dalam transaksi harian (4 Bagian).',
                            'status' => 'in_progress',
                            'tag' => 'SEDANG DIPELAJARI',
                            'completed_parts' => 2,
                            'total_parts' => 4,
                            'progress' => 50,
                            'visual_type' => 'text',
                            'visual_content' => '123',
                        ],
                        [
                            'slug' => 'kata-ganti-orang',
                            'title' => 'Kata Ganti Orang',
                            'description' => 'Membedakan isyarat saya, kamu, dia, dan mereka (3 Bagian).',
                            'status' => 'not_started',
                            'tag' => null,
                            'completed_parts' => 0,
                            'total_parts' => 3,
                            'progress' => 0,
                            'visual_type' => 'icon',
                            'visual_content' => 'images/dashboard/icon-hands.svg',
                        ],
                    ],
                ],
                [
                    'id' => 2,
                    'badge' => 'UNIT 2 • TINGKAT MENENGAH',
                    'title' => 'Percakapan & Ungkapan Sosial',
                    'subtitle' => 'Selesaikan semua modul di Unit 1 untuk membuka unit ini.',
                    'is_locked' => true,
                    'status_text' => 'Terkunci',
                    'modules' => [
                        [
                            'slug' => 'salam-dan-etiket',
                            'title' => 'Salam & Etiket',
                            'description' => 'Ungkapan sopan santun, salam perpisahan, dan etika komunikasi Tuli.',
                            'status' => 'locked',
                            'tag' => null,
                            'completed_parts' => 0,
                            'total_parts' => 4,
                            'progress' => 0,
                            'visual_type' => 'icon',
                            'visual_content' => 'images/dashboard/icon-hands.svg',
                        ],
                    ],
                ],
            ],
        ];
    }
};
?>

<div x-data="{ activeFilter: 'all' }" class="space-y-6 pb-8">
    <!-- A. TOP HEADER -->
    <header class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-[28px] lg:text-[32px] font-semibold text-black tracking-tight leading-tight">
                Modul Belajar
            </h1>
            <p class="text-[#686868] text-sm sm:text-base font-normal mt-1">
                Kuasai bahasa isyarat BISINDO langkah demi langkah.
            </p>
        </div>

        <x-gamified-badges :streak="$streak" :poin="$poin" />
    </header>

    <!-- B. MASCOT UNA CALLOUT & FILTER CHIPS -->
    <section class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5 flex-1 max-w-2xl">
            <!-- Maskot Una SVG -->
            <div class="w-16 h-16 sm:w-[72px] sm:h-[72px] shrink-0 select-none" aria-hidden="true">
                <svg viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-xs">
                    <circle cx="36" cy="38" r="30" fill="#C98A06" />
                    <circle cx="36" cy="35" r="30" fill="#F9B92F" />
                    <g transform="rotate(15 54 20)">
                        <ellipse cx="54" cy="21" rx="5.5" ry="8" fill="#C98A06" />
                        <ellipse cx="54" cy="19.5" rx="5" ry="7.5" fill="#FFDEA7" />
                        <circle cx="55" cy="19" r="2" fill="#F9B92F" />
                    </g>
                    <ellipse cx="10" cy="42" rx="4.5" ry="3.5" fill="#FFDEA7" stroke="#C98A06" stroke-width="1.5" />
                    <circle cx="27" cy="30" r="3.5" fill="#1C1C1E" />
                    <circle cx="45" cy="30" r="3.5" fill="#1C1C1E" />
                    <circle cx="28.2" cy="28.8" r="1.2" fill="#FFFFFF" />
                    <circle cx="46.2" cy="28.8" r="1.2" fill="#FFFFFF" />
                    <ellipse cx="20" cy="36" rx="4.5" ry="3" fill="#FF7A00" fill-opacity="0.35" />
                    <ellipse cx="52" cy="36" rx="4.5" ry="3" fill="#FF7A00" fill-opacity="0.35" />
                    <path d="M31 37.5C32.5 40 39.5 40 41 37.5" stroke="#1C1C1E" stroke-width="2.5" stroke-linecap="round" />
                </svg>
            </div>

            <!-- Speech Bubble -->
            <div class="relative bg-white border-2 border-b-[3px] border-[#E4E4E7] rounded-2xl px-4 py-3 text-sm sm:text-[15px] text-[#C98A06] font-medium leading-relaxed shadow-2xs flex-1">
                <span class="hidden sm:block absolute -left-[7px] top-1/2 -translate-y-1/2 w-3 h-3 bg-white border-l-2 border-b-2 border-[#E4E4E7] rotate-45"></span>
                {{ $mascotMessage }}
            </div>
        </div>

        <!-- Filter Chips (Client-Side Alpine.js) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0 shrink-0">
            @foreach ([
                'all'         => 'Semua',
                'in_progress' => 'Sedang Aktif',
                'completed'   => 'Selesai',
            ] as $filterKey => $filterLabel)
                <button
                    type="button"
                    @click="activeFilter = '{{ $filterKey }}'"
                    :class="activeFilter === '{{ $filterKey }}'
                        ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E]'
                        : 'bg-white border-[#E4E4E7] text-[#52525B] hover:bg-zinc-50'"
                    class="border-2 border-b-[3px] rounded-xl px-4 py-2 text-sm font-bold transition-all cursor-pointer whitespace-nowrap"
                >
                    {{ $filterLabel }}
                </button>
            @endforeach
        </div>
    </section>

    <!-- C. DAFTAR UNIT & MODUL -->
    <div class="space-y-8">
        @forelse ($units as $unit)
            @php $unitLocked = (bool) ($unit['is_locked'] ?? false); @endphp

            <section class="space-y-4">
                <!-- Banner Unit -->
                @if (! $unitLocked)
                    <div class="bg-[#F9B92F] border-2 border-b-4 border-[#C98A06] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1.5">
                            <span class="inline-block bg-white border border-[#C98A06] rounded-md px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#1C1C1E]">
                                {{ $unit['badge'] }}
                            </span>
                            <h2 class="text-xl sm:text-2xl font-semibold text-[#1C1C1E] leading-snug">
                                {{ $unit['title'] }}
                            </h2>
                        </div>

                        <div class="self-start sm:self-center bg-white/95 border border-[#C98A06]/40 rounded-full px-3.5 py-1.5 inline-flex items-center gap-2 shrink-0">
                            <span class="w-2 h-2 rounded-full bg-[#22B05B]"></span>
                            <span class="text-[13px] font-bold text-[#1C1C1E]">{{ $unit['status_text'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="bg-[#EFEFEF] border-2 border-b-4 border-[#D4D4D8] rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start sm:items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-white/70 border border-[#D4D4D8] flex items-center justify-center shrink-0 text-[#71717A]">
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <span class="inline-block bg-white border border-[#D4D4D8] rounded-sm px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#71717A]">
                                    {{ $unit['badge'] }}
                                </span>
                                <h2 class="text-lg sm:text-xl font-bold text-[#71717A] tracking-tight">
                                    {{ $unit['title'] }}
                                </h2>
                                @if (! empty($unit['subtitle']))
                                    <p class="text-[13px] text-[#A1A1AA]">{{ $unit['subtitle'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="self-start sm:self-center bg-white border border-[#D4D4D8] rounded-full px-4 py-1.5 inline-flex items-center gap-2 shrink-0 text-[#71717A]">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-[13px] font-bold">{{ $unit['status_text'] }}</span>
                        </div>
                    </div>
                @endif

                <!-- Daftar Kartu Modul -->
                <div class="space-y-4">
                    @forelse ($unit['modules'] ?? [] as $modul)
                        <x-modul-card :modul="$modul" />
                    @empty
                        <div class="bg-white border-2 border-dashed border-[#E4E4E7] rounded-2xl p-6 text-center text-sm text-[#71717A]">
                            Belum ada materi pada unit ini.
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="bg-white border-2 border-dashed border-[#E4E4E7] rounded-2xl p-10 text-center text-sm text-[#71717A]">
                Daftar unit pembelajaran belum tersedia.
            </div>
        @endforelse
    </div>
</div>