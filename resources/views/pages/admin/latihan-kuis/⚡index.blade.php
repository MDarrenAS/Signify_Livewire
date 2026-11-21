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

    // State Modal Paket Kuis
    public bool $showModal = false;
    public ?int $editIdx = null;

    public string $title = '';
    public string $subtitle = '';
    public string $level = 'Pemula';
    public int $xpReward = 50;
    public string $iconEmoji = '🎯';
    public $iconFile = null;
    public string $iconUrl = '';
    public bool $isActive = true;

    // State Modal Kelola Soal Kuis
    public bool $showQuestionsModal = false;
    public ?int $managePackageIdx = null;

    // State Tambah Soal Baru
    public bool $showAddQuestionModal = false;
    public string $questionText = 'Perhatikan gerakan isyarat ini. Manakah arti atau huruf yang benar?';
    public string $questionMediaUrl = '';
    public $questionMediaFile = null;
    public array $questionOptions = ['', '', '', ''];
    public int $questionCorrectIndex = 0;

    public function mount(): void
    {
        $this->packages = session('admin_quiz_packages', [
            [
                'title'           => 'Tantangan Abjad Visual A–Z',
                'subtitle'        => 'Uji kecekatan membaca dan mengenali 26 alfabet BISINDO.',
                'level'           => 'Pemula',
                'questions_count' => 3,
                'xp_reward'       => 50,
                'icon_emoji'      => '🔤',
                'icon_url'        => '',
                'is_active'       => true,
                'questions'       => [
                    [
                        'question'      => 'Isyarat dengan menyatukan ujung ibu jari dan telunjuk membentuk segitiga melambangkan huruf?',
                        'video_url'     => '/storage/signs/abjad/a.mp4',
                        'options'       => ['Huruf A', 'Huruf B', 'Huruf C', 'Huruf D'],
                        'correct_index' => 0,
                    ],
                    [
                        'question'      => 'Mengacungkan jari kelingking tegak ke atas melambangkan huruf?',
                        'video_url'     => '/storage/signs/abjad/i.mp4',
                        'options'       => ['Huruf U', 'Huruf I', 'Huruf E', 'Huruf O'],
                        'correct_index' => 1,
                    ],
                    [
                        'question'      => 'Dua jari telunjuk dan jari tengah tegak rapat ke atas melambangkan huruf?',
                        'video_url'     => '',
                        'options'       => ['Huruf V', 'Huruf U', 'Huruf W', 'Huruf R'],
                        'correct_index' => 1,
                    ],
                ],
            ],
            [
                'title'           => 'Sapaan & Perkenalan Kilat',
                'subtitle'        => 'Salam, terima kasih, perkenalan diri, dan panggilan sopan.',
                'level'           => 'Pemula',
                'questions_count' => 2,
                'xp_reward'       => 40,
                'icon_emoji'      => '👋',
                'icon_url'        => '',
                'is_active'       => true,
                'questions'       => [
                    [
                        'question'      => 'Menyentuhkan ujung jari ke dagu lalu mengarahkannya ke depan berarti?',
                        'video_url'     => '/storage/signs/sapaan/terima-kasih.mp4',
                        'options'       => ['Selamat Pagi', 'Terima Kasih', 'Sama-sama', 'Minta Maaf'],
                        'correct_index' => 1,
                    ],
                    [
                        'question'      => 'Melambaikan tangan kanan terbuka ke arah depan berarti?',
                        'video_url'     => '/storage/signs/sapaan/halo.mp4',
                        'options'       => ['Halo / Hai', 'Sampai Jumpa', 'Kabar Baik', 'Siapa Namamu'],
                        'correct_index' => 0,
                    ],
                ],
            ],
            [
                'title'           => 'Angka, Jam & Waktu Sehari-hari',
                'subtitle'        => 'Mengenal bilangan isyarat, tanggal, dan rentang waktu.',
                'level'           => 'Menengah',
                'questions_count' => 0,
                'xp_reward'       => 75,
                'icon_emoji'      => '⏱️',
                'icon_url'        => '',
                'is_active'       => false,
                'questions'       => [],
            ],
        ]);
    }

    private function persist(): void
    {
        session(['admin_quiz_packages' => $this->packages]);
    }

    public function getActivePackageProperty(): ?array
    {
        if ($this->managePackageIdx === null || ! isset($this->packages[$this->managePackageIdx])) {
            return null;
        }

        return $this->packages[$this->managePackageIdx];
    }

    // ==================== KELOLA PAKET KUIS ====================
    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editIdx   = null;
        $this->title     = '';
        $this->subtitle  = '';
        $this->level     = 'Pemula';
        $this->xpReward  = 50;
        $this->iconEmoji = '🎯';
        $this->iconFile  = null;
        $this->iconUrl   = '';
        $this->isActive  = true;
        $this->showModal = true;
    }

    public function openEdit(int $idx): void
    {
        $pkg = $this->packages[$idx] ?? null;
        if (! $pkg) return;

        $this->resetValidation();
        $this->editIdx   = $idx;
        $this->title     = $pkg['title'] ?? '';
        $this->subtitle  = $pkg['subtitle'] ?? '';
        $this->level     = $pkg['level'] ?? 'Pemula';
        $this->xpReward  = (int) ($pkg['xp_reward'] ?? 50);
        $this->iconEmoji = $pkg['icon_emoji'] ?? '🎯';
        $this->iconFile  = null;
        $this->iconUrl   = $pkg['icon_url'] ?? '';
        $this->isActive  = (bool) ($pkg['is_active'] ?? true);
        $this->showModal = true;
    }

    public function savePackage(): void
    {
        $this->validate([
            'title'    => 'required|string|min:3|max:100',
            'xpReward' => 'required|integer|min:5|max:500',
            'iconFile' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:10240',
        ], [
            'title.required' => 'Judul paket kuis wajib diisi.',
            'iconFile.mimes' => 'Ikon kuis harus berupa file gambar atau GIF (JPG, PNG, GIF, SVG, WEBP).',
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

        if ($this->editIdx !== null && isset($this->packages[$this->editIdx])) {
            $this->packages[$this->editIdx]['title']      = $this->title;
            $this->packages[$this->editIdx]['subtitle']   = $this->subtitle;
            $this->packages[$this->editIdx]['level']      = $this->level;
            $this->packages[$this->editIdx]['xp_reward']  = $this->xpReward;
            $this->packages[$this->editIdx]['icon_emoji'] = $this->iconEmoji ?: '🎯';
            $this->packages[$this->editIdx]['icon_url']   = $finalIconUrl;
            $this->packages[$this->editIdx]['is_active']  = $this->isActive;

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Paket kuis "' . $this->title . '" berhasil diperbarui!',
            ]);
        } else {
            $this->packages[] = [
                'title'           => $this->title,
                'subtitle'        => $this->subtitle,
                'level'           => $this->level,
                'questions_count' => 0,
                'xp_reward'       => $this->xpReward,
                'icon_emoji'      => $this->iconEmoji ?: '🎯',
                'icon_url'        => $finalIconUrl,
                'is_active'       => $this->isActive,
                'questions'       => [],
            ];

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Paket kuis baru berhasil ditambahkan! Silakan klik "Kelola Soal" untuk membuat soalnya.',
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

    // ==================== KELOLA BUTIR SOAL KUIS ====================
    public function openManageQuestions(int $pkgIdx): void
    {
        $this->managePackageIdx = $pkgIdx;
        $this->showQuestionsModal = true;
    }

    public function openAddQuestion(): void
    {
        $this->resetValidation();
        $this->questionText         = 'Perhatikan gerakan isyarat ini. Manakah arti atau huruf yang benar?';
        $this->questionMediaUrl     = '';
        $this->questionMediaFile    = null;
        $this->questionOptions      = ['', '', '', ''];
        $this->questionCorrectIndex = 0;
        $this->showAddQuestionModal = true;
    }

    public function saveQuestion(): void
    {
        $this->validate([
            'questionText'      => 'required|string|max:250',
            'questionOptions.0' => 'required|string|max:100',
            'questionOptions.1' => 'required|string|max:100',
            'questionOptions.2' => 'required|string|max:100',
            'questionOptions.3' => 'required|string|max:100',
            'questionMediaFile' => 'nullable|file|mimes:mp4,webm,gif,png,jpg,jpeg,webp|max:30720',
        ], [
            'questionText.required'      => 'Teks pertanyaan wajib diisi.',
            'questionOptions.*.required' => 'Seluruh 4 pilihan opsi jawaban wajib diisi.',
            'questionMediaFile.mimes'    => 'Media soal harus berupa file video (MP4, WEBM) atau gambar (GIF, PNG, JPG).',
        ]);

        $finalUrl = $this->questionMediaUrl;
        if ($this->questionMediaFile) {
            try {
                $path = $this->questionMediaFile->store('quizzes/media', 'public');
                $finalUrl = '/storage/' . $path;
            } catch (\Throwable $e) {
                // fallback
            }
        }

        if ($this->managePackageIdx !== null && isset($this->packages[$this->managePackageIdx])) {
            $this->packages[$this->managePackageIdx]['questions'][] = [
                'question'      => $this->questionText,
                'video_url'     => $finalUrl,
                'options'       => array_values($this->questionOptions),
                'correct_index' => (int) $this->questionCorrectIndex,
            ];

            $this->packages[$this->managePackageIdx]['questions_count'] = count($this->packages[$this->managePackageIdx]['questions']);
            $this->persist();

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Soal kuis baru berhasil ditambahkan!',
            ]);
        }

        $this->showAddQuestionModal = false;
    }

    public function removeQuestion(int $qIdx): void
    {
        if ($this->managePackageIdx !== null && isset($this->packages[$this->managePackageIdx])) {
            unset($this->packages[$this->managePackageIdx]['questions'][$qIdx]);
            $this->packages[$this->managePackageIdx]['questions'] = array_values($this->packages[$this->managePackageIdx]['questions']);
            $this->packages[$this->managePackageIdx]['questions_count'] = count($this->packages[$this->managePackageIdx]['questions']);
            $this->persist();

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Soal kuis #' . ($qIdx + 1) . ' berhasil dihapus.',
            ]);
        }
    }
};
?>

<div class="space-y-6">
    <x-admin.page-header
        title="Latihan & Tantangan Kuis"
        subtitle="Kelola paket evaluasi mandiri, tingkat kesulitan, reward skor, dan butir-butir soal peraga kuis."
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
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#F4F4F5]">
                                    {{ count($pkg['questions'] ?? []) }} Soal
                                </span>
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
                                    wire:click="openManageQuestions({{ $idx }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#FFF8E6] border border-[#F9B92F] text-xs font-extrabold text-[#C98A06] hover:bg-[#F9B92F] hover:text-[#1C1C1E] transition-colors cursor-pointer"
                                    title="Kelola & Tambah Soal Kuis"
                                >
                                    Kelola Soal ({{ count($pkg['questions'] ?? []) }}) →
                                </button>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $idx }})"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold cursor-pointer transition-colors"
                                    title="{{ $pkg['is_active'] ? 'Nonaktifkan' : 'Publikasikan' }}"
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
    <x-admin.latihan-kuis.package-modal
        :show="$showModal"
        :edit-index="$editIdx"
        :icon-file="$iconFile"
        :icon-url="$iconUrl"
    />

    {{-- Modal Kelola Daftar Butir Soal --}}
    <x-admin.latihan-kuis.questions-modal
        :show="$showQuestionsModal"
        :active-package="$this->activePackage"
    />

    {{-- Modal Form Input Soal Baru --}}
    <x-admin.latihan-kuis.add-question-modal
        :show="$showAddQuestionModal"
        :media-file="$questionMediaFile"
        :media-url="$questionMediaUrl"
        :correct-index="$questionCorrectIndex"
    />
</div>
