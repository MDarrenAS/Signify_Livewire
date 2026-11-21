<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Pengguna & Progres — Admin Signify')] class extends Component
{
    public string $search = '';
    public string $statusFilter = 'all';

    public array $users = [
        [
            'name'             => 'Nadia Putri',
            'email'            => 'nadia@example.com',
            'level'            => 'Tingkat 3 • Mahir',
            'total_xp'         => 340,
            'completed_parts'  => 14,
            'status'           => 'Aktif',
            'last_active'      => '10 menit yang lalu',
        ],
        [
            'name'             => 'Rizky Pratama',
            'email'            => 'rizky@example.com',
            'level'            => 'Tingkat 2 • Menengah',
            'total_xp'         => 210,
            'completed_parts'  => 8,
            'status'           => 'Aktif',
            'last_active'      => '1 jam yang lalu',
        ],
        [
            'name'             => 'Aulia Zahra',
            'email'            => 'aulia@example.com',
            'level'            => 'Tingkat 1 • Pemula',
            'total_xp'         => 90,
            'completed_parts'  => 4,
            'status'           => 'Aktif',
            'last_active'      => 'Kemarin',
        ],
        [
            'name'             => 'Budi Santoso',
            'email'            => 'budi@example.com',
            'level'            => 'Tingkat 1 • Pemula',
            'total_xp'         => 30,
            'completed_parts'  => 1,
            'status'           => 'Nonaktif',
            'last_active'      => '5 hari yang lalu',
        ],
    ];
};
?>

<div class="space-y-6">
    <x-admin.page-header
        title="Pengguna & Progres Belajar"
        subtitle="Pantau keaktifan peserta, perolehan XP, dan penyelesaian modul isyarat BISINDO."
    />

    {{-- Filter & Search --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Cari pengguna berdasarkan nama atau email..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-medium focus:outline-none focus:border-[#F9B92F] transition-colors"
            />
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#71717A] pointer-events-none">
                <flux:icon.magnifying-glass class="size-4" />
            </div>
        </div>

        <select
            wire:model.live="statusFilter"
            class="px-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-extrabold bg-white text-[#1C1C1E] focus:outline-none focus:border-[#F9B92F] cursor-pointer"
        >
            <option value="all">Semua Status</option>
            <option value="Aktif">Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
        </select>
    </div>

    {{-- Tabel Pengguna --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E4E4E7] text-[11px] font-extrabold uppercase tracking-wider text-[#71717A] bg-[#FAFAFA]">
                        <th class="py-3.5 px-5">Nama Pengguna</th>
                        <th class="py-3.5 px-4">Tingkat Kemahiran</th>
                        <th class="py-3.5 px-4">Total XP</th>
                        <th class="py-3.5 px-4">Bagian Selesai</th>
                        <th class="py-3.5 px-4">Aktivitas Terakhir</th>
                        <th class="py-3.5 px-5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E4E7] text-sm">
                    @php
                        $filtered = array_filter($users, function ($u) {
                            $matchSearch = empty($this->search)
                                || str_contains(strtolower($u['name'] ?? ''), strtolower($this->search))
                                || str_contains(strtolower($u['email'] ?? ''), strtolower($this->search));

                            $matchStatus = $this->statusFilter === 'all' || ($u['status'] ?? '') === $this->statusFilter;

                            return $matchSearch && $matchStatus;
                        });
                    @endphp

                    @forelse ($filtered as $u)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center font-extrabold text-xs text-[#C98A06] shrink-0">
                                        {{ strtoupper(substr($u['name'], 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-sm text-[#1C1C1E]">{{ $u['name'] }}</p>
                                        <p class="text-xs text-[#71717A] font-medium">{{ $u['email'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-xs font-bold text-[#52525B]">
                                {{ $u['level'] }}
                            </td>
                            <td class="py-4 px-4 text-xs font-extrabold text-[#C98A06]">
                                ⚡ {{ $u['total_xp'] }} XP
                            </td>
                            <td class="py-4 px-4 text-xs font-bold text-[#1C1C1E]">
                                {{ $u['completed_parts'] }} Bagian
                            </td>
                            <td class="py-4 px-4 text-xs text-[#71717A] font-medium">
                                {{ $u['last_active'] }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border {{ $u['status'] === 'Aktif' ? 'bg-[#F0FDF4] border-[#86EFAC] text-[#15803D]' : 'bg-zinc-100 border-[#E4E4E7] text-[#71717A]' }}">
                                    {{ $u['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-xs font-medium text-[#71717A]">
                                Tidak ada data pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
