<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    @include('partials.head')
</head>
<body
    x-data="{
        activeSection: 'beranda',
        updateActiveSection() {
            const sections = ['beranda', 'tentang-kami', 'keunggulan'];
            for (const id of sections) {
                const el = document.getElementById(id);
                if (el) {
                    const rect = el.getBoundingClientRect();
                    if (rect.top <= 140 && rect.bottom >= 140) {
                        this.activeSection = id;
                        break;
                    }
                }
            }
        }
    }"
    @scroll.window.throttle.100ms="updateActiveSection()"
    class="overflow-x-hidden bg-bg-page font-sans text-text-main antialiased"
>
    <x-landing.navbar />

    <main id="beranda">
        {{-- ================= HERO SECTION ================= --}}
        <section class="relative overflow-hidden bg-bg-page">
            <div class="mx-auto flex max-w-[1320px] flex-col items-center justify-between gap-10 px-6 pt-10 pb-5 text-center lg:flex-row lg:gap-15 lg:py-10 lg:text-left">
                <div class="flex w-full flex-1 basis-[480px] items-center justify-center">
                    <div class="relative flex aspect-[556/470] w-full max-w-[520px] items-center justify-center">
                        <img src="{{ asset('images/landing/hero-blob.svg') }}" alt="" aria-hidden="true" class="pointer-events-none absolute inset-0 z-1 h-full w-full object-contain" />
                        <img src="{{ asset('images/landing/hero-illustration.png') }}" alt="Dua orang sedang belajar bahasa isyarat BISINDO bersama" fetchpriority="high" decoding="async" class="animate-float-subtle relative z-2 h-auto w-[86%] object-contain drop-shadow-[0px_8px_16px_rgba(0,0,0,0.08)]" />
                    </div>
                </div>

                <div class="flex flex-1 basis-[600px] flex-col items-center gap-8 lg:items-start">
                    <div class="flex flex-col gap-5">
                        <h1 class="text-[2rem] leading-[1.2] font-semibold tracking-[-0.5px] text-black sm:text-4xl lg:text-[3rem]">
                            Cara seru dan efektif<br />belajar bahasa isyarat!
                        </h1>
                        <p class="text-lg leading-[1.45] font-normal text-text-muted sm:text-xl lg:text-2xl">
                            Belajar BISINDO kini jadi seru dan menyenangkan lewat modul interaktif, latihan visual yang mudah diikuti, dan kamus isyarat terlengkap.
                        </p>
                    </div>

                    <div class="flex w-full flex-wrap items-center justify-center gap-6 sm:w-auto lg:justify-start">
                        <a
                            href="{{ Route::has('register') ? route('register') : '#tentang-kami' }}"
                            class="inline-flex w-full items-center justify-center rounded-[20px] bg-yellow-primary px-8 py-4.5 text-lg font-semibold text-[#FFFCEF] shadow-figma transition-all duration-250 hover:-translate-y-0.5 hover:bg-[#f3af21] hover:shadow-[0px_8px_16px_rgba(249,185,47,0.4)] active:translate-y-px sm:w-auto sm:min-w-[260px] lg:text-[1.35rem]"
                        >
                            MULAI
                        </a>
                        <a
                            href="{{ Route::has('login') ? route('login') : '#tentang-kami' }}"
                            class="inline-flex w-full items-center justify-center rounded-[20px] border border-border-gray bg-white px-8 py-4.5 text-lg font-semibold text-yellow-primary shadow-figma transition-all duration-250 hover:-translate-y-0.5 hover:border-yellow-primary hover:bg-[#fafafa] hover:shadow-[0px_8px_16px_rgba(0,0,0,0.12)] active:translate-y-px sm:w-auto sm:min-w-[260px] lg:text-[1.35rem]"
                        >
                            Aku Sudah Punya Akun
                        </a>
                    </div>
                </div>
            </div>

            <div class="-mb-0.5 block w-full overflow-hidden leading-none" aria-hidden="true">
                <img src="{{ asset('images/landing/section-scallop-wave.svg') }}" alt="" class="block h-[50px] w-full object-fill sm:h-[80px] lg:h-[110px]" />
            </div>
        </section>

        {{-- ================= SECTION 2: KENALAN DENGAN SIGNIFY ================= --}}
        <section id="tentang-kami" class="relative overflow-hidden bg-yellow-pale px-6 pt-15 pb-20">
            <div class="mx-auto flex max-w-[1320px] flex-col items-center justify-between gap-10 text-center lg:flex-row lg:gap-15 lg:text-left">
                <div class="flex flex-1 basis-[580px] flex-col items-center gap-7 lg:items-start">
                    <h2 class="flex flex-wrap items-baseline justify-center gap-3 lg:justify-start">
                        <span class="text-[1.8rem] leading-[1.2] font-extrabold text-green-primary sm:text-4xl lg:text-[2.5rem]">
                            Kenalan Dengan
                        </span>
                        <span class="font-brand text-[2.8rem] leading-[1.1] tracking-[0.5px] text-yellow-primary sm:text-5xl lg:text-[4rem]">
                            Signify
                        </span>
                    </h2>

                    <p class="text-xl leading-[1.35] font-semibold text-text-main sm:text-2xl lg:text-[2rem]">
                        Belajar bahasa isyarat di <span class="font-brand text-[1.05em] font-normal text-yellow-primary">Signify</span> itu mudah dan menyenangkan! Dengan latihan singkat, kamu bisa menguasai gerakan dasar, menambah kosakata, dan berkomunikasi lebih inklusif setiap harinya.
                    </p>

                    <div class="mt-2 flex flex-wrap items-center justify-center gap-5 lg:justify-start">
                        <div class="inline-flex items-center justify-center rounded-[20px] bg-green-active px-6.5 py-3.5 text-base font-bold text-black shadow-[0px_4px_10px_rgba(0,0,0,0.05)] transition-transform duration-200 hover:-translate-y-0.5 hover:bg-[#c4d0b8]">
                            5 - 10 menit sehari
                        </div>
                        <div class="inline-flex items-center justify-center rounded-[20px] bg-green-active px-6.5 py-3.5 text-base font-bold text-black shadow-[0px_4px_10px_rgba(0,0,0,0.05)] transition-transform duration-200 hover:-translate-y-0.5 hover:bg-[#c4d0b8]">
                            Latihan Kapan Saja
                        </div>
                    </div>
                </div>

                <div class="flex w-full flex-1 basis-[480px] items-center justify-center">
                    <div class="relative flex min-h-[400px] w-full max-w-[440px] items-end justify-center sm:min-h-[520px]">
                        <img src="{{ asset('images/landing/door-arch.svg') }}" alt="" aria-hidden="true" class="absolute top-0 left-1/2 z-1 h-auto w-[90%] -translate-x-1/2" />
                        <img src="{{ asset('images/landing/character-signify.png') }}" alt="Instruktur Signify memperagakan gerakan bahasa isyarat" loading="lazy" decoding="async" class="relative z-2 mt-12.5 h-auto w-[95%] object-contain" />

                        <div style="--rot: 5.9deg;" class="animate-pulse-badge absolute top-[10%] right-0 z-3 rounded-[20px] bg-yellow-primary px-3.5 py-1.5 text-[0.95rem] font-bold whitespace-nowrap text-green-primary shadow-[0px_6px_14px_rgba(0,0,0,0.12)] select-none sm:-right-[5%] sm:px-5.5 sm:py-2.5 sm:text-[1.35rem]">
                            GRATIS
                        </div>

                        <div style="--rot: 7.72deg; animation-delay: 0.5s;" class="animate-pulse-badge absolute top-[25%] left-0 z-3 rounded-[20px] bg-yellow-primary px-3.5 py-1.5 text-[0.95rem] font-bold whitespace-nowrap text-green-primary shadow-[0px_6px_14px_rgba(0,0,0,0.12)] select-none sm:-left-[8%] sm:px-5.5 sm:py-2.5 sm:text-[1.35rem]">
                            INKLUSIF
                        </div>

                        <div style="--rot: 0deg; animation-delay: 1s;" class="animate-pulse-badge absolute right-0 bottom-[2%] z-3 rounded-[20px] bg-yellow-primary px-3.5 py-1.5 text-[0.95rem] font-bold whitespace-nowrap text-green-primary shadow-[0px_6px_14px_rgba(0,0,0,0.12)] select-none sm:-right-[8%] sm:bottom-[8%] sm:px-5.5 sm:py-2.5 sm:text-[1.35rem]">
                            MENYENANGKAN
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= SECTION 3: KEUNGGULAN ================= --}}
        <section id="keunggulan" class="bg-bg-page px-6 pt-25 pb-30">
            <div class="mx-auto max-w-[1320px]">
                <div class="mb-15 text-center">
                    <div class="mb-3 inline-flex items-baseline justify-center gap-2.5">
                        <span class="text-[1.4rem] font-normal text-black sm:text-2xl lg:text-[2rem]">Kenapa Belajar di</span>
                        <span class="font-brand text-[2rem] leading-[1.1] text-yellow-primary sm:text-4xl lg:text-[3rem]">Signify?</span>
                    </div>
                    <h2 class="text-[1.8rem] leading-[1.2] font-semibold text-black sm:text-4xl lg:text-[3rem]">
                        Belajar Bahasa Isyarat Lebih Menyenangkan
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    {{-- Card 1 --}}
                    <article class="group relative flex flex-col justify-start overflow-hidden rounded-[20px] bg-yellow-primary px-7 py-9 shadow-card transition-all duration-250 hover:-translate-y-2 hover:shadow-hover lg:min-h-[470px] lg:px-8 lg:pt-10 lg:pb-12">
                        <div class="pointer-events-none absolute top-[15px] -right-[25px] z-1 h-[217px] w-[217px] opacity-65" aria-hidden="true">
                            <img src="{{ asset('images/landing/card-pattern-circles.svg') }}" alt="" />
                        </div>
                        <div class="relative z-2 mb-9 flex h-24 w-24 items-center justify-center rounded-full bg-yellow-cream transition-transform duration-250 group-hover:scale-108 group-hover:rotate-4">
                            <img src="{{ asset('images/landing/open-book.svg') }}" alt="" class="h-12 w-12 object-contain" />
                        </div>
                        <div class="relative z-2 flex flex-col gap-5 text-black">
                            <h3 class="text-2xl leading-[1.35] font-semibold">Kamus Isyarat<br />Visual</h3>
                            <p class="text-base leading-[1.6] font-normal">
                                Lihat ribuan video atau gambar contoh gerakan tangan dari berbagai sudut pandang yang jelas dan mudah ditiru.
                            </p>
                        </div>
                    </article>

                    {{-- Card 2 --}}
                    <article class="group relative flex flex-col justify-start overflow-hidden rounded-[20px] bg-green-primary px-7 py-9 shadow-card transition-all duration-250 hover:-translate-y-2 hover:shadow-hover lg:min-h-[470px] lg:px-8 lg:pt-10 lg:pb-12">
                        <div class="pointer-events-none absolute top-[25px] -right-[20px] z-1 w-[250px] opacity-80" aria-hidden="true">
                            <img src="{{ asset('images/landing/card-pattern-wave.svg') }}" alt="" />
                        </div>
                        <div class="relative z-2 mb-9 flex h-24 w-24 items-center justify-center rounded-full bg-green-active transition-transform duration-250 group-hover:scale-108 group-hover:rotate-4">
                            <img src="{{ asset('images/landing/compass.svg') }}" alt="" class="h-12 w-12 object-contain" />
                        </div>
                        <div class="relative z-2 flex flex-col gap-5 text-bg-page">
                            <h3 class="text-2xl leading-[1.35] font-semibold">Latihan Praktik<br />Interaktif</h3>
                            <p class="text-base leading-[1.6] font-normal">
                                Latih gerakan tanganmu secara langsung di depan layar dengan panduan visual yang seru seperti bermain game!
                            </p>
                        </div>
                    </article>

                    {{-- Card 3 --}}
                    <article class="group relative flex flex-col justify-start overflow-hidden rounded-[20px] bg-yellow-cream px-7 py-9 shadow-card transition-all duration-250 hover:-translate-y-2 hover:shadow-hover md:col-span-2 lg:col-span-1 lg:min-h-[470px] lg:px-8 lg:pt-10 lg:pb-12">
                        <div class="pointer-events-none absolute -top-[15px] -right-[25px] z-1 h-[240px] w-[240px] rotate-[66deg] opacity-75" aria-hidden="true">
                            <img src="{{ asset('images/landing/card-pattern-spiral.svg') }}" alt="" />
                        </div>
                        <div class="relative z-2 mb-9 flex h-24 w-24 items-center justify-center rounded-full bg-yellow-pale transition-transform duration-250 group-hover:scale-108 group-hover:rotate-4">
                            <img src="{{ asset('images/landing/game-console.svg') }}" alt="" class="h-12 w-12 object-contain" />
                        </div>
                        <div class="relative z-2 flex flex-col gap-5 text-black">
                            <h3 class="text-2xl leading-[1.35] font-semibold">Langkah Belajar<br />Rapi &amp; Santai</h3>
                            <p class="text-base leading-[1.6] font-normal">
                                Mulai dari abjad, kata sapaan manis, sampai obrolan sehari-hari langkah demi langkah tanpa rasa terbebani.
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- ================= SECTION 4: CALL TO ACTION BANNER ================= --}}
        <section class="relative overflow-hidden bg-yellow-primary pt-10 lg:pt-15">
            <div class="pointer-events-none absolute top-12.5 left-0 z-1 w-[clamp(140px,18vw,267px)]" aria-hidden="true">
                <img src="{{ asset('images/landing/direction-arrows.svg') }}" alt="" class="h-auto w-full" />
            </div>

            <div class="relative z-2 mx-auto max-w-[820px] px-6">
                <div class="flex flex-col items-center gap-8 text-center">
                    <h2 class="text-[2rem] leading-[1.2] font-semibold text-black sm:text-4xl lg:text-[3rem]">
                        Satu Gerakan, Ribuan Makna
                    </h2>
                    <p class="max-w-[720px] text-lg leading-[1.45] font-normal text-black sm:text-xl lg:text-2xl">
                        Tidak ada kata terlambat untuk mulai belajar. Kuasai bahasa isyarat dengan modul interaktif kami dan jadilah bagian dari masyarakat tanpa batas komunikasi.
                    </p>
                    <a
                        href="{{ Route::has('register') ? route('register') : (Route::has('home') ? route('home') : '#beranda') }}"
                        class="inline-flex w-full items-center justify-center rounded-[20px] bg-white px-6 py-4 text-lg font-bold text-yellow-primary shadow-[0px_8px_20px_rgba(0,0,0,0.15)] transition-all duration-250 hover:-translate-y-0.5 hover:bg-[#FFFCEF] hover:shadow-[0px_12px_24px_rgba(0,0,0,0.2)] active:translate-y-px sm:w-auto sm:px-12 sm:py-5 lg:text-2xl"
                    >
                        Mulai Perjalananmu
                    </a>
                </div>
            </div>

            <div class="relative z-2 mx-auto mt-10 w-full max-w-[1320px] leading-none">
                <img src="{{ asset('images/landing/banner-signers.png') }}" alt="Sekelompok orang mempraktikkan berbagai gerakan bahasa isyarat" loading="lazy" decoding="async" class="block h-auto w-full object-contain" />
            </div>
        </section>
    </main>

    <x-landing.footer />
</body>
</html>