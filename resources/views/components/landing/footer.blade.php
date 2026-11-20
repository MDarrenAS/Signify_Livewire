<footer class="bg-[#1a1a1a] px-6 pt-15 pb-8 text-[#f0f0f0]">
    <div class="mx-auto max-w-[1320px]">
        <div class="flex flex-wrap justify-between gap-12 border-b border-white/12 pb-10">
            <div class="max-w-[360px] flex-1 basis-[320px]">
                <span class="mb-3 inline-block font-brand text-[2.8rem] leading-none text-yellow-primary">Signify</span>
                <p class="text-[0.95rem] leading-relaxed text-[#a8a8a8]">
                    Platform edukasi interaktif Bahasa Isyarat Indonesia (BISINDO) yang inklusif, mudah, dan menyenangkan untuk semua kalangan.
                </p>
            </div>

            <div class="flex flex-wrap gap-14">
                <div>
                    <h4 class="mb-4 text-lg font-semibold text-yellow-primary">Navigasi</h4>
                    <ul class="flex flex-col gap-2.5 text-[0.95rem] text-[#cccccc]">
                        <li><a href="#beranda" class="transition-colors hover:text-yellow-primary">Beranda</a></li>
                        <li><a href="#tentang-kami" class="transition-colors hover:text-yellow-primary">Tentang Kami</a></li>
                        <li><a href="#keunggulan" class="transition-colors hover:text-yellow-primary">Keunggulan</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-lg font-semibold text-yellow-primary">Belajar</h4>
                    <ul class="flex flex-col gap-2.5 text-[0.95rem] text-[#cccccc]">
                        <li><a href="{{ Route::has('login') ? route('login') : '#keunggulan' }}" class="transition-colors hover:text-yellow-primary">Modul Interaktif</a></li>
                        <li><a href="{{ Route::has('login') ? route('login') : '#keunggulan' }}" class="transition-colors hover:text-yellow-primary">Kamus Isyarat</a></li>
                        <li><a href="#tentang-kami" class="transition-colors hover:text-yellow-primary">Komunitas Inklusif</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="mb-4 text-lg font-semibold text-yellow-primary">Hubungi Kami</h4>
                    <ul class="flex flex-col gap-2.5 text-[0.95rem] text-[#cccccc]">
                        <li><a href="mailto:halo@signify.id" class="transition-colors hover:text-yellow-primary">halo@signify.id</a></li>
                        <li><a href="#tentang-kami" class="transition-colors hover:text-yellow-primary">Pusat Bantuan</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="pt-6 text-center text-sm text-[#888888]">
            <p>&copy; {{ date('Y') }} Signify. Seluruh Hak Cipta Dilindungi. Dibuat dengan cinta untuk Indonesia yang inklusif.</p>
        </div>
    </div>
</footer>