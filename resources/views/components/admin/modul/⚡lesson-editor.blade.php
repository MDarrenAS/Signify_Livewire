<?php

use App\Models\LibraryItem;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public array $part = [];
    public int $partIndex = 0;

    public bool $showModal = false;
    public ?int $selectedLibraryItemId = null;
    public string $title = '';
    public string $videoUrl = '';
    public $mediaFile = null;
    public string $description = '';
    public string $tip = '';
    public string $handType = '2 Tangan';

    public function updatedSelectedLibraryItemId(?int $libId): void
    {
        if (! $libId) {
            return;
        }

        $libItem = LibraryItem::with('media')->find($libId);
        if (! $libItem) {
            return;
        }

        $this->title = $libItem->title;
        $this->description = $libItem->movement_description ?: ($libItem->description ?: 'Perhatikan gerakan tangan dengan seksama.');

        if ($libItem->media->isNotEmpty()) {
            try {
                $this->videoUrl = $libItem->media->first()->temporaryUrl();
            } catch (\Throwable) {
                $this->videoUrl = $libItem->media->first()->storage_path ?? '';
            }
        }
    }

    public function saveItem(): void
    {
        $this->validate([
            'title'       => 'required|string|max:60',
            'description' => 'required|string|max:400',
            'mediaFile'   => 'nullable|file|mimes:mp4,webm,gif,png,jpg,jpeg,webp|max:30720',
        ], [
            'title.required'       => 'Huruf atau kata isyarat wajib diisi.',
            'description.required' => 'Deskripsi gerakan wajib diisi.',
            'mediaFile.mimes'      => 'File media harus berupa video (MP4, WEBM) atau gambar (GIF, PNG, JPG, WEBP).',
        ]);

        $finalMediaUrl = $this->videoUrl;

        // Tangani penyimpanan file jika admin upload
        if ($this->mediaFile) {
            try {
                $path = $this->mediaFile->store('signs/lessons', 'public');
                $finalMediaUrl = '/storage/' . $path;
            } catch (\Throwable $e) {
                // fallback
            }
        }

        $order = count($this->part['items'] ?? []) + 1;

        $this->part['items'][] = [
            'library_item_id' => $this->selectedLibraryItemId,
            'order'           => $order,
            'symbol'          => strtoupper(substr($this->title, 0, 3)),
            'title'           => $this->title,
            'hand_type'       => $this->handType,
            'video_url'       => $finalMediaUrl,
            'instruction'     => $this->description,
            'description'     => $this->description,
            'una_tip'         => $this->tip ?: 'Perhatikan posisi jari dan arah telapak tangan.',
            'tip'             => $this->tip ?: 'Perhatikan posisi jari dan arah telapak tangan.',
        ];

        $this->dispatch('part-updated', index: $this->partIndex, part: $this->part);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Isyarat "' . $this->title . '" berhasil ditambahkan!',
        ]);

        $this->reset(['selectedLibraryItemId', 'title', 'videoUrl', 'mediaFile', 'description', 'tip', 'showModal']);
    }

    public function removeItem(int $itemIdx): void
    {
        $title = $this->part['items'][$itemIdx]['title'] ?? 'Item';
        unset($this->part['items'][$itemIdx]);
        $this->part['items'] = array_values($this->part['items']);

        // Rapikan urutan
        foreach ($this->part['items'] as $i => &$item) {
            $item['order'] = $i + 1;
        }

        $this->dispatch('part-updated', index: $this->partIndex, part: $this->part);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Isyarat "' . $title . '" berhasil dihapus.',
        ]);
    }
};
?>

