<header
    x-data="{ mobileOpen: false }"
    @keydown.escape.window="mobileOpen = false"
    class="sticky top-0 z-50 w-full border-b border-black/8 bg-bg-page/95 backdrop-blur-md transition-all"
>
    <div class="mx-auto flex max-w-[1320px] items-center justify-between px-5 py-3.5 md:px-8 md:py-4.5">
        <a href="{{ Route::has('home') ? route('home') : '#beranda' }}" class="font-brand text-[2.5rem] leading-none text-yellow-primary transition-transform duration-200 hover:scale-103 md:text-6xl">
            Signify
        </a>

        {{-- Desktop Menu --}}
        <nav class="hidden items-center gap-12 lg:flex">
            <ul class="flex items-center gap-10">
                @foreach ([
                    ['id' => 'beranda', 'label' => 'Beranda'],
                    ['id' => 'tentang-kami', 'label' => 'Tentang Kami'],
                    ['id' => 'keunggulan', 'label' => 'Keunggulan'],
                ] as $item)
                    <li>
                        <a
                            href="#{{ $item['id'] }}"
                            :class="activeSection === '{{ $item['id'] }}' ? 'text-yellow-primary after:w-full' : 'text-black after:w-0'"
                            class="relative py-1.5 text-base font-semibold transition-colors duration-200 after:absolute after:bottom-0 after:left-0 after:h-0.5 after:bg-yellow-primary after:transition-all after:duration-200 hover:text-yellow-primary hover:after:w-full"
                        >
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="flex items-center gap-7">
                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-[20px] bg-yellow-primary px-6 py-2.5 text-base font-semibold text-[#FFFCEF] transition-all hover:-translate-y-0.5 hover:bg-[#f3af21] hover:shadow-[0px_4px_12px_rgba(249,185,47,0.4)]">
                        Dashboard
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="px-3 py-2 text-base font-semibold text-black transition-colors hover:text-yellow-primary">
                            Masuk
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-[20px] bg-yellow-primary px-6 py-2.5 text-base font-semibold text-[#FFFCEF] transition-all hover:-translate-y-0.5 hover:bg-[#f3af21] hover:shadow-[0px_4px_12px_rgba(249,185,47,0.4)]">
                            Daftar
                        </a>
                    @endif
                @endauth
            </div>
        </nav>

        {{-- Mobile Hamburger Toggle --}}
        <button
            type="button"
            @click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen.toString()"
            aria-label="Buka menu navigasi"
            class="relative z-50 flex h-5 w-7 flex-col justify-between lg:hidden"
        >
            <span :class="mobileOpen ? 'translate-y-[8.5px] rotate-45' : ''" class="h-[3px] w-full rounded-xs bg-text-main transition-all duration-300"></span>
            <span :class="mobileOpen ? 'opacity-0' : ''" class="h-[3px] w-full rounded-xs bg-text-main transition-all duration-300"></span>
            <span :class="mobileOpen ? '-translate-y-[8.5px] -rotate-45' : ''" class="h-[3px] w-full rounded-xs bg-text-main transition-all duration-300"></span>
        </button>
    </div>

    {{-- Mobile Drawer Backdrop --}}
    <div
        x-show="mobileOpen"
        x-transition.opacity
        @click="mobileOpen = false"
        class="fixed inset-0 z-40 bg-black/40 lg:hidden"
        style="display: none;"
    ></div>

    {{-- Mobile Drawer Panel --}}
    <div
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 right-0 z-40 flex h-screen w-[300px] flex-col gap-8 bg-white px-8 pt-24 pb-10 shadow-2xl lg:hidden"
        style="display: none;"
    >
        <ul class="flex flex-col gap-6">
            <li><a href="#beranda" @click="mobileOpen = false" class="block text-xl font-semibold text-black hover:text-yellow-primary">Beranda</a></li>
            <li><a href="#tentang-kami" @click="mobileOpen = false" class="block text-xl font-semibold text-black hover:text-yellow-primary">Tentang Kami</a></li>
            <li><a href="#keunggulan" @click="mobileOpen = false" class="block text-xl font-semibold text-black hover:text-yellow-primary">Keunggulan</a></li>
        </ul>

        <div class="flex flex-col gap-4 border-t border-gray-100 pt-5">
            @auth
                <a href="{{ url('/dashboard') }}" class="w-full rounded-[20px] bg-yellow-primary py-3 text-center text-base font-semibold text-[#FFFCEF]">
                    Dashboard
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="w-full py-2 text-left text-lg font-semibold text-black hover:text-yellow-primary">
                        Masuk
                    </a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="w-full rounded-[20px] bg-yellow-primary py-3 text-center text-base font-semibold text-[#FFFCEF]">
                        Daftar
                    </a>
                @endif
            @endauth
        </div>
    </div>
</header>