@props([
    'unit',
    'uIdx',
    'search' => '',
    'canDelete' => true,
])

@php
    $modules = $unit['modules'] ?? [];
    if (!empty($search)) {
        $modules = array_filter($modules, function ($m) use ($search) {
            return str_contains(strtolower($m['title'] ?? ''), strtolower($search))
                || str_contains(strtolower($m['description'] ?? ''), strtolower($search))
                || str_contains(strtolower($m['subtitle'] ?? ''), strtolower($search));
        });
    }
@endphp

<div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden shadow-2xs">
    {{-- Header Unit --}}
    <div class="px-5 py-4 bg-[#FAFAFA] border-b-2 border-[#E4E4E7] flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full bg-[#FFF8E6] border border-[#F9B92F] text-[10px] font-extrabold text-[#C98A06] uppercase tracking-wide">
                    {{ $unit['badge'] ?? 'UNIT ' . ($uIdx + 1) }}
                </span>
                <span class="text-xs font-bold text-[#71717A]">
                    {{ count($unit['modules'] ?? []) }} Modul Terdaftar
                </span>
            </div>
            <h2 class="text-lg font-extrabold text-[#1C1C1E]">{{ $unit['title'] }}</h2>
            @if (!empty($unit['description']))
                <p class="text-xs text-[#71717A] font-medium leading-relaxed">{{ $unit['description'] }}</p>
            @endif
        </div>

        {{-- Action Buttons Unit --}}
        <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
            <button
                type="button"
                wire:click="openEditUnit({{ $uIdx }})"
                class="px-3 py-1.5 rounded-xl bg-white border border-[#E4E4E7] hover:bg-zinc-50 text-xs font-extrabold text-[#52525B] cursor-pointer"
            >
                ✎ Edit Unit
            </button>
            <button
                type="button"
                wire:click="openCreateModul({{ $uIdx }})"
                class="px-3.5 py-1.5 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] text-xs font-extrabold text-[#C98A06] hover:bg-[#F9B92F] hover:text-[#1C1C1E] transition-colors cursor-pointer"
            >
                + Tambah Modul
            </button>
            @if ($canDelete)
                <button
                    type="button"
                    wire:click="deleteUnit({{ $uIdx }})"
                    wire:confirm="Yakin ingin menghapus unit ini beserta seluruh modul di dalamnya?"
                    class="px-2.5 py-1.5 rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-xs font-extrabold text-[#B91C1C] cursor-pointer"
                    title="Hapus Unit"
                >
                    ✕
                </button>
            @endif
        </div>
    </div>

    {{-- Tabel Modul di dalam Unit --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-[#E4E4E7] text-[11px] font-extrabold uppercase tracking-wider text-[#71717A] bg-white">
                    <th class="py-3 px-5">Modul Belajar</th>
                    <th class="py-3 px-4">Bagian / Isi</th>
                    <th class="py-3 px-4">Reward XP</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E4E7] text-sm">
                @forelse ($modules as $mIdx => $modul)
                    <tr class="hover:bg-zinc-50/70 transition-colors">
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-3">
                                {{-- Icon / Gambar Modul --}}
                                <div class="w-10 h-10 rounded-xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-lg shrink-0 overflow-hidden">
                                    @if (!empty($modul['icon_url']))
                                        <img src="{{ $modul['icon_url'] }}" alt="{{ $modul['title'] }}" class="w-full h-full object-cover" />
                                    @else
                                        {{ $modul['icon'] ?? '🤟' }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <a
                                        href="{{ route('admin.modul.show', ['modul' => $modul['slug'] ?? 'm']) }}"
                                        wire:navigate
                                        class="font-extrabold text-sm text-[#1C1C1E] hover:text-[#C98A06] transition-colors block truncate"
                                    >
                                        {{ $modul['title'] }}
                                    </a>
                                    <p class="text-xs text-[#71717A] truncate font-medium">
                                        {{ $modul['subtitle'] ?? $modul['description'] ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-bold text-[#52525B]">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#F4F4F5]">
                                {{ count($modul['parts'] ?? []) }} Bagian
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs font-extrabold text-[#C98A06]">
                            +{{ $modul['xp_reward'] ?? 50 }} XP
                        </td>
                        <td class="py-3.5 px-4">
                            @php $isActive = ($modul['status'] ?? 'active') !== 'locked'; @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border {{ $isActive ? 'bg-[#F0FDF4] border-[#86EFAC] text-[#15803D]' : 'bg-zinc-100 border-[#E4E4E7] text-[#71717A]' }}">
                                {{ $isActive ? 'Aktif' : 'Terkunci' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right space-x-1.5">
                            <a
                                href="{{ route('admin.modul.show', ['modul' => $modul['slug'] ?? 'm']) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] text-xs font-extrabold text-[#C98A06] hover:bg-[#F9B92F] hover:text-[#1C1C1E] transition-colors cursor-pointer"
                                title="Buka Editor Isi Materi & Kuis"
                            >
                                Kelola Isi →
                            </a>
                            <button
                                type="button"
                                wire:click="openEditModul({{ $uIdx }}, {{ $mIdx }})"
                                class="px-2.5 py-1.5 rounded-xl border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold text-[#52525B] cursor-pointer"
                                title="Edit Informasi Modul"
                            >
                                ✎
                            </button>
                            <button
                                type="button"
                                wire:click="deleteModul({{ $uIdx }}, {{ $mIdx }})"
                                wire:confirm="Yakin ingin menghapus modul ini?"
                                class="px-2.5 py-1.5 rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-xs font-extrabold text-[#B91C1C] cursor-pointer"
                                title="Hapus Modul"
                            >
                                ✕
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-xs font-medium text-[#71717A]">
                            Belum ada modul di unit ini. Klik tombol <strong class="text-[#C98A06]">+ Tambah Modul</strong> di atas untuk menambahkan materi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
