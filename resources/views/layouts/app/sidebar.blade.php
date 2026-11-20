<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light" style="color-scheme: light;">
<head>
    @include('partials.head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-[#FAFAFA] font-['Plus_Jakarta_Sans'] text-[#1F1F1F] antialiased">
    {{-- Mobile Header --}}
    <flux:header class="lg:hidden bg-[#FAFAFA] border-b border-[#EDEDED] px-4 py-2.5">
        <flux:sidebar.toggle class="lg:hidden !text-[#1F1F1F]" icon="bars-2" inset="left" />

        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           class="font-['Caveat_Brush'] text-4xl text-[#F9B92F] leading-none ms-2 select-none"
           wire:navigate>
            Signify
        </a>

        <flux:spacer />
    </flux:header>

    {{-- Sidebar Utama --}}
    <flux:sidebar
        sticky
        stashable
        class="w-64 bg-[#FAFAFA] border-e border-[#EDEDED] px-4 py-6 lg:my-3.5 lg:ms-4 lg:h-[calc(100vh-28px)] lg:rounded-[18px] lg:border lg:shadow-[0_4px_18px_rgba(0,0,0,0.07),0_1px_3px_rgba(0,0,0,0.04)]"
    >
        <flux:sidebar.toggle class="lg:hidden self-end !text-[#1F1F1F]" icon="x-mark" />

        {{-- Logo --}}
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           class="font-['Caveat_Brush'] text-[44px] text-[#F9B92F] text-center leading-none mb-7 block select-none"
           wire:navigate>
            Signify
        </a>

        {{-- Menu Navigasi --}}
        <flux:navlist variant="outline" class="space-y-1">
            <flux:navlist.item
                icon="squares-2x2"
                :href="Route::has('dashboard') ? route('dashboard') : '#'"
                :current="request()->routeIs('dashboard')"
                class="!rounded-xl !py-2.5 !text-[15px] !font-semibold !text-[#8A8A8A] hover:!bg-[#FEF8EA] hover:!text-[#1F1F1F] data-[current]:!bg-[#FEF8EA] data-[current]:!text-black"
                wire:navigate
            >
                Dashboard
            </flux:navlist.item>

            <flux:navlist.item
                icon="book-open"
                :href="Route::has('modul.index') ? route('modul.index') : '#'"
                :current="request()->routeIs('modul.*')"
                class="!rounded-xl !py-2.5 !text-[15px] !font-semibold !text-[#8A8A8A] hover:!bg-[#FEF8EA] hover:!text-[#1F1F1F] data-[current]:!bg-[#FEF8EA] data-[current]:!text-black"
                wire:navigate
            >
                Modul Belajar
            </flux:navlist.item>

            <flux:navlist.item
                icon="clipboard-document-check"
                :href="Route::has('latihan.index') ? route('latihan.index') : '#'"
                :current="request()->routeIs('latihan.*')"
                class="!rounded-xl !py-2.5 !text-[15px] !font-semibold !text-[#8A8A8A] hover:!bg-[#FEF8EA] hover:!text-[#1F1F1F] data-[current]:!bg-[#FEF8EA] data-[current]:!text-black"
                wire:navigate
            >
                Latihan dan Kuis
            </flux:navlist.item>

            <flux:navlist.item
                icon="language"
                :href="Route::has('kamus.index') ? route('kamus.index') : '#'"
                :current="request()->routeIs('kamus.*')"
                class="!rounded-xl !py-2.5 !text-[15px] !font-semibold !text-[#8A8A8A] hover:!bg-[#FEF8EA] hover:!text-[#1F1F1F] data-[current]:!bg-[#FEF8EA] data-[current]:!text-black"
                wire:navigate
            >
                Kamus BISINDO
            </flux:navlist.item>

            <flux:navlist.item
                icon="user"
                :href="Route::has('profile.edit') ? route('profile.edit') : '#'"
                :current="request()->routeIs('profile.*')"
                class="!rounded-xl !py-2.5 !text-[15px] !font-semibold !text-[#8A8A8A] hover:!bg-[#FEF8EA] hover:!text-[#1F1F1F] data-[current]:!bg-[#FEF8EA] data-[current]:!text-black"
                wire:navigate
            >
                Profil
            </flux:navlist.item>
        </flux:navlist>

        <flux:spacer />

        {{-- Info User & Tombol Keluar --}}
        <div class="pt-6 space-y-2.5">
            <div class="bg-[#FEF5E0] rounded-xl px-3.5 py-3">
                <strong class="block text-sm font-bold text-[#1F1F1F] truncate">
                    {{ auth()->user()->name ?? 'Alifah' }}
                </strong>
                <span class="block text-[11px] text-[#828282] truncate mt-0.5">
                    {{ auth()->user()->email ?? 'alifah@example.com' }}
                </span>
            </div>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full py-2.5 px-3 bg-white border border-[#D4D4D8] rounded-[10px] text-[#828282] text-[13px] font-semibold cursor-pointer transition-colors hover:bg-[#FEF5E0] hover:text-[#C79426] hover:border-[#F9B92F] flex items-center justify-center gap-1.5"
                    >
                        <flux:icon.arrow-right-start-on-rectangle variant="micro" class="size-4" />
                        <span>Keluar</span>
                    </button>
                </form>
            @endauth
        </div>
    </flux:sidebar>

    {{-- Area Konten Utama --}}
    <flux:main class="!p-3 sm:!p-4 lg:!py-3.5 lg:!px-4">
        <div class="min-h-[calc(100vh-28px)] bg-[#FAFAFA] rounded-[18px] border border-[#EDEDED] shadow-[0_4px_18px_rgba(0,0,0,0.07),0_1px_3px_rgba(0,0,0,0.04)] p-4 sm:p-6 lg:p-7 max-w-[1360px] mx-auto">
            {{ $slot }}
        </div>
    </flux:main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>
</html>