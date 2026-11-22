@props([
    'show' => false,
    'itemId' => null,
    'categories' => [],
    'selectedMedia' => [],
    'mediaOptions' => [],
    'upload' => null,
    'uploadLimitKb' => 2048,
])

<x-admin.modal
    :show="$show"
    :title="$itemId !== null ? 'Edit Kosakata Kamus' : 'Tambah Kosakata Kamus Baru'"
    close-action="closeModal"
    max-width="max-w-3xl"
>
    <form wire:submit="save" class="grid gap-4 sm:grid-cols-2">
        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
            Kategori
            <select
                wire:model="categoryId"
                class="w-full rounded-xl border border-[#E4E4E7] bg-white px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            >
                <option value="0">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('categoryId') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
            Kata / frasa
            <input
                type="text"
                wire:model="title"
                maxlength="255"
                placeholder="Contoh: Selamat Pagi"
                class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            >
            @error('title') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
            Slug
            <input
                type="text"
                wire:model="slug"
                maxlength="255"
                placeholder="selamat-pagi"
                class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            >
            @error('slug') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
            Jenis kosakata
            <select
                wire:model="itemType"
                class="w-full rounded-xl border border-[#E4E4E7] bg-white px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            >
                <option value="alphabet">Huruf</option>
                <option value="number">Angka</option>
                <option value="word">Kata</option>
                <option value="phrase">Frasa</option>
                <option value="expression">Ekspresi</option>
            </select>
            @error('itemType') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
            Status
            <select
                wire:model="status"
                class="w-full rounded-xl border border-[#E4E4E7] bg-white px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            >
                <option value="draft">Draft</option>
                <option value="published">Terbit</option>
                <option value="archived">Arsip</option>
            </select>
            @error('status') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
            Urutan
            <input
                type="number"
                wire:model="sortOrder"
                min="0"
                class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            >
            @error('sortOrder') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E] sm:col-span-2">
            Makna
            <textarea
                wire:model="meaning"
                rows="2"
                class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            ></textarea>
            @error('meaning') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E] sm:col-span-2">
            Deskripsi
            <textarea
                wire:model="description"
                rows="2"
                class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            ></textarea>
            @error('description') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E] sm:col-span-2">
            Panduan peragaan gerakan
            <textarea
                wire:model="movementDescription"
                rows="3"
                placeholder="Tuliskan runtutan gerakan isyarat."
                class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            ></textarea>
            @error('movementDescription') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E] sm:col-span-2">
            Contoh penggunaan
            <textarea
                wire:model="exampleUsage"
                rows="2"
                class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
            ></textarea>
            @error('exampleUsage') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </label>

        <fieldset class="space-y-3 rounded-xl border border-[#E4E4E7] p-3 sm:col-span-2">
            <legend class="px-1 text-xs font-extrabold text-[#1C1C1E]">
                Media terpasang (urutan pertama menjadi media utama)
            </legend>

            @forelse ($selectedMedia as $index => $media)
                <div wire:key="selected-media-{{ $media->id }}" class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="min-w-0 flex-1 truncate font-semibold">
                        {{ $media->alt_text ?: basename($media->storage_path) }}
                        <span class="text-[#71717A]">· {{ $media->mime_type }}</span>
                    </span>
                    <button
                        type="button"
                        wire:click="moveMedia({{ $media->id }}, -1)"
                        @disabled($index === 0)
                        class="rounded-lg border border-[#E4E4E7] px-2 py-1 font-bold disabled:opacity-40"
                    >
                        Naik
                    </button>
                    <button
                        type="button"
                        wire:click="moveMedia({{ $media->id }}, 1)"
                        @disabled($index === $selectedMedia->count() - 1)
                        class="rounded-lg border border-[#E4E4E7] px-2 py-1 font-bold disabled:opacity-40"
                    >
                        Turun
                    </button>
                    <button
                        type="button"
                        wire:click="removeMedia({{ $media->id }})"
                        class="rounded-lg border border-[#FCA5A5] bg-[#FEF2F2] px-2 py-1 font-bold text-[#B91C1C]"
                    >
                        Lepas
                    </button>
                </div>
            @empty
                <p class="text-xs text-[#71717A]">Belum ada media terpasang.</p>
            @endforelse

            <label class="grid gap-1 text-xs font-bold">
                Cari media yang sudah diunggah
                <input
                    type="search"
                    wire:model.live.debounce.300ms="mediaSearch"
                    class="w-full rounded-xl border border-[#E4E4E7] px-3 py-2 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
                >
            </label>

            <div class="max-h-40 space-y-2 overflow-y-auto">
                @forelse ($mediaOptions as $media)
                    <label
                        wire:key="media-option-{{ $media->id }}"
                        class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs hover:bg-zinc-50"
                    >
                        <input type="checkbox" wire:model="selectedMediaIds" value="{{ $media->id }}">
                        <span class="truncate">
                            {{ $media->alt_text ?: basename($media->storage_path) }}
                            <span class="text-[#71717A]">· {{ $media->media_type }} · {{ $media->mime_type }}</span>
                        </span>
                    </label>
                @empty
                    <p class="text-xs text-[#71717A]">Tidak ada media yang cocok.</p>
                @endforelse
            </div>

            @error('selectedMediaIds') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            @error('selectedMediaIds.*') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
        </fieldset>

        <div class="space-y-3 sm:col-span-2">
            <x-admin.file-uploader
                model="upload"
                :allow-url="false"
                accept="image/png,image/jpeg,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                label="Media peragaan Kamus"
                :hint="'PNG, JPG, WebP, GIF, MP4, WebM, atau MOV (maks. '.number_format($uploadLimitKb / 1024, 1).' MB)'"
            >
                @if ($upload)
                    @if (str_starts_with((string) $upload->getMimeType(), 'image/'))
                        <img
                            src="{{ $upload->temporaryUrl() }}"
                            alt="Pratinjau media"
                            class="max-h-44 rounded-xl object-contain"
                        >
                    @else
                        <p class="text-xs font-semibold text-[#52525B]">{{ $upload->getClientOriginalName() }}</p>
                    @endif
                @endif
            </x-admin.file-uploader>

            <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
                Teks alternatif
                <input
                    type="text"
                    wire:model="uploadAltText"
                    maxlength="255"
                    class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
                >
                @error('uploadAltText') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1 text-xs font-extrabold text-[#1C1C1E]">
                Keterangan media
                <input
                    type="text"
                    wire:model="uploadCaption"
                    maxlength="255"
                    class="w-full rounded-xl border border-[#E4E4E7] px-3.5 py-2.5 text-sm font-medium focus:border-[#F9B92F] focus:outline-none"
                >
                @error('uploadCaption') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </label>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-[#E4E4E7] pt-3 sm:col-span-2">
            <button
                type="button"
                wire:click="closeModal"
                class="cursor-pointer rounded-xl border border-[#E4E4E7] px-4 py-2 text-xs font-extrabold text-[#52525B] hover:bg-zinc-100"
            >
                Batal
            </button>
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="save"
                class="cursor-pointer rounded-xl border-2 border-b-4 border-[#C98A06] bg-[#F9B92F] px-5 py-2 text-xs font-extrabold text-[#1C1C1E] active:translate-y-0.5 active:border-b-2"
            >
                Simpan Kosakata
            </button>
        </div>
    </form>
</x-admin.modal>
