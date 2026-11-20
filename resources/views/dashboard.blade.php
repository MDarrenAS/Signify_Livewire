@php
    $streak = $streak ?? 0;
    $xp = $xp ?? 0;
    $userRank = $userRank ?? 99;
    $poin = $poin ?? 0;

    $modules = $modules ?? [
        [
            'slug' => 'abjad-dan-ejaan',
            'title' => 'Abjad dan Ejaan',
            'level' => 'Tingkat Dasar',
            'description' => 'Kuasai 26 isyarat abjad dasar sebagai fondasi awal untuk mengeja namamu.',
            'progress' => 50,
            'is_locked' => false,
            'visual_type' => 'text',
            'visual_content' => 'A – Z',
        ],
        [
            'slug' => 'angka-dan-hitungan',
            'title' => 'Angka dan Hitungan',
            'level' => 'Tingkat Dasar',
            'description' => 'Isyarat angka satuan hingga ribuan dalam transaksi harian',
            'progress' => 0,
            'is_locked' => false,
            'visual_type' => 'text',
            'visual_content' => '123',
        ],
        [
            'slug' => 'kata-ganti-orang',
            'title' => 'Kata Ganti Orang',
            'level' => 'Tingkat Dasar',
            'description' => 'Membedakan isyarat saya, kamu, dia, dan mereka',
            'progress' => 0,
            'is_locked' => true,
            'visual_type' => 'icon',
            'visual_content' => 'images/dashboard/icon-hands.svg',
        ],
    ];

    $missions = $missions ?? [
        [
            'title' => 'Dapatkan 10 xp',
            'reward' => 100,
            'current' => 5,
            'target' => 10,
        ],
        [
            'title' => 'Tuntaskan 1 materi',
            'reward' => 100,
            'current' => 0,
            'target' => 1,
        ],
    ];

    $leaderboard = $leaderboard ?? [
        ['rank' => 1, 'name' => 'Anis Baswedan', 'xp' => 100, 'is_current_user' => false],
        ['rank' => 2, 'name' => 'Prabowo Subianto', 'xp' => 80, 'is_current_user' => false],
        ['rank' => 3, 'name' => 'Ganjar Pranowo', 'xp' => 70, 'is_current_user' => false],
        ['rank' => $userRank, 'name' => auth()->user()->name ?? 'Alifah', 'xp' => 0, 'is_current_user' => true],
    ];
@endphp

