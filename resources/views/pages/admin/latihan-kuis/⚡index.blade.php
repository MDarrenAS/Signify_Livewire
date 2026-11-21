<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')] #[Title('Kelola Latihan Kuis — Admin Signify')] class extends Component
{
    use WithFileUploads;

    public array $packages = [];
    public string $search = '';
    public string $levelFilter = 'all';

    public bool $showModal = false;
    public ?int $editIdx = null;

    public string $title = '';
    public string $subtitle = '';
    public string $level = 'Pemula';
    public int $xpReward = 50;
    public int $questionsCount = 10;
    public string $iconEmoji = '🎯';
    public $iconFile = null;
    public string $iconUrl = '';
    public bool $isActive = true;

    public function mount(): void
    {
        $this->packages = session('admin_quiz_packages', [
            [
                'title'           => 'Tantangan Abjad Visual A–Z',
                'subtitle'        => 'Uji kecekatan membaca dan mengenali 26 alfabet BISINDO.',
                'level'           => 'Pemula',
                'questions_count' => 10,
                'xp_reward'       => 50,
                'icon_emoji'      => '🔤',
                'icon_url'        => '',
                'is_active'       => true,
            ],
            [
                'title'           => 'Sapaan & Perkenalan Kilat',
                'subtitle'        => 'Salam, terima kasih, perkenalan diri, dan panggilan sopan.',
                'level'           => 'Pemula',
                'questions_count' => 8,
                'xp_reward'       => 40,
                'icon_emoji'      => '👋',
                'icon_url'        => '',
                'is_active'       => true,
            ],
            [
                'title'           => 'Angka, Jam & Waktu Sehari-hari',
                'subtitle'        => 'Mengenal bilangan isyarat, tanggal, dan rentang waktu.',
                'level'           => 'Menengah',
                'questions_count' => 12,
                'xp_reward'       => 75,
                'icon_emoji'      => '⏱️',
                'icon_url'        => '',
                'is_active'       => false,
            ],
        ]);
    }

    private function persist(): void
    {
        session(['admin_quiz_packages' => $this->packages]);
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editIdx        = null;
        $this->title          = '';
        $this->subtitle       = '';
        $this->level          = 'Pemula';
        $this->xpReward       = 50;
        $this->questionsCount = 10;
        $this->iconEmoji      = '🎯';
        $this->iconFile       = null;
        $this->iconUrl        = '';
        $this->isActive       = true;
        $this->showModal      = true;
    }

    public function openEdit(int $idx): void
    {
        $pkg = $this->packages[$idx] ?? null;
        if (! $pkg) return;

        $this->resetValidation();
        $this->editIdx        = $idx;
        $this->title          = $pkg['title'] ?? '';
        $this->subtitle       = $pkg['subtitle'] ?? '';
        $this->level          = $pkg['level'] ?? 'Pemula';
        $this->xpReward       = (int) ($pkg['xp_reward'] ?? 50);
        $this->questionsCount = (int) ($pkg['questions_count'] ?? 10);
        $this->iconEmoji      = $pkg['icon_emoji'] ?? '🎯';
        $this->iconFile       = null;
        $this->iconUrl        = $pkg['icon_url'] ?? '';
        $this->isActive       = (bool) ($pkg['is_active'] ?? true);
        $this->showModal      = true;
    }

    public function savePackage(): void
    {
        $this->validate([
            'title'          => 'required|string|min:3|max:100',
            'xpReward'       => 'required|integer|min:5|max:500',
            'questionsCount' => 'required|integer|min:1|max:100',
            'iconFile'       => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:10240',
        ], [
            'title.required'    => 'Judul paket kuis wajib diisi.',
            'iconFile.mimes'    => 'Ikon kuis harus berupa file gambar atau GIF (JPG, PNG, GIF, SVG, WEBP).',
        ]);

        $finalIconUrl = $this->iconUrl;
        if ($this->iconFile) {
            try {
                $path = $this->iconFile->store('quizzes/icons', 'public');
                $finalIconUrl = '/storage/' . $path;
            } catch (\Throwable $e) {
                // fallback
            }
        }

        $payload = [
            'title'           => $this->title,
            'subtitle'        => $this->subtitle,
            'level'           => $this->level,
            'questions_count' => $this->questionsCount,
            'xp_reward'       => $this->xpReward,
            'icon_emoji'      => $this->iconEmoji ?: '🎯',
            'icon_url'        => $finalIconUrl,
            'is_active'       => $this->isActive,
        ];

        if ($this->editIdx !== null && isset($this->packages[$this->editIdx])) {
            $this->packages[$this->editIdx] = $payload;

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Paket kuis "' . $this->title . '" berhasil diperbarui!',
            ]);
        } else {
            $this->packages[] = $payload;

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Paket kuis baru berhasil ditambahkan!',
            ]);
        }

        $this->persist();
        $this->showModal = false;
    }

    public function toggleStatus(int $idx): void
    {
        if (isset($this->packages[$idx])) {
            $this->packages[$idx]['is_active'] = ! $this->packages[$idx]['is_active'];
            $this->persist();

            $statusText = $this->packages[$idx]['is_active'] ? 'dipublikasikan' : 'dinonaktifkan';
            $this->dispatch('notify', [
                'type'    => 'info',
                'message' => 'Paket kuis berhasil ' . $statusText . '.',
            ]);
        }
    }

    public function deletePackage(int $idx): void
    {
        $name = $this->packages[$idx]['title'] ?? 'Paket';
        unset($this->packages[$idx]);
        $this->packages = array_values($this->packages);
        $this->persist();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Paket kuis "' . $name . '" berhasil dihapus.',
        ]);
    }
};
?>

<div class="space-y-6">
    <x-admin.page-header
        title="Latihan & Tantangan Kuis"
        subtitle="Kelola paket evaluasi mandiri, tingkat kesulitan, reward skor, dan ikon peraga kuis."
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="openCreate"
                class="inline-flex items-center gap-2 bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all cursor-pointer shadow-xs"
            >
                <flux:icon.plus class="size-4" />
                <span>Tambah Paket Kuis</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter & Search Responsif --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Cari paket kuis berdasarkan judul atau deskripsi..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-medium focus:outline-none focus:border-[#F9B92F] transition-colors"
            />
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#71717A] pointer-events-none">
                <flux:icon.magnifying-glass class="size-4" />
            </div>
        </div>

        <select
            wire:model.live="levelFilter"
            class="px-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-extrabold bg-white text-[#1C1C1E] focus:outline-none focus:border-[#F9B92F] cursor-pointer"
        >
            <option value="all">Semua Tingkat</option>
            <option value="Pemula">Pemula</option>
            <option value="Menengah">Menengah</option>
            <option value="Mahir">Mahir</option>
        </select>
    </div>

    {{-- Tabel Paket Kuis --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E4E4E7] text-[11px] font-extrabold uppercase tracking-wider text-[#71717A] bg-[#FAFAFA]">
                        <th class="py-3.5 px-5">Paket Kuis</th>
                        <th class="py-3.5 px-4">Tingkat</th>
                        <th class="py-3.5 px-4">Jumlah Soal</th>
                        <th class="py-3.5 px-4">Reward</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E4E7] text-sm">
                    @php
                        $filtered = array_filter($packages, function ($pkg) {
                            $matchSearch = empty($this->search)
                                || str_contains(strtolower($pkg['title'] ?? ''), strtolower($this->search))
                                || str_contains(strtolower($pkg['subtitle'] ?? ''), strtolower($this->search));

                            $matchLevel = $this->levelFilter === 'all' || ($pkg['level'] ?? '') === $this->levelFilter;

                            return $matchSearch && $matchLevel;
                        });
                    @endphp

                    @forelse ($filtered as $idx => $pkg)
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    {{-- Ikon / Thumbnail Kuis --}}
                                    <div class="w-11 h-11 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-xl shrink-0 overflow-hidden shadow-2xs">
                                        @if (!empty($pkg['icon_url']))
                                            <img src="{{ $pkg['icon_url'] }}" alt="{{ $pkg['title'] }}" class="w-full h-full object-cover" />
                                        @else
                                            {{ $pkg['icon_emoji'] ?? '🎯' }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-extrabold text-sm text-[#1C1C1E]">{{ $pkg['title'] }}</h3>
                                        @if (!empty($pkg['subtitle']))
                                            <p class="text-xs text-[#71717A] font-medium truncate max-w-md">{{ $pkg['subtitle'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-[#F4F4F5] text-[#52525B]">
                                    {{ $pkg['level'] }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs font-extrabold text-[#1C1C1E]">
                                {{ $pkg['questions_count'] }} Soal
                            </td>
                            <td class="py-4 px-4 text-xs font-extrabold text-[#C98A06]">
                                +{{ $pkg['xp_reward'] }} XP
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border {{ $pkg['is_active'] ? 'bg-[#F0FDF4] border-[#86EFAC] text-[#15803D]' : 'bg-zinc-100 border-[#E4E4E7] text-[#71717A]' }}">
                                    {{ $pkg['is_active'] ? 'Aktif' : 'Draft' }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right space-x-1.5">
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $idx }})"
                                    class="px-3 py-1.5 rounded-xl border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold cursor-pointer transition-colors"
                                >
                                    {{ $pkg['is_active'] ? 'Nonaktifkan' : 'Publikasikan' }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="openEdit({{ $idx }})"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold text-[#52525B] cursor-pointer"
                                    title="Edit Paket"
                                >
                                    ✎
                                </button>
                                <button
                                    type="button"
                                    wire:click="deletePackage({{ $idx }})"
                                    wire:confirm="Yakin ingin menghapus paket kuis '{{ $pkg['title'] }}'?"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-xs font-extrabold text-[#B91C1C] cursor-pointer"
                                    title="Hapus Paket"
                                >
                                    ✕
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-xs font-medium text-[#71717A]">
                                Tidak ada paket kuis yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah / Edit Paket Kuis --}}
    <x-admin.modal :show="$showModal" title="{{ $editIdx !== null ? 'Edit Paket Kuis Mandiri' : 'Tambah Paket Kuis Mandiri' }}" close-action="$set('showModal', false)" max-width="max-w-lg">
        <form wire:submit="savePackage" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Judul Paket Kuis</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="Contoh: Tantangan Ekspresi Wajah & Emosi"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
                    />
                    @error('title') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Ikon Emoji</label>
                    <input
                        type="text"
                        wire:model="iconEmoji"
                        placeholder="🎯"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-extrabold text-center focus:outline-none focus:border-[#F9B92F]"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Deskripsi Singkat</label>
                <input
                    type="text"
                    wire:model="subtitle"
                    placeholder="Contoh: Latihan mengenali ekspresi dan tata bahasa visual BISINDO."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
                />
            </div>

            {{-- Uploader Ikon / Cover Kuis (Gambar atau GIF) --}}
            <x-admin.file-uploader
                model="iconFile"
                url-model="iconUrl"
                :current-url="$iconUrl"
                accept="image/*,.gif,.svg"
                label="Ikon / Cover Visual Kuis (Gambar atau GIF)"
                hint="Unggah file JPG, PNG, GIF, atau SVG sebagai thumbnail paket kuis (Maks. 10MB)"
            >
                @if ($iconFile)
                    <div class="flex items-center gap-3 p-2 bg-[#F0FDF4] border border-[#86EFAC] rounded-xl">
                        <img src="{{ $iconFile->temporaryUrl() }}" class="w-12 h-12 rounded-lg object-cover border border-[#22B05B]" />
                        <div class="text-xs">
                            <span class="font-bold text-[#15803D] block">File Ikon Terpilih</span>
                            <span class="text-[#71717A] text-[10px]">{{ $iconFile->getClientOriginalName() }}</span>
                        </div>
                    </div>
                @elseif (!empty($iconUrl))
                    <div class="flex items-center gap-3 p-2 bg-[#FFF8E6] border border-[#F9B92F] rounded-xl">
                        <img src="{{ $iconUrl }}" class="w-12 h-12 rounded-lg object-cover border border-[#C98A06]" />
                        <span class="text-xs font-bold text-[#C98A06]">Ikon Aktif</span>
                    </div>
                @endif
            </x-admin.file-uploader>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Tingkat Kesulitan</label>
                    <select
                        wire:model="level"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-bold bg-white text-[#1C1C1E]"
                    >
                        <option value="Pemula">Pemula</option>
                        <option value="Menengah">Menengah</option>
                        <option value="Mahir">Mahir</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Target Soal</label>
                    <input
                        type="number"
                        wire:model="questionsCount"
                        class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold focus:outline-none focus:border-[#F9B92F]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Reward XP</label>
                    <input
                        type="number"
                        wire:model="xpReward"
                        class="w-full px-3.5 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold focus:outline-none focus:border-[#F9B92F]"
                    />
                </div>
            </div>

            <div class="pt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="isActive" class="rounded text-[#22B05B] focus:ring-[#22B05B] size-4" />
                    <span class="text-xs font-extrabold text-[#1C1C1E]">Publikasikan Paket Kuis Ini (Bisa Dikerjakan Siswa)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E4E4E7]">
                <button
                    type="button"
                    wire:click="$set('showModal', false)"
                    class="px-4 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold text-[#52525B] hover:bg-zinc-100 cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
                >
                    Simpan Paket Kuis
                </button>
            </div>
        </form>
    </x-admin.modal>
</div>
