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
                <div class="bg-white border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#E4E4E7] rounded-full px-3.5 py-1.5 flex items-center gap-2 shadow-xs transition hover:scale-105 select-none" title="Streak 5 Hari">
                    <img src="{{ asset('images/dashboard/icon-fire.svg') }}" alt="Streak" class="w-3.5 h-3.5" />
                    <span class="font-bold text-[#FF7A00] text-xs tracking-wider uppercase">5 HARI</span>
                </div>

                <!-- XP Badge -->
                <div class="bg-white border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#E4E4E7] rounded-full px-3.5 py-1.5 flex items-center gap-2 shadow-xs transition hover:scale-105 select-none" title="Total 420 XP">
                    <img src="{{ asset('images/dashboard/icon-coin-sm.svg') }}" alt="XP" class="w-4.5 h-4.5" />
                    <span class="font-bold text-[#C98A06] text-xs tracking-wider">420</span>
                </div>
            </div>
        </header>

        <!-- Main Grid Layout: Modul Belajar (Left) + Aktivitas (Right Side-by-Side) -->
        <div class="grid grid-cols-1 md:grid-cols-12 lg:grid-cols-12 gap-5 items-start">
            
            <!-- Left Column: Modul Belajar -->
            <section class="md:col-span-7 lg:col-span-7 xl:col-span-7 2xl:col-span-8 space-y-3.5">
                <h2 class="text-lg sm:text-xl font-semibold text-black">
                    Modul Belajar
                </h2>

                <!-- Card 1: Active (Abjad dan Ejaan) -->
                <article class="bg-[#FAFAFA] rounded-[18px] shadow-[0px_3px_12px_rgba(0,0,0,0.06),0px_1px_3px_rgba(0,0,0,0.03)] border border-[#EDEDED] p-3 sm:p-4 flex flex-col sm:flex-row items-center gap-3.5 transition hover:shadow-md">
                    <!-- Left Preview Graphic Box (Compact 92x92) -->
                    <div class="bg-[#F9B92F] rounded-[14px] w-full sm:w-[92px] h-[92px] shrink-0 flex items-center justify-center shadow-inner">
                        <span class="font-bold text-[26px] text-black leading-none select-none">A – Z</span>
                    </div>

                    <!-- Middle Content Details -->
                    <div class="flex-1 min-w-0 w-full flex flex-col justify-between self-stretch">
                        <div>
                            <span class="inline-block bg-[#FDE9BF] text-[#574110] text-[10.5px] font-medium px-2 py-0.5 rounded-full mb-1">
                                Tingkat Dasar
                            </span>
                            <h3 class="text-[16px] font-semibold text-[#1F1F1F] mb-0.5 leading-snug">
                                Abjad dan Ejaan
                            </h3>
                            <p class="text-[12px] text-[#3A3A3C] leading-relaxed">
                                Kuasai 26 isyarat abjad dasar sebagai fondasi awal untuk mengeja namamu.
                            </p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-2">
                            <span class="block text-[11px] font-semibold text-black mb-1">
                                50% Selesai
                            </span>
                            <div class="h-1.5 bg-[#D9D9D9] rounded-full w-full max-w-[200px] overflow-hidden">
                                <div class="h-full bg-[#F9B92F] rounded-full w-1/2 transition-all duration-500"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Compact 3D Action Button -->
                    <div class="w-full sm:w-auto shrink-0 sm:self-center">
                        <a href="{{ route('modul.show', 'abjad-dan-ejaan') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-[#F9B92F] hover:bg-[#E0A72A] active:translate-y-0.5 active:border-b-2 border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#C98A06] rounded-lg px-3 py-1.5 text-[#1C1C1E] font-bold text-[11px] sm:text-xs tracking-wider uppercase transition-all shadow-xs cursor-pointer whitespace-nowrap">
                            <span>LANJUTKAN</span>
                            <img src="{{ asset('images/dashboard/icon-arrow-right.svg') }}" alt="" class="w-2 h-2" />
                        </a>
                    </div>
                </article>

                <!-- Card 2: Inactive / Not Started (Angka dan Hitungan) -->
                <article class="bg-[#FAFAFA] rounded-[18px] shadow-[0px_3px_12px_rgba(0,0,0,0.06),0px_1px_3px_rgba(0,0,0,0.03)] border border-[#EDEDED] p-3 sm:p-4 flex flex-col sm:flex-row items-center gap-3.5 transition hover:shadow-md">
                    <!-- Left Preview Graphic Box -->
                    <div class="bg-[#E6E6E6] rounded-[14px] w-full sm:w-[92px] h-[92px] shrink-0 flex items-center justify-center">
                        <span class="font-bold text-[36px] text-[#C98A06] leading-none select-none">123</span>
                    </div>

                    <!-- Middle Content Details -->
                    <div class="flex-1 min-w-0 w-full flex flex-col justify-between self-stretch">
                        <div>
                            <span class="inline-block bg-[#E6E6E6] text-[#8A8A8A] text-[10.5px] font-medium px-2 py-0.5 rounded-full mb-1">
                                Tingkat Dasar
                            </span>
                            <h3 class="text-[16px] font-semibold text-[#1F1F1F] mb-0.5 leading-snug">
                                Angka dan Hitungan
                            </h3>
                            <p class="text-[12px] text-[#3A3A3C] leading-relaxed">
                                Isyarat angka satuan hingga ribuan dalam transaksi harian
                            </p>
                        </div>

                        <!-- Status text -->
                        <div class="mt-2">
                            <span class="block text-[11px] font-semibold text-[#828282]">
                                Belum Dimulai
                            </span>
                        </div>
                    </div>

                    <!-- Right Compact 3D Action Button -->
                    <div class="w-full sm:w-auto shrink-0 sm:self-center">
                        <a href="{{ route('modul.show', 'angka-dan-hitungan') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center bg-white hover:bg-zinc-50 active:translate-y-0.5 active:border-b-2 border-t-2 border-l-2 border-r-2 border-b-[3px] border-[#E4E4E7] rounded-lg px-3.5 py-1.5 text-[#1C1C1E] font-bold text-[11px] sm:text-xs tracking-wider uppercase transition-all shadow-xs cursor-pointer whitespace-nowrap min-w-[75px]">
                            <span>MULAI</span>
                        </a>
                    </div>
                </article>

                <!-- Card 3: Locked (Kata Ganti Orang) -->
                <article class="bg-[#F3F3F3] rounded-[18px] border border-[#E5E5E5] p-3 sm:p-4 flex flex-col sm:flex-row items-center gap-3.5 opacity-95">
                    <!-- Left Preview Graphic Box -->
                    <div class="bg-[#E6E6E6] rounded-[14px] w-full sm:w-[92px] h-[92px] shrink-0 flex items-center justify-center">
                        <img src="{{ asset('images/dashboard/icon-hands.svg') }}" alt="" class="w-10 h-10 object-contain" />
                    </div>

                    <!-- Middle Content Details -->
                    <div class="flex-1 min-w-0 w-full flex flex-col justify-between self-stretch">
                        <div>
                            <span class="inline-block bg-[#E6E6E6] text-[#8A8A8A] text-[10.5px] font-medium px-2 py-0.5 rounded-full mb-1">
                                Tingkat Dasar
                            </span>
                            <h3 class="text-[16px] font-semibold text-[#1F1F1F] mb-0.5 leading-snug">
                                Kata Ganti Orang
                            </h3>
                            <p class="text-[12px] text-[#3A3A3C] leading-relaxed">
                                Membedakan isyarat saya, kamu, dia, dan mereka
                            </p>
                        </div>
                    </div>

                    <!-- Right Compact 3D Action Button (Locked) -->
                    <div class="w-full sm:w-auto shrink-0 sm:self-center">
                        <div class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-[#E4E4E7] border border-[#D4D4D8] rounded-lg px-2.5 py-1.5 text-[#71717A] font-bold text-[11px] sm:text-xs tracking-wider uppercase cursor-not-allowed select-none whitespace-nowrap">
                            <span>TERKUNCI</span>
                            <img src="{{ asset('images/dashboard/icon-lock.svg') }}" alt="" class="w-2.5 h-2.5" />
                        </div>
                    </div>
                </article>
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
                        <!-- Misi 1 -->
                        <div class="flex items-center gap-3">
                            <div class="bg-[#E6E6E6] rounded-xl w-9 h-10 shrink-0 flex flex-col items-center justify-center relative overflow-hidden shadow-xs">
                                <img src="{{ asset('images/dashboard/icon-coin.svg') }}" alt="" class="w-7 h-7 object-contain -mt-1" />
                                <span class="text-[10px] font-semibold text-[#8A8A8A] leading-none -mt-1">100</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12.5px] font-normal text-[#8A8A8A] mb-1 truncate">
                                    Dapatkan 10 xp
                                </p>
                                <div class="h-3.5 bg-[#D9D9D9] rounded-full relative overflow-hidden flex items-center justify-center">
                                    <div class="h-full bg-[#6B8A47] absolute left-0 w-1/2 rounded-full"></div>
                                    <span class="text-[9.5px] text-white font-medium relative z-10">5/10</span>
                                </div>
                            </div>
                        </div>

                        <!-- Misi 2 -->
                        <div class="flex items-center gap-3">
                            <div class="bg-[#E6E6E6] rounded-xl w-9 h-10 shrink-0 flex flex-col items-center justify-center relative overflow-hidden shadow-xs">
                                <img src="{{ asset('images/dashboard/icon-coin.svg') }}" alt="" class="w-7 h-7 object-contain -mt-1" />
                                <span class="text-[10px] font-semibold text-[#8A8A8A] leading-none -mt-1">100</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12.5px] font-normal text-[#8A8A8A] mb-1 truncate">
                                    Tuntaskan 1 materi
                                </p>
                                <div class="h-3.5 bg-[#D9D9D9] rounded-full relative overflow-hidden flex items-center justify-center">
                                    <span class="text-[9.5px] text-white font-medium relative z-10">0/1</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Liga Perunggu (Leaderboard) -->
                <div class="bg-[#FAFAFA] rounded-[18px] shadow-[0px_3px_12px_rgba(0,0,0,0.06),0px_1px_3px_rgba(0,0,0,0.03)] border border-[#EDEDED] p-3.5 sm:p-4 transition hover:shadow-md">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h3 class="text-base sm:text-[17px] font-semibold text-[#1F1F1F]">
                            Liga Perunggu
                        </h3>
                        <span class="text-[11px] text-[#BB8B23] font-medium whitespace-nowrap">
                            Peringkat #5
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <div class="bg-[#F3F3F3] rounded-lg px-2.5 py-1.5 flex items-center justify-between text-[12.5px] text-black font-normal">
                            <span>1. Anis Baswedan</span>
                            <span class="font-medium text-right">100 XP</span>
                        </div>
                        <div class="bg-[#F3F3F3] rounded-lg px-2.5 py-1.5 flex items-center justify-between text-[12.5px] text-black font-normal">
                            <span>2. Prabowo Subianto</span>
                            <span class="font-medium text-right">80  XP</span>
                        </div>
                        <div class="bg-[#F3F3F3] rounded-lg px-2.5 py-1.5 flex items-center justify-between text-[12.5px] text-black font-normal">
                            <span>3. Ganjar Pranowo</span>
                            <span class="font-medium text-right">70  XP</span>
                        </div>
                        <div class="bg-[#F9B92F] rounded-lg px-2.5 py-1.5 flex items-center justify-between text-[12.5px] text-black font-semibold shadow-xs">
                            <span>5. {{ auth()->user()->name ?? 'Alifah' }}</span>
                            <span class="text-right">60 XP</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts::app>
