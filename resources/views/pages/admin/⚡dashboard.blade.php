<?php

use App\Services\AdminDashboardService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Dashboard Admin — Signify')] class extends Component
{
    #[Computed]
    public function stats(): array
    {
        return app(AdminDashboardService::class)->getStats();
    }
};
?>

<div class="space-y-6">
    <x-admin.page-header
        title="Dashboard Admin"
        subtitle="Pusat kelola materi kurikulum, modul isyarat BISINDO, latihan kuis, dan kosakata kamus."
    >
        <x-slot:actions>
            <a
                href="{{ route('admin.modul.index') }}"
                wire:navigate
                class="inline-flex items-center gap-2 bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all shadow-xs"
            >
                <flux:icon.plus class="size-4" />
                <span>Kelola Modul & Unit</span>
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Grid 4 Kartu Statistik Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.modul.index') }}" wire:navigate class="bg-white border-2 border-b-4 border-[#E4E4E7] hover:border-[#F9B92F] rounded-2xl p-5 transition-all shadow-2xs group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-[#71717A]">Modul Belajar</span>
                <span class="w-10 h-10 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] flex items-center justify-center text-[#C98A06] group-hover:scale-105 transition-transform">
                    <flux:icon.book-open class="size-5" />
                </span>
            </div>
            <p class="text-3xl font-extrabold text-[#1C1C1E] mt-3 tracking-tight">{{ $this->stats['modules'] }} Modul</p>
            <p class="text-xs font-semibold text-[#71717A] mt-1">Dalam {{ $this->stats['units'] }} unit aktif</p>
        </a>

        <a href="{{ route('admin.kamus.index') }}" wire:navigate class="bg-white border-2 border-b-4 border-[#E4E4E7] hover:border-[#F9B92F] rounded-2xl p-5 transition-all shadow-2xs group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-[#71717A]">Kamus BISINDO</span>
                <span class="w-10 h-10 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] flex items-center justify-center text-[#C98A06] group-hover:scale-105 transition-transform">
                    <flux:icon.chat-bubble-left-right class="size-5" />
                </span>
            </div>
            <p class="text-3xl font-extrabold text-[#1C1C1E] mt-3 tracking-tight">{{ $this->stats['signs'] }} Entri</p>
            <p class="text-xs font-semibold text-[#71717A] mt-1">Video & gambar peragaan</p>
        </a>

        <a href="{{ route('admin.latihan.index') }}" wire:navigate class="bg-white border-2 border-b-4 border-[#E4E4E7] hover:border-[#F9B92F] rounded-2xl p-5 transition-all shadow-2xs group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-[#71717A]">Latihan Kuis</span>
                <span class="w-10 h-10 rounded-xl bg-[#F0FDF4] border border-[#86EFAC] flex items-center justify-center text-[#15803D] group-hover:scale-105 transition-transform">
                    <flux:icon.clipboard-document-check class="size-5" />
                </span>
            </div>
            <p class="text-3xl font-extrabold text-[#15803D] mt-3 tracking-tight">{{ $this->stats['quizzes'] }} Paket</p>
            <p class="text-xs font-semibold text-[#71717A] mt-1">Evaluasi mandiri siswa</p>
        </a>

        <a href="{{ route('admin.pengguna.index') }}" wire:navigate class="bg-white border-2 border-b-4 border-[#E4E4E7] hover:border-[#F9B92F] rounded-2xl p-5 transition-all shadow-2xs group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-extrabold uppercase tracking-wider text-[#71717A]">Total Pengguna</span>
                <span class="w-10 h-10 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] flex items-center justify-center text-[#C98A06] group-hover:scale-105 transition-transform">
                    <flux:icon.users class="size-5" />
                </span>
            </div>
            <p class="text-3xl font-extrabold text-[#1C1C1E] mt-3 tracking-tight">{{ $this->stats['users'] }}</p>
            <p class="text-xs font-semibold text-[#15803D] mt-1">+{{ $this->stats['new_users_this_week'] }} pengguna minggu ini</p>
        </a>
    </div>

    {{-- Akses Cepat & Panduan Singkat --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-5 space-y-3">
            <div class="w-10 h-10 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] flex items-center justify-center text-lg">
                📚
            </div>
            <h3 class="text-sm font-extrabold text-[#1C1C1E]">1. Tambah Unit & Modul</h3>
            <p class="text-xs text-[#71717A] leading-relaxed">
                Buat unit kurikulum baru, lalu masukkan modul bertingkat dengan cover atau ikon visual.
            </p>
            <a href="{{ route('admin.modul.index') }}" wire:navigate class="text-xs font-extrabold text-[#C98A06] hover:underline inline-flex items-center gap-1">
                Buka Modul →
            </a>
        </div>

        <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-5 space-y-3">
            <div class="w-10 h-10 rounded-xl bg-[#F0FDF4] border border-[#86EFAC] flex items-center justify-center text-lg">
                🎬
            </div>
            <h3 class="text-sm font-extrabold text-[#1C1C1E]">2. Upload Media Peragaan</h3>
            <p class="text-xs text-[#71717A] leading-relaxed">
                Unggah video MP4, WEBM atau GIF peragaan gerakan isyarat agar siswa dapat menirukan dengan mudah.
            </p>
            <a href="{{ route('admin.kamus.index') }}" wire:navigate class="text-xs font-extrabold text-[#15803D] hover:underline inline-flex items-center gap-1">
                Buka Kamus →
            </a>
        </div>

        <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-5 space-y-3">
            <div class="w-10 h-10 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] flex items-center justify-center text-lg">
                🎯
            </div>
            <h3 class="text-sm font-extrabold text-[#1C1C1E]">3. Susun Kuis Evaluasi</h3>
            <p class="text-xs text-[#71717A] leading-relaxed">
                Buat paket kuis dan soal pilihan ganda interaktif lengkap dengan media dan kunci jawaban otomatis.
            </p>
            <a href="{{ route('admin.latihan.index') }}" wire:navigate class="text-xs font-extrabold text-[#C98A06] hover:underline inline-flex items-center gap-1">
                Buka Kuis →
            </a>
        </div>
    </div>
</div>
