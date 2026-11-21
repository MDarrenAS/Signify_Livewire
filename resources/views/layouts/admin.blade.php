<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light" style="color-scheme: light;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Studio — Signify' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-[#FAFAFA] font-['Plus_Jakarta_Sans',sans-serif] text-[#1C1C1E] antialiased">
    {{-- Pop Up Notifikasi Global (Toast) --}}
    <x-admin.toast />

    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
        {{-- Backdrop Mobile --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            style="display: none;"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden"
        ></div>

        {{-- SIDEBAR ADMIN --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:sticky top-0 left-0 z-50 h-screen w-64 shrink-0 bg-white border-r-2 border-[#E4E4E7] flex flex-col transition-transform duration-200 ease-in-out"
        >
            {{-- Header Sidebar / Logo Signify --}}
            <div class="h-20 px-5 border-b-2 border-[#E4E4E7] flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="block select-none group">
                    <span class="font-['Caveat_Brush'] text-[36px] leading-none text-[#F9B92F] block tracking-normal transition-transform group-hover:scale-105 origin-left">
                        Signify
                    </span>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#C98A06] mt-2 block">
                        Admin Studio
                    </span>
                </a>

                <button
                    type="button"
                    @click="sidebarOpen = false"
                    class="lg:hidden p-1.5 rounded-xl text-[#71717A] hover:bg-zinc-100 hover:text-[#1C1C1E]"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Navigasi Menu --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p class="px-3 pb-1.5 text-[10px] font-extrabold uppercase tracking-wider text-[#71717A]">Ringkasan</p>

                <a
                    href="{{ route('admin.dashboard') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-extrabold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#FFF8E6] text-[#C98A06] border-2 border-[#F9B92F] shadow-xs' : 'text-[#52525B] hover:bg-zinc-100 hover:text-[#1C1C1E]' }}"
                >
                    <flux:icon.squares-2x2 class="size-4 shrink-0" />
                    <span>Dashboard</span>
                </a>

                <p class="px-3 pt-5 pb-1.5 text-[10px] font-extrabold uppercase tracking-wider text-[#71717A]">Konten Belajar</p>

                <a
                    href="{{ route('admin.modul.index') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-extrabold transition-all {{ request()->routeIs('admin.modul.*') ? 'bg-[#FFF8E6] text-[#C98A06] border-2 border-[#F9B92F] shadow-xs' : 'text-[#52525B] hover:bg-zinc-100 hover:text-[#1C1C1E]' }}"
                >
                    <flux:icon.book-open class="size-4 shrink-0" />
                    <span>Modul Belajar</span>
                </a>

                <a
                    href="{{ route('admin.kamus.index') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-extrabold transition-all {{ request()->routeIs('admin.kamus.*') ? 'bg-[#FFF8E6] text-[#C98A06] border-2 border-[#F9B92F] shadow-xs' : 'text-[#52525B] hover:bg-zinc-100 hover:text-[#1C1C1E]' }}"
                >
                    <flux:icon.chat-bubble-left-right class="size-4 shrink-0" />
                    <span>Kamus BISINDO</span>
                </a>

                <a
                    href="{{ route('admin.latihan.index') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-extrabold transition-all {{ request()->routeIs('admin.latihan.*') ? 'bg-[#FFF8E6] text-[#C98A06] border-2 border-[#F9B92F] shadow-xs' : 'text-[#52525B] hover:bg-zinc-100 hover:text-[#1C1C1E]' }}"
                >
                    <flux:icon.clipboard-document-check class="size-4 shrink-0" />
                    <span>Latihan Kuis</span>
                </a>

                <p class="px-3 pt-5 pb-1.5 text-[10px] font-extrabold uppercase tracking-wider text-[#71717A]">Sistem & Pengguna</p>

                <a
                    href="{{ route('admin.pengguna.index') }}"
                    wire:navigate
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-extrabold transition-all {{ request()->routeIs('admin.pengguna.*') ? 'bg-[#FFF8E6] text-[#C98A06] border-2 border-[#F9B92F] shadow-xs' : 'text-[#52525B] hover:bg-zinc-100 hover:text-[#1C1C1E]' }}"
                >
                    <flux:icon.users class="size-4 shrink-0" />
                    <span>Pengguna & Progres</span>
                </a>
            </nav>

            {{-- Footer Sidebar: Shortcut Halaman Belajar --}}
            <div class="p-3 border-t-2 border-[#E4E4E7]">
                <a
                    href="{{ route('modul.index') }}"
                    wire:navigate
                    class="w-full flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl bg-white hover:bg-zinc-50 border-2 border-b-4 border-[#E4E4E7] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] transition-all"
                >
                    <flux:icon.arrow-left-start-on-rectangle class="size-4 text-[#71717A]" />
                    <span>Halaman Belajar</span>
                </a>
            </div>
        </aside>

        {{-- KONTEN UTAMA --}}
        <div class="flex-1 min-w-0 flex flex-col">
            {{-- Header Bar --}}
            <header class="sticky top-0 z-30 h-16 sm:h-18 bg-white/95 backdrop-blur-xs border-b-2 border-[#E4E4E7] px-4 sm:px-8 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden w-10 h-10 rounded-xl border-2 border-[#E4E4E7] flex items-center justify-center text-[#1C1C1E] hover:bg-zinc-50 active:translate-y-0.5"
                    >
                        <flux:icon.bars-3 class="size-5" />
                    </button>
                    <div>
                        <span class="text-xs sm:text-sm font-extrabold text-[#1C1C1E] block">Panel Kelola Konten Signify</span>
                        <span class="text-[11px] font-semibold text-[#71717A] hidden sm:block">Kurikulum BISINDO, Modul, Kuis & Kosakata</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F0FDF4] border border-[#86EFAC] text-[11px] font-extrabold text-[#15803D]">
                        <span class="w-2 h-2 rounded-full bg-[#22B05B] animate-pulse"></span>
                        Admin Mode
                    </span>

                    <div class="flex items-center gap-2.5 pl-2 sm:pl-3 border-l-2 border-[#E4E4E7]">
                        <div class="w-9 h-9 rounded-xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-xs font-extrabold text-[#C98A06] shadow-2xs">
                            AD
                        </div>
                        <div class="hidden sm:block text-left">
                            <span class="text-xs font-extrabold text-[#1C1C1E] block leading-tight">Admin Signify</span>
                            <span class="text-[10px] font-bold text-[#71717A]">Editor & Pengelola</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Content Slot --}}
            <main class="flex-1 max-w-[1320px] w-full mx-auto px-4 sm:px-8 py-6 sm:py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @fluxScripts
</body>
</html>