<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-5">
        <!-- Top Header: Welcome Message & Gamified Badges -->
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-[28px] lg:text-[30px] font-semibold text-black tracking-tight leading-tight">
                    Selamat Datang, <span class="text-[#F9B92F]">{{ auth()->user()->name ?? 'Alifah' }}</span>
                </h1>
                <p class="text-[#686868] text-xs sm:text-sm font-normal mt-0.5">
                    Lanjutkan belajar mu hari ini
                </p>
            </div>

            <!-- Gamified Badges (Streak & XP) -->
            <div class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
                <!-- Streak Badge -->
                <div class="bg-white border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#E4E4E7] rounded-full px-3.5 py-1.5 flex items-center gap-2 shadow-xs transition hover:scale-105 select-none" title="Streak {{ $streak }} Hari">
                    <img src="{{ asset('images/dashboard/icon-fire.svg') }}" alt="Streak" class="w-3.5 h-3.5" />
                    <span class="font-bold text-[#FF7A00] text-xs tracking-wider uppercase">{{ $streak }} HARI</span>
                </div>

                <!-- poin Badge -->
                <div class="bg-white border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#E4E4E7] rounded-full px-3.5 py-1.5 flex items-center gap-2 shadow-xs transition hover:scale-105 select-none" title="Total {{ $poin }} poin">
                    <img src="{{ asset('images/dashboard/icon-coin-sm.svg') }}" alt="poin" class="w-4.5 h-4.5" />
                    <span class="font-bold text-[#C98A06] text-xs tracking-wider">{{ $poin }} poin</span>
                </div>
            </div>
        </header>

        <!-- Main Grid Layout: Modul Belajar (Left) + Aktivitas (Right) -->
        <div class="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-5 items-start">
            
            <!-- Left Column: Modul Belajar -->
            <section class="md:col-span-7 lg:col-span-7 xl:col-span-7 2xl:col-span-8 space-y-3.5">
                <h2 class="text-lg sm:text-xl font-semibold text-black">
                    Modul Belajar
                </h2>

                @foreach ($modules as $modul)
                    @php
                        $progress = min(100, max(0, (int) ($modul['progress'] ?? 0)));
                        $isLocked = (bool) ($modul['is_locked'] ?? false);
                        $isActive = ! $isLocked && $progress > 0;
                        $modulUrl = Route::has('modul.show')
                            ? route('modul.show', $modul['slug'] ?? '')
                            : (Route::has('modul.index') ? route('modul.index') : '#');
                    @endphp

                    <article class="{{ $isLocked ? 'bg-[#F3F3F3] border-[#E5E5E5] opacity-95' : 'bg-[#FAFAFA] shadow-[0px_3px_12px_rgba(0,0,0,0.06),0px_1px_3px_rgba(0,0,0,0.03)] border-[#EDEDED] transition hover:shadow-md' }} rounded-[18px] border p-3 sm:p-4 flex flex-col sm:flex-row items-center gap-3.5">
                        <!-- Left Preview Graphic Box -->
                        <div class="{{ $isActive ? 'bg-[#F9B92F] shadow-inner' : 'bg-[#E6E6E6]' }} rounded-[14px] w-full sm:w-[92px] h-[92px] shrink-0 flex items-center justify-center">
                            @if (($modul['visual_type'] ?? 'text') === 'icon')
                                <img src="{{ asset($modul['visual_content'] ?? 'images/dashboard/icon-hands.svg') }}" alt="" class="w-10 h-10 object-contain" />
                            @else
                                @php $visualText = $modul['visual_content'] ?? ''; @endphp
                                <span class="font-bold {{ mb_strlen($visualText) <= 3 ? 'text-[36px]' : 'text-[26px]' }} {{ $isActive ? 'text-black' : 'text-[#C98A06]' }} leading-none select-none">
                                    {{ $visualText }}
                                </span>
                            @endif
                        </div>

                        <!-- Middle Content Details -->
                        <div class="flex-1 min-w-0 w-full flex flex-col justify-between self-stretch">
                            <div>
                                <span class="inline-block {{ $isActive ? 'bg-[#FDE9BF] text-[#574110]' : 'bg-[#E6E6E6] text-[#8A8A8A]' }} text-[10.5px] font-medium px-2 py-0.5 rounded-full mb-1">
                                    {{ $modul['level'] ?? 'Tingkat Dasar' }}
                                </span>
                                <h3 class="text-[16px] font-semibold text-[#1F1F1F] mb-0.5 leading-snug">
                                    {{ $modul['title'] }}
                                </h3>
                                <p class="text-[12px] text-[#3A3A3C] leading-relaxed">
                                    {{ $modul['description'] }}
                                </p>
                            </div>

                            <!-- Progress / Status -->
                            @if (! $isLocked)
                                <div class="mt-2">
                                    @if ($progress > 0)
                                        <span class="block text-[11px] font-semibold text-black mb-1">
                                            {{ $progress }}% Selesai
                                        </span>
                                        <div class="h-1.5 bg-[#D9D9D9] rounded-full w-full max-w-[200px] overflow-hidden">
                                            <div class="h-full bg-[#F9B92F] rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                                        </div>
                                    @else
                                        <span class="block text-[11px] font-semibold text-[#828282]">
                                            Belum Dimulai
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Right Compact 3D Action Button -->
                        <div class="w-full sm:w-auto shrink-0 sm:self-center">
                            @if ($isLocked)
                                <div class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-[#E4E4E7] border border-[#D4D4D8] rounded-lg px-2.5 py-1.5 text-[#71717A] font-bold text-[11px] sm:text-xs tracking-wider uppercase cursor-not-allowed select-none whitespace-nowrap">
                                    <span>TERKUNCI</span>
                                    <img src="{{ asset('images/dashboard/icon-lock.svg') }}" alt="" class="w-2.5 h-2.5" />
                                </div>
                            @elseif ($progress > 0)
                                <a href="{{ $modulUrl }}"
                                   class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-[#F9B92F] hover:bg-[#E0A72A] active:translate-y-0.5 active:border-b-2 border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#C98A06] rounded-lg px-3 py-1.5 text-[#1C1C1E] font-bold text-[11px] sm:text-xs tracking-wider uppercase transition-all shadow-xs cursor-pointer whitespace-nowrap">
                                    <span>LANJUTKAN</span>
                                    <img src="{{ asset('images/dashboard/icon-arrow-right.svg') }}" alt="" class="w-2 h-2" />
                                </a>
                            @else
                                <a href="{{ $modulUrl }}"
                                   class="w-full sm:w-auto inline-flex items-center justify-center bg-white hover:bg-zinc-50 active:translate-y-0.5 active:border-b-2 border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#E4E4E7] rounded-lg px-3.5 py-1.5 text-[#1C1C1E] font-bold text-[11px] sm:text-xs tracking-wider uppercase transition-all shadow-xs cursor-pointer whitespace-nowrap min-w-[75px]">
                                    <span>MULAI</span>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <!-- Right Column: Aktivitas (Misi Harian & Liga Perunggu) -->
            <aside class="md:col-span-5 lg:col-span-5 xl:col-span-5 2xl:col-span-4 space-y-4">
                <h2 class="text-lg sm:text-xl font-semibold text-black">
                    Aktivitas
                </h2>

                <!-- Card 1: Misi Harian -->
                <div class="bg-[#FAFAFA] rounded-[18px] shadow-[0px_3px_12px_rgba(0,0,0,0.06),0px_1px_3px_rgba(0,0,0,0.03)] border border-[#EDEDED] p-3.5 sm:p-4 transition hover:shadow-md">
                    <h3 class="text-base sm:text-[17px] font-semibold text-[#1F1F1F] mb-3">
                        Misi Harian
                    </h3>

                    <div class="space-y-3">
                        @foreach ($missions as $mission)
                            @php
                                $current = (int) ($mission['current'] ?? 0);
                                $target = max(1, (int) ($mission['target'] ?? 1));
                                $missionPercent = min(100, max(0, (int) round(($current / $target) * 100)));
                            @endphp

                            <div class="flex items-center gap-3">
                                <div class="bg-[#E6E6E6] rounded-xl w-9 h-10 shrink-0 flex flex-col items-center justify-center relative overflow-hidden shadow-xs">
                                    <img src="{{ asset('images/dashboard/icon-coin.svg') }}" alt="" class="w-7 h-7 object-contain -mt-1" />
                                    <span class="text-[10px] font-semibold text-[#8A8A8A] leading-none -mt-1">{{ $mission['reward'] ?? 100 }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12.5px] font-normal text-[#8A8A8A] mb-1 truncate">
                                        {{ $mission['title'] }}
                                    </p>
                                    <div class="h-3.5 bg-[#D9D9D9] rounded-full relative overflow-hidden flex items-center justify-center">
                                        @if ($missionPercent > 0)
                                            <div class="h-full bg-[#6B8A47] absolute left-0 rounded-full transition-all duration-500" style="width: {{ $missionPercent }}%"></div>
                                        @endif
                                        <span class="text-[9.5px] text-white font-medium relative z-10">{{ $current }}/{{ $target }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Card 2: Liga Perunggu (Leaderboard) -->
                <div class="bg-[#FAFAFA] rounded-[18px] shadow-[0px_3px_12px_rgba(0,0,0,0.06),0px_1px_3px_rgba(0,0,0,0.03)] border border-[#EDEDED] p-3.5 sm:p-4 transition hover:shadow-md">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h3 class="text-base sm:text-[17px] font-semibold text-[#1F1F1F]">
                            Liga Perunggu
                        </h3>
                        <span class="text-[11px] text-[#BB8B23] font-medium whitespace-nowrap">
                            Peringkat #{{ $userRank }}
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        @foreach ($leaderboard as $player)
                            @php $isMe = (bool) ($player['is_current_user'] ?? false); @endphp
                            <div class="{{ $isMe ? 'bg-[#F9B92F] font-semibold shadow-xs' : 'bg-[#F3F3F3] font-normal' }} rounded-lg px-2.5 py-1.5 flex items-center justify-between text-[12.5px] text-black">
                                <span>{{ $player['rank'] ?? $loop->iteration }}. {{ $player['name'] }}</span>
                                <span class="{{ $isMe ? '' : 'font-medium' }} text-right">{{ $player['xp'] ?? 0 }} XP</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts::app>