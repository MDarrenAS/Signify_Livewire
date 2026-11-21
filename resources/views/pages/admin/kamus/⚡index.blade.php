<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')] #[Title('Kamus BISINDO — Admin Signify')] class extends Component
{
    use WithFileUploads;

    public array $entries = [];
    public string $search = '';
    public string $categoryFilter = 'all';

    public bool $showModal = false;
    public ?int $editIdx = null;

    public string $word = '';
    public string $category = 'Sapaan & Sehari-hari';
    public string $handType = '2 Tangan';
    public string $description = '';
    public string $tip = '';
    public $mediaFile = null;
    public string $mediaUrl = '';

    public function mount(): void
    {
        $this->entries = session('admin_dictionary_entries', [
            [
                'word'        => 'Halo / Hai',
                'category'    => 'Sapaan & Sehari-hari',
                'hand_type'   => '1 Tangan',
                'description' => 'Lambaikan tangan kanan terbuka ke arah depan dengan rileks dan ramah.',
                'tip'         => 'Sertai dengan kontak mata dan senyuman bersahabat.',
                'media_url'   => '/storage/signs/sapaan/halo.mp4',
            ],
            [
                'word'        => 'Terima Kasih',
                'category'    => 'Sapaan & Sehari-hari',
                'hand_type'   => '1 Tangan',
                'description' => 'Sentuhkan ujung jari tangan kanan ke dagu atau bibir bawah, lalu gerakkan ke arah lawan bicara.',
                'tip'         => 'Gerakan mengalir lembut ke depan menghormati lawan bicara.',
                'media_url'   => '/storage/signs/sapaan/terima-kasih.mp4',
            ],
            [
                'word'        => 'Sama-sama',
                'category'    => 'Sapaan & Sehari-hari',
                'hand_type'   => '2 Tangan',
                'description' => 'Kedua tangan terbuka menghadap ke atas, digerakkan sedikit ke depan secara bersamaan.',
                'tip'         => 'Gerakan ini menunjukkan penerimaan dengan tulus.',
                'media_url'   => '',
            ],
            [
                'word'        => 'Huruf A',
                'category'    => 'Abjad & Ejaan',
                'hand_type'   => '2 Tangan',
                'description' => 'Pertemukan ujung ibu jari dan telunjuk kedua tangan membentuk segitiga puncak huruf A.',
                'tip'         => 'Pastikan bentuk segitiganya rapat dan simetris.',
                'media_url'   => '/storage/signs/abjad/a.mp4',
            ],
            [
                'word'        => 'Angka 5',
                'category'    => 'Angka & Waktu',
                'hand_type'   => '1 Tangan',
                'description' => 'Buka seluruh lima jari tangan kanan ke depan dengan telapak menghadap lawan bicara.',
                'tip'         => 'Rentangkan kelima jari dengan jelas.',
                'media_url'   => '',
            ],
        ]);
    }

    private function persist(): void
    {
        session(['admin_dictionary_entries' => $this->entries]);
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editIdx     = null;
        $this->word        = '';
        $this->category    = 'Sapaan & Sehari-hari';
        $this->handType    = '2 Tangan';
        $this->description = '';
        $this->tip         = '';
        $this->mediaFile   = null;
        $this->mediaUrl    = '';
        $this->showModal   = true;
    }

    public function openEdit(int $idx): void
    {
        $entry = $this->entries[$idx] ?? null;
        if (! $entry) return;

        $this->resetValidation();
        $this->editIdx     = $idx;
        $this->word        = $entry['word'] ?? '';
        $this->category    = $entry['category'] ?? 'Sapaan & Sehari-hari';
        $this->handType    = $entry['hand_type'] ?? '2 Tangan';
        $this->description = $entry['description'] ?? '';
        $this->tip         = $entry['tip'] ?? '';
        $this->mediaFile   = null;
        $this->mediaUrl    = $entry['media_url'] ?? '';
        $this->showModal   = true;
    }

    public function saveEntry(): void
    {
        $this->validate([
            'word'        => 'required|string|min:1|max:80',
            'category'    => 'required|string|max:50',
            'description' => 'required|string|max:400',
            'mediaFile'   => 'nullable|file|mimes:mp4,webm,gif,png,jpg,jpeg,webp|max:30720',
        ], [
            'word.required'        => 'Kata atau frasa isyarat wajib diisi.',
            'description.required' => 'Deskripsi gerakan isyarat wajib diisi.',
            'mediaFile.mimes'      => 'File media harus berupa video (MP4, WEBM) atau gambar/GIF (GIF, PNG, JPG).',
        ]);

        $finalUrl = $this->mediaUrl;
        if ($this->mediaFile) {
            try {
                $path = $this->mediaFile->store('dictionary/media', 'public');
                $finalUrl = '/storage/' . $path;
            } catch (\Throwable $e) {
                // fallback
            }
        }

        $payload = [
            'word'        => $this->word,
            'category'    => $this->category,
            'hand_type'   => $this->handType,
            'description' => $this->description,
            'tip'         => $this->tip,
            'media_url'   => $finalUrl,
        ];

        if ($this->editIdx !== null && isset($this->entries[$this->editIdx])) {
            $this->entries[$this->editIdx] = $payload;

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Kosakata "' . $this->word . '" berhasil diperbarui!',
            ]);
        } else {
            $this->entries[] = $payload;

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Kosakata baru berhasil ditambahkan ke kamus!',
            ]);
        }

        $this->persist();
        $this->showModal = false;
    }

    public function deleteEntry(int $idx): void
    {
        $word = $this->entries[$idx]['word'] ?? 'Kosakata';
        unset($this->entries[$idx]);
        $this->entries = array_values($this->entries);
        $this->persist();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Kosakata "' . $word . '" berhasil dihapus dari kamus.',
        ]);
    }
};
?>

<div class="space-y-6">
    <x-admin.page-header
        title="Kamus Kosakata BISINDO"
        subtitle="Kelola direktori kata, kategori sapaan & komunikasi, serta video/GIF peragaan visual kamus."
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="openCreate"
                class="inline-flex items-center gap-2 bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all cursor-pointer shadow-xs"
            >
                <flux:icon.plus class="size-4" />
                <span>Tambah Kosakata</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter & Search Responsif --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Cari kata atau arti dalam kamus BISINDO..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-medium focus:outline-none focus:border-[#F9B92F] transition-colors"
            />
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#71717A] pointer-events-none">
                <flux:icon.magnifying-glass class="size-4" />
            </div>
        </div>

        <select
            wire:model.live="categoryFilter"
            class="px-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-extrabold bg-white text-[#1C1C1E] focus:outline-none focus:border-[#F9B92F] cursor-pointer"
        >
            <option value="all">Semua Kategori</option>
            <option value="Abjad & Ejaan">Abjad & Ejaan</option>
            <option value="Angka & Waktu">Angka & Waktu</option>
            <option value="Sapaan & Sehari-hari">Sapaan & Sehari-hari</option>
            <option value="Keluarga & Hubungan">Keluarga & Hubungan</option>
            <option value="Aktivitas & Kata Kerja">Aktivitas & Kata Kerja</option>
        </select>
    </div>

    {{-- Tabel Kosakata Kamus --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E4E4E7] text-[11px] font-extrabold uppercase tracking-wider text-[#71717A] bg-[#FAFAFA]">
                        <th class="py-3.5 px-5">Kata / Isyarat</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Tangan</th>
                        <th class="py-3.5 px-4">Deskripsi Gerakan</th>
                        <th class="py-3.5 px-4">Media</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E4E7] text-sm">
                    @php
                        $filtered = array_filter($entries, function ($e) {
                            $matchSearch = empty($this->search)
                                || str_contains(strtolower($e['word'] ?? ''), strtolower($this->search))
                                || str_contains(strtolower($e['description'] ?? ''), strtolower($this->search));

                            $matchCat = $this->categoryFilter === 'all' || ($e['category'] ?? '') === $this->categoryFilter;

                            return $matchSearch && $matchCat;
                        });
                    @endphp

                    @forelse ($filtered as $idx => $entry)
                        @php
                            $media = $entry['media_url'] ?? '';
                            $isImgOrGif = preg_match('/\.(gif|png|jpe?g|webp|svg)$/i', $media);
                        @endphp
                        <tr class="hover:bg-zinc-50/70 transition-colors">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center font-extrabold text-sm text-[#1C1C1E] shrink-0">
                                        {{ substr($entry['word'], 0, 2) }}
                                    </div>
                                    <span class="font-extrabold text-sm text-[#1C1C1E]">{{ $entry['word'] }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-[#F4F4F5] text-[#52525B]">
                                    {{ $entry['category'] }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-xs font-bold text-[#71717A]">
                                {{ $entry['hand_type'] ?? '2 Tangan' }}
                            </td>
                            <td class="py-4 px-4 text-xs text-[#52525B] max-w-xs font-medium leading-relaxed">
                                <p class="truncate">{{ $entry['description'] }}</p>
                                @if (!empty($entry['tip']))
                                    <span class="text-[10px] text-[#C98A06] font-bold block truncate">💡 {{ $entry['tip'] }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if (!empty($media))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-[#F0FDF4] border border-[#86EFAC] text-[#15803D]">
                                        {{ $isImgOrGif ? '🖼️ Gambar/GIF' : '🎬 Video' }}
                                    </span>
                                @else
                                    <span class="text-xs text-zinc-400 font-semibold">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right space-x-1.5">
                                <button
                                    type="button"
                                    wire:click="openEdit({{ $idx }})"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#E4E4E7] hover:bg-zinc-100 text-xs font-extrabold text-[#52525B] cursor-pointer"
                                    title="Edit Kosakata"
                                >
                                    ✎
                                </button>
                                <button
                                    type="button"
                                    wire:click="deleteEntry({{ $idx }})"
                                    wire:confirm="Yakin ingin menghapus kata '{{ $entry['word'] }}' dari kamus?"
                                    class="px-2.5 py-1.5 rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-xs font-extrabold text-[#B91C1C] cursor-pointer"
                                    title="Hapus"
                                >
                                    ✕
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-xs font-medium text-[#71717A]">
                                Belum ada kosakata yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah / Edit Kosakata (Modular Component) --}}
    <x-admin.kamus.entry-modal
        :show="$showModal"
        :edit-index="$editIdx"
        :media-file="$mediaFile"
        :media-url="$mediaUrl"
    />
</div>
