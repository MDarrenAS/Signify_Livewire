<?php

use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\LibraryMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts::admin')] #[Title('Kelola Kamus — Admin Signify')] class extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?int $itemId = null;

    public int $categoryId = 0;

    public string $title = '';

    public string $slug = '';

    public string $itemType = 'word';

    public ?string $meaning = null;

    public ?string $description = null;

    public ?string $movementDescription = null;

    public ?string $exampleUsage = null;

    public int $sortOrder = 0;

    public string $status = 'draft';

    public string $search = '';

    public string $categoryFilter = 'all';

    public bool $showModal = false;

    public string $mediaSearch = '';

    /** @var array<int, int|string> */
    public array $selectedMediaIds = [];

    public ?TemporaryUploadedFile $upload = null;

    public ?string $uploadAltText = null;

    public ?string $uploadCaption = null;

    public function mount(): void
    {
        $this->authorizeAdmin();
    }

    #[Computed]
    public function items(): LengthAwarePaginator
    {
        $this->authorizeAdmin();

        return LibraryItem::query()
            ->with(['category', 'media'])
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $search = '%'.trim($this->search).'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', $search)
                        ->orWhere('slug', 'like', $search)
                        ->orWhere('meaning', 'like', $search)
                        ->orWhere('description', 'like', $search)
                        ->orWhere('movement_description', 'like', $search);
                });
            })
            ->when($this->categoryFilter !== 'all', function (Builder $query): void {
                $query->where('category_id', (int) $this->categoryFilter);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories(): Collection
    {
        $this->authorizeAdmin();

        return LibraryCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function mediaOptions(): Collection
    {
        $this->authorizeAdmin();

        $search = trim($this->mediaSearch);

        return LibraryMedia::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $term = '%'.$search.'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('alt_text', 'like', $term)
                        ->orWhere('storage_path', 'like', $term)
                        ->orWhere('mime_type', 'like', $term);
                });
            })
            ->latest('id')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function selectedMedia(): Collection
    {
        $this->authorizeAdmin();

        $ids = array_map('intval', $this->selectedMediaIds);
        $mediaById = LibraryMedia::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id): ?LibraryMedia => $mediaById->get($id))
            ->filter()
            ->values();
    }

    public function newEntry(): void
    {
        $this->authorizeAdmin();

        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->authorizeAdmin();

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->itemId = null;
        $this->categoryId = 0;
        $this->title = '';
        $this->slug = '';
        $this->itemType = 'word';
        $this->meaning = null;
        $this->description = null;
        $this->movementDescription = null;
        $this->exampleUsage = null;
        $this->sortOrder = 0;
        $this->status = 'draft';
        $this->selectedMediaIds = [];
        $this->upload = null;
        $this->uploadAltText = null;
        $this->uploadCaption = null;
        $this->resetValidation();
        $this->resetPage();
    }

    public function edit(int $itemId): void
    {
        $this->authorizeAdmin();

        $item = LibraryItem::query()
            ->with('media')
            ->findOrFail($itemId);

        $this->itemId = $item->id;
        $this->categoryId = $item->category_id;
        $this->title = $item->title;
        $this->slug = $item->slug;
        $this->itemType = $item->item_type;
        $this->meaning = $item->meaning;
        $this->description = $item->description;
        $this->movementDescription = $item->movement_description;
        $this->exampleUsage = $item->example_usage;
        $this->sortOrder = $item->sort_order;
        $this->status = $item->status;
        $this->selectedMediaIds = $item->media
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->all();
        $this->upload = null;
        $this->uploadAltText = null;
        $this->uploadCaption = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();
        $this->slug = Str::slug($this->slug !== '' ? $this->slug : $this->title);
        $categoryRule = Rule::exists('library_categories', 'id');

        if ($this->status === 'published') {
            $categoryRule->where('status', 'published');
        }

        $validated = $this->validate([
            'categoryId' => ['required', 'integer', $categoryRule],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('library_items', 'slug')->ignore($this->itemId),
            ],
            'itemType' => ['required', Rule::in(['alphabet', 'number', 'word', 'phrase', 'expression'])],
            'meaning' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'movementDescription' => ['nullable', 'string'],
            'exampleUsage' => ['nullable', 'string'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'selectedMediaIds' => ['array'],
            'selectedMediaIds.*' => [
                'integer',
                'distinct',
                Rule::exists('library_media', 'id'),
            ],
            'upload' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,gif,mp4,webm,mov',
                'extensions:jpg,jpeg,png,webp,gif,mp4,webm,mov',
                'max:'.(int) config('services.media.max_upload_kb', 51200),
            ],
            'uploadAltText' => ['nullable', 'string', 'max:255'],
            'uploadCaption' => ['nullable', 'string', 'max:255'],
        ], [
            'upload.mimes' => 'Unggah gambar PNG, JPG, WebP, atau GIF, maupun video MP4, WebM, atau MOV.',
            'upload.extensions' => 'Ekstensi file tidak sesuai dengan jenis medianya.',
            'upload.max' => 'Ukuran file melebihi batas yang diizinkan.',
        ]);

        $item = $this->itemId === null
            ? new LibraryItem()
            : LibraryItem::query()->findOrFail($this->itemId);

        DB::transaction(function () use ($item, $validated): void {
            $item->fill([
                'category_id' => $validated['categoryId'],
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'item_type' => $validated['itemType'],
                'meaning' => $validated['meaning'] ?? null,
                'description' => $validated['description'] ?? null,
                'movement_description' => $validated['movementDescription'] ?? null,
                'example_usage' => $validated['exampleUsage'] ?? null,
                'sort_order' => $validated['sortOrder'],
                'status' => $validated['status'],
            ]);

            if (! $item->exists) {
                $item->created_by = auth()->id();
            }

            $item->save();
            $this->syncMedia($item, $validated['selectedMediaIds'] ?? []);
        });

        $this->itemId = $item->id;

        if ($this->upload !== null && ! $this->storeUpload($item)) {
            return;
        }

        $this->selectedMediaIds = $item->media()
            ->pluck('library_media.id')
            ->map(fn (int $id): string => (string) $id)
            ->all();

        $this->showModal = false;
        session()->flash('status', 'Entri Kamus berhasil disimpan.');
    }

    public function delete(int $itemId): void
    {
        $this->authorizeAdmin();

        LibraryItem::query()->findOrFail($itemId)->delete();

        if ($this->itemId === $itemId) {
            $this->closeModal();
        }

        session()->flash('status', 'Entri Kamus berhasil dihapus.');
    }

    public function moveMedia(int $mediaId, int $direction): void
    {
        $this->authorizeAdmin();

        if (! in_array($direction, [-1, 1], true)) {
            abort(422);
        }

        $ids = array_values(array_map('intval', $this->selectedMediaIds));
        $index = array_search($mediaId, $ids, true);
        $targetIndex = $index === false ? false : $index + $direction;

        if ($index === false || $targetIndex < 0 || $targetIndex >= count($ids)) {
            return;
        }

        [$ids[$index], $ids[$targetIndex]] = [$ids[$targetIndex], $ids[$index]];
        $this->selectedMediaIds = array_map('strval', $ids);
    }

    public function removeMedia(int $mediaId): void
    {
        $this->authorizeAdmin();

        $this->selectedMediaIds = array_values(array_filter(
            array_map('intval', $this->selectedMediaIds),
            fn (int $id): bool => $id !== $mediaId,
        ));
    }

    private function syncMedia(LibraryItem $item, array $mediaIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $mediaIds)));
        $previousCaptions = $item->media()
            ->get()
            ->mapWithKeys(fn (LibraryMedia $media): array => [
                $media->id => $media->pivot->caption,
            ]);
        $mediaById = LibraryMedia::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
        $attachments = [];

        foreach ($ids as $sortOrder => $mediaId) {
            $media = $mediaById->get($mediaId);

            if ($media === null) {
                throw ValidationException::withMessages([
                    'selectedMediaIds' => 'A selected media item no longer exists.',
                ]);
            }

            $attachments[$mediaId] = [
                'sort_order' => $sortOrder,
                'is_primary' => $sortOrder === 0,
                'caption' => $previousCaptions->has($mediaId)
                    ? $previousCaptions->get($mediaId)
                    : $media->caption,
            ];
        }

        DB::transaction(function () use ($item, $attachments): void {
            DB::table('library_item_media')
                ->where('library_item_id', $item->id)
                ->update(['is_primary' => false]);

            $item->media()->sync($attachments);
        });
    }

    private function storeUpload(LibraryItem $item): bool
    {
        $mimeType = $this->upload->getMimeType();
        $extension = strtolower((string) ($this->upload->getClientOriginalExtension() ?: $this->upload->extension()));

        if (! is_string($mimeType) || $extension === '') {
            $this->addError('upload', 'Jenis file tidak dapat ditentukan.');

            return false;
        }

        $allowedMimes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
        ];

        $bucketMimeType = $allowedMimes[$extension] ?? $mimeType;

        try {
            $path = $this->upload->storeAs(
                'kamus/'.$item->id,
                Str::uuid().'.'.$extension,
                [
                    'disk' => 's3',
                    'ContentType' => $bucketMimeType,
                    'mimetype' => $bucketMimeType,
                ],
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('upload', 'File gagal diunggah. Entri sudah tersimpan; silakan coba lagi.');

            return false;
        }

        if (! is_string($path)) {
            $this->addError('upload', 'File gagal diunggah. Entri sudah tersimpan; silakan coba lagi.');

            return false;
        }

        try {
            $mediaId = DB::transaction(function () use ($item, $bucketMimeType, $path): int {
                $media = LibraryMedia::query()->create([
                    'library_item_id' => null,
                    'media_type' => str_starts_with($bucketMimeType, 'image/') ? 'image' : 'video',
                    'storage_path' => $path,
                    'mime_type' => $bucketMimeType,
                    'file_size_bytes' => (int) $this->upload?->getSize(),
                    'alt_text' => filled($this->uploadAltText) ? trim($this->uploadAltText) : null,
                    'caption' => filled($this->uploadCaption) ? trim($this->uploadCaption) : null,
                    'uploaded_by' => auth()->id(),
                ]);

                $mediaIds = array_map('intval', $this->selectedMediaIds);
                $mediaIds[] = $media->id;
                $this->syncMedia($item, $mediaIds);

                return $media->id;
            });
        } catch (\Throwable $exception) {
            try {
                if (! Storage::disk('s3')->delete($path)) {
                    Log::warning('Failed to remove a Kamus upload after a database error.', [
                        'storage_path' => $path,
                    ]);
                }
            } catch (\Throwable $cleanupException) {
                report($cleanupException);
            }

            throw $exception;
        }

        $this->selectedMediaIds[] = (string) $mediaId;
        $this->upload = null;
        $this->uploadAltText = null;
        $this->uploadCaption = null;

        return true;
    }

    private function authorizeAdmin(): void
    {
        Gate::authorize('manage-kamus');
    }
};
?>

<div class="space-y-6">
    <x-admin.page-header
        title="Kamus Kosakata BISINDO"
        subtitle="Kelola kosakata, kategori, deskripsi gerakan, serta media Kamus."
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="newEntry"
                class="inline-flex items-center gap-2 rounded-xl border-2 border-b-4 border-[#C98A06] bg-[#F9B92F] px-4 py-2.5 text-xs font-extrabold text-[#1C1C1E] shadow-xs transition-all hover:bg-[#fabf45] active:translate-y-0.5 active:border-b-2 sm:text-sm"
            >
                <flux:icon.plus class="size-4" />
                <span>Tambah Kosakata</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <p role="status" class="rounded-xl border border-[#86EFAC] bg-[#F0FDF4] px-4 py-3 text-sm font-semibold text-[#15803D]">
            {{ session('status') }}
        </p>
    @endif

    <div class="flex flex-col gap-3 rounded-2xl border-2 border-b-4 border-[#E4E4E7] bg-white p-4 sm:flex-row">
        <label class="relative flex-1">
            <span class="sr-only">Cari kosakata</span>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari kata atau arti dalam Kamus BISINDO..."
                class="w-full rounded-xl border-2 border-[#E4E4E7] py-2.5 pl-10 pr-4 text-xs font-medium transition-colors focus:border-[#F9B92F] focus:outline-none sm:text-sm"
            >
            <flux:icon.magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-[#71717A]" />
        </label>

        <label>
            <span class="sr-only">Filter kategori</span>
            <select
                wire:model.live="categoryFilter"
                class="cursor-pointer rounded-xl border-2 border-[#E4E4E7] bg-white px-4 py-2.5 text-xs font-extrabold text-[#1C1C1E] focus:border-[#F9B92F] focus:outline-none sm:text-sm"
            >
                <option value="all">Semua Kategori</option>
                @foreach ($this->categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="overflow-hidden rounded-2xl border-2 border-b-4 border-[#E4E4E7] bg-white shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-[#E4E4E7] bg-[#FAFAFA] text-[11px] font-extrabold uppercase tracking-wider text-[#71717A]">
                        <th class="px-5 py-3.5">Kata / Isyarat</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Jenis</th>
                        <th class="px-4 py-3.5">Makna & Deskripsi</th>
                        <th class="px-4 py-3.5">Media</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E4E7] text-sm">
                    @forelse ($this->items as $item)
                        @php
                            $itemTypeLabel = match ($item->item_type) {
                                'alphabet' => 'Huruf',
                                'number' => 'Angka',
                                'phrase' => 'Frasa',
                                'expression' => 'Ekspresi',
                                default => 'Kata',
                            };
                        @endphp
                        <tr wire:key="library-item-{{ $item->id }}" class="transition-colors hover:bg-zinc-50/70">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl border-2 border-[#F9B92F] bg-[#FFF8E6] text-sm font-extrabold text-[#1C1C1E]">
                                        {{ mb_substr($item->title, 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-[#1C1C1E]">{{ $item->title }}</span>
                                        <span class="mt-1 block text-[11px] font-semibold text-[#71717A]">
                                            {{ $item->slug }} · {{ ucfirst($item->status) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-lg bg-[#F4F4F5] px-2.5 py-0.5 text-xs font-bold text-[#52525B]">
                                    {{ $item->category->name }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-[#71717A]">{{ $itemTypeLabel }}</td>
                            <td class="max-w-xs px-4 py-4 text-xs font-medium leading-relaxed text-[#52525B]">
                                <p class="truncate">{{ $item->meaning ?: $item->description ?: '—' }}</p>
                                @if (filled($item->movement_description))
                                    <span class="mt-1 block truncate text-[10px] font-bold text-[#C98A06]">
                                        {{ $item->movement_description }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($item->media as $media)
                                        <span
                                            wire:key="media-badge-{{ $item->id }}-{{ $media->id }}"
                                            class="inline-flex items-center rounded-full border border-[#86EFAC] bg-[#F0FDF4] px-2.5 py-0.5 text-[11px] font-extrabold text-[#15803D]"
                                        >
                                            {{ $media->media_type === 'image' ? 'Gambar' : 'Video' }}
                                        </span>
                                    @empty
                                        <span class="text-xs font-semibold text-zinc-400">Belum ada</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="space-x-1.5 px-5 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="edit({{ $item->id }})"
                                    class="cursor-pointer rounded-xl border border-[#E4E4E7] px-2.5 py-1.5 text-xs font-extrabold text-[#52525B] hover:bg-zinc-100"
                                    title="Edit kosakata"
                                >
                                    ✎
                                </button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $item->id }})"
                                    wire:confirm="Yakin ingin menghapus '{{ $item->title }}' dari kamus?"
                                    class="cursor-pointer rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] px-2.5 py-1.5 text-xs font-extrabold text-[#B91C1C] hover:bg-[#FEE2E2]"
                                    title="Hapus kosakata"
                                >
                                    ✕
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-xs font-medium text-[#71717A]">
                                Belum ada kosakata yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#E4E4E7] p-4">
            {{ $this->items->links() }}
        </div>
    </div>

    @if ($showModal)
        <x-admin.kamus.entry-modal
            :show="$showModal"
            :item-id="$itemId"
            :categories="$this->categories"
            :selected-media="$this->selectedMedia"
            :media-options="$this->mediaOptions"
            :upload="$upload"
            :upload-limit-kb="(int) config('services.media.max_upload_kb', 51200)"
        />
    @endif
</div>