<div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden shadow-2xs">
    {{-- Header Card --}}
    <div class="px-5 py-4 bg-[#FAFAFA] border-b-2 border-[#E4E4E7] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md bg-[#FFF8E6] border border-[#F9B92F] text-[10px] font-extrabold text-[#C98A06] uppercase">
                    Materi Belajar
                </span>
                <span class="text-xs font-bold text-[#71717A]">
                    {{ count($part['items'] ?? []) }} Isyarat Terdaftar
                </span>
            </div>
            <h2 class="text-base font-extrabold text-[#1C1C1E] mt-0.5">Daftar Kosakata & Peragaan Isyarat</h2>
            <p class="text-xs text-[#71717A] font-medium">
                Siswa mendapat <strong class="text-[#C98A06]">+{{ $part['xp_reward'] ?? 0 }} XP</strong> setelah menuntaskan seluruh isyarat di bagian ini.
            </p>
        </div>

        <button
            type="button"
            wire:click="$set('showModal', true)"
            class="px-4 py-2.5 rounded-xl bg-[#22B05B] hover:bg-[#1ea052] border-2 border-b-4 border-[#15803D] active:translate-y-0.5 active:border-b-2 text-white text-xs font-extrabold cursor-pointer transition-all self-start sm:self-auto shadow-xs"
        >
            + Tambah Isyarat Baru
        </button>
    </div>

    {{-- Daftar Item Isyarat --}}
    <div class="divide-y divide-[#E4E4E7]">
        @forelse ($part['items'] ?? [] as $iIdx => $item)
            @php
                $media = $item['video_url'] ?? '';
                if ($media && ! str_starts_with($media, 'http') && ! str_starts_with($media, '/storage/')) {
                    try {
                        $media = \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($media, now()->addMinutes(60));
                    } catch (\Throwable) {
                        $media = \Illuminate\Support\Facades\Storage::disk('s3')->url($media);
                    }
                }
                $isImageOrGif = preg_match('/\.(gif|png|jpe?g|webp|svg)($|\?)/i', $media);
            @endphp
            <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-zinc-50/50 transition-colors">
                <div class="flex items-start gap-4">
                    {{-- Preview Ikon / Symbol / Thumbnail --}}
                    <div class="w-14 h-14 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center font-extrabold text-lg text-[#1C1C1E] shrink-0 overflow-hidden shadow-2xs">
                        @if (!empty($media) && $isImageOrGif)
                            <img src="{{ $media }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover" />
                        @else
                            {{ $item['symbol'] ?? substr($item['title'] ?? 'A', 0, 2) }}
                        @endif
                    </div>

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-extrabold text-[#1C1C1E]">
                                Isyarat "{{ $item['title'] }}"
                            </h3>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ !empty($media) ? 'bg-[#F0FDF4] border border-[#86EFAC] text-[#15803D]' : 'bg-zinc-100 border border-[#E4E4E7] text-[#71717A]' }}">
                                {{ !empty($media) ? ($isImageOrGif ? '🖼️ Gambar/GIF Terpasang' : '🎬 Video Terpasang') : 'Tanpa Media' }}
                            </span>
                            @if (!empty($item['hand_type']))
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#F4F4F5] text-[#52525B]">
                                    {{ $item['hand_type'] }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-[#52525B] font-medium leading-relaxed max-w-2xl">
                            {{ $item['instruction'] ?? $item['description'] ?? '-' }}
                        </p>

                        @if (!empty($item['una_tip']) || !empty($item['tip']))
                            <p class="text-[11px] text-[#C98A06] font-bold mt-1">
                                💡 Tips: {{ $item['una_tip'] ?? $item['tip'] }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Aksi --}}
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    <button
                        type="button"
                        wire:click="removeItem({{ $iIdx }})"
                        wire:confirm="Yakin ingin menghapus isyarat '{{ $item['title'] }}'?"
                        class="px-3 py-1.5 rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-[#B91C1C] text-xs font-extrabold cursor-pointer transition-colors"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        @empty
            <div class="p-10 text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] mx-auto flex items-center justify-center text-xl">
                    🤟
                </div>
                <h4 class="text-sm font-extrabold text-[#1C1C1E]">Belum Ada Item Isyarat</h4>
                <p class="text-xs text-[#71717A] max-w-sm mx-auto">Tambahkan isyarat huruf, kata, atau kalimat beserta media peragaan video/GIF dan panduan gerakannya.</p>
            </div>
        @endforelse
    </div>

    {{-- Modal Tambah Isyarat --}}
    <x-admin.modal :show="$showModal" title="Tambah Item Isyarat Materi" close-action="$set('showModal', false)" max-width="max-w-lg">
        <form wire:submit="saveItem" class="space-y-4">
            {{-- Pilihan dari Kamus BISINDO --}}
            @php
                $kamusItems = \App\Models\LibraryItem::orderBy('title')->get(['id', 'title', 'item_type']);
            @endphp
            @if ($kamusItems->isNotEmpty())
                <div class="p-3 bg-[#FFFDF7] border-2 border-[#F9B92F] rounded-xl space-y-1">
                    <label class="block text-xs font-extrabold text-[#C98A06]">
                        📚 Hubungkan ke Kamus BISINDO (Gunakan Media yang Sudah Ada)
                    </label>
                    <select
                        wire:model.live="selectedLibraryItemId"
                        class="w-full px-3 py-2 rounded-lg border border-[#F9B92F] text-xs font-bold bg-white text-[#1C1C1E] focus:outline-none"
                    >
                        <option value="">-- Pilih entri kamus agar media otomatis terpasang --</option>
                        @foreach ($kamusItems as $kItem)
                            <option value="{{ $kItem->id }}">{{ $kItem->title }} ({{ ucfirst($kItem->item_type) }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-[#71717A]">
                        Pilih entri kamus agar media video/gambar tidak perlu diunggah ulang (menghemat kuota bucket Supabase).
                    </p>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Huruf / Kata Isyarat</label>
                    <input
                        type="text"
                        wire:model="title"
                        placeholder="Contoh: Huruf B atau Terima Kasih"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-sm font-bold focus:outline-none focus:border-[#F9B92F]"
                    />
                    @error('title') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Jenis Tangan</label>
                    <select
                        wire:model="handType"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-bold bg-white text-[#1C1C1E]"
                    >
                        <option value="1 Tangan">1 Tangan</option>
                        <option value="2 Tangan">2 Tangan</option>
                    </select>
                </div>
            </div>

            {{-- Uploader Media: Video, Gambar, atau GIF --}}
            <x-admin.file-uploader
                model="mediaFile"
                url-model="videoUrl"
                :current-url="$videoUrl"
                accept="video/mp4,video/webm,image/*,.gif"
                label="Media Peragaan Gerakan (Video / GIF / Gambar)"
                hint="Format file yang didukung: MP4, WEBM, GIF, PNG, JPG (Maks. 30MB)"
            >
                {{-- Live Preview saat file dipilih --}}
                @if ($mediaFile)
                    @php $isImage = str_contains($mediaFile->getMimeType(), 'image'); @endphp
                    <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-48 flex items-center justify-center p-2">
                        @if ($isImage)
                            <img src="{{ $mediaFile->temporaryUrl() }}" class="h-full object-contain" />
                        @else
                            <video src="{{ $mediaFile->temporaryUrl() }}" controls muted loop class="h-full object-contain"></video>
                        @endif
                    </div>
                @elseif (trim($videoUrl) !== '')
                    @php $isImageOrGif = preg_match('/\.(gif|png|jpe?g|webp|svg)($|\?)/i', $videoUrl); @endphp
                    <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-48 flex items-center justify-center p-2">
                        @if ($isImageOrGif)
                            <img src="{{ $videoUrl }}" class="h-full object-contain" />
                        @else
                            <video src="{{ $videoUrl }}" controls muted loop class="h-full object-contain"></video>
                        @endif
                    </div>
                @endif
            </x-admin.file-uploader>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Instruksi & Cara Memperagakan</label>
                <textarea
                    wire:model="description"
                    rows="3"
                    placeholder="Jelaskan detail posisi tangan, jari yang tegak, dan arah gerakan..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
                ></textarea>
                @error('description') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Tips Maskot Una (Opsional)</label>
                <input
                    type="text"
                    wire:model="tip"
                    placeholder="Contoh: Pastikan ibu jari tidak menutupi keempat jari lainnya."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs font-medium focus:outline-none focus:border-[#F9B92F]"
                />
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
                    Simpan Isyarat
                </button>
            </div>
        </form>
    </x-admin.modal>
</div>
