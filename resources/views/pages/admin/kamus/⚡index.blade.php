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
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
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
            ->with('category')
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $search = '%'.trim($this->search).'%';

                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', $search)
                        ->orWhere('slug', 'like', $search);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15);
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
        $this->reset();
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
        $this->resetValidation();
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
                'mimes:jpg,jpeg,png,webp,mp4,webm,mov',
                'extensions:jpg,jpeg,png,webp,mp4,webm,mov',
                'max:'.(int) config('services.media.max_upload_kb', 12288),
            ],
            'uploadAltText' => ['nullable', 'string', 'max:255'],
            'uploadCaption' => ['nullable', 'string', 'max:255'],
        ], [
            'upload.mimes' => 'Unggah gambar JPG, PNG, atau WebP, maupun video MP4, WebM, atau MOV.',
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

        if ($this->upload !== null) {
            $this->storeUpload($item);
        }

        $this->selectedMediaIds = $item->media()
            ->pluck('library_media.id')
            ->map(fn (int $id): string => (string) $id)
            ->all();

        session()->flash('status', 'Entri Kamus berhasil disimpan.');
    }

    public function delete(int $itemId): void
    {
        $this->authorizeAdmin();

        LibraryItem::query()->findOrFail($itemId)->delete();

        if ($this->itemId === $itemId) {
            $this->newEntry();
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

    private function storeUpload(LibraryItem $item): void
    {
        $mimeType = $this->upload->getMimeType();
        $extension = $this->upload->extension();

        if (! is_string($mimeType) || $extension === '') {
            $this->addError('upload', 'Jenis file tidak dapat ditentukan.');

            return;
        }

        try {
            $path = $this->upload->storeAs(
                'kamus/'.$item->id,
                Str::uuid().'.'.$extension,
                's3',
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('upload', 'File gagal diunggah. Entri sudah tersimpan; silakan coba lagi.');

            return;
        }

        if (! is_string($path)) {
            $this->addError('upload', 'File gagal diunggah. Entri sudah tersimpan; silakan coba lagi.');

            return;
        }

        try {
            $mediaId = DB::transaction(function () use ($item, $mimeType, $path): int {
                $media = LibraryMedia::query()->create([
                    'library_item_id' => null,
                    'media_type' => str_starts_with($mimeType, 'image/') ? 'image' : 'video',
                    'storage_path' => $path,
                    'mime_type' => $mimeType,
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
    }

    private function authorizeAdmin(): void
    {
        Gate::authorize('manage-kamus');
    }
};
?>

<div class="mx-auto max-w-6xl space-y-8 p-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold">Kelola Kamus</h1>
        <a href="{{ route('kamus.index') }}" wire:navigate class="underline">Lihat Kamus</a>
    </header>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <section class="space-y-4">
        <h2 class="text-xl font-medium">{{ $itemId ? 'Edit entri' : 'Entri baru' }}</h2>

        <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
            <label class="grid gap-1">
                Kategori
                <select wire:model="categoryId">
                    <option value="0">Pilih kategori</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('categoryId') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Judul / isyarat
                <input type="text" wire:model="title" maxlength="255">
                @error('title') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Slug
                <input type="text" wire:model="slug" maxlength="255">
                @error('slug') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Jenis
                <select wire:model="itemType">
                    <option value="alphabet">Huruf</option>
                    <option value="number">Angka</option>
                    <option value="word">Kata</option>
                    <option value="phrase">Frasa</option>
                    <option value="expression">Ekspresi</option>
                </select>
                @error('itemType') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Makna
                <textarea wire:model="meaning" rows="2"></textarea>
                @error('meaning') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Deskripsi
                <textarea wire:model="description" rows="2"></textarea>
                @error('description') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Deskripsi gerakan
                <textarea wire:model="movementDescription" rows="2"></textarea>
                @error('movementDescription') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Contoh penggunaan
                <textarea wire:model="exampleUsage" rows="2"></textarea>
                @error('exampleUsage') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Urutan
                <input type="number" wire:model="sortOrder" min="0">
                @error('sortOrder') <span>{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-1">
                Status
                <select wire:model="status">
                    <option value="draft">Draft</option>
                    <option value="published">Terbit</option>
                    <option value="archived">Arsip</option>
                </select>
                @error('status') <span>{{ $message }}</span> @enderror
            </label>

            <fieldset class="space-y-3 md:col-span-2">
                <legend class="font-medium">Media terpasang (urutan pertama menjadi media utama)</legend>

                @forelse ($this->selectedMedia as $index => $media)
                    <div wire:key="selected-media-{{ $media->id }}" class="flex flex-wrap items-center gap-3">
                        <span>{{ $media->media_type }} · {{ $media->mime_type }} · {{ basename($media->storage_path) }}</span>
                        <button type="button" wire:click="moveMedia({{ $media->id }}, -1)" @disabled($index === 0)>
                            Naik
                        </button>
                        <button
                            type="button"
                            wire:click="moveMedia({{ $media->id }}, 1)"
                            @disabled($index === $this->selectedMedia->count() - 1)
                        >
                            Turun
                        </button>
                        <button type="button" wire:click="removeMedia({{ $media->id }})">Lepas</button>
                    </div>
                @empty
                    <p>Belum ada media terpasang.</p>
                @endforelse

                <label class="grid gap-1">
                    Cari media yang sudah diunggah
                    <input type="search" wire:model.live.debounce.300ms="mediaSearch">
                </label>

                @forelse ($this->mediaOptions as $media)
                    <label wire:key="media-option-{{ $media->id }}" class="flex items-center gap-2">
                        <input type="checkbox" wire:model="selectedMediaIds" value="{{ $media->id }}">
                        <span>{{ $media->media_type }} · {{ $media->mime_type }} · {{ basename($media->storage_path) }}</span>
                    </label>
                @empty
                    <p>Tidak ada media yang cocok.</p>
                @endforelse

                @error('selectedMediaIds') <span>{{ $message }}</span> @enderror
                @error('selectedMediaIds.*') <span>{{ $message }}</span> @enderror
            </fieldset>

            <fieldset class="space-y-3 md:col-span-2">
                <legend class="font-medium">Unggah media baru</legend>
                <input
                    type="file"
                    wire:model="upload"
                    accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime"
                >
                @error('upload') <span>{{ $message }}</span> @enderror

                <label class="grid gap-1">
                    Teks alternatif
                    <input type="text" wire:model="uploadAltText" maxlength="255">
                    @error('uploadAltText') <span>{{ $message }}</span> @enderror
                </label>

                <label class="grid gap-1">
                    Keterangan media
                    <input type="text" wire:model="uploadCaption" maxlength="255">
                    @error('uploadCaption') <span>{{ $message }}</span> @enderror
                </label>
            </fieldset>

            <div class="flex gap-3 md:col-span-2">
                <button type="submit" wire:loading.attr="disabled">Simpan entri</button>
                <button type="button" wire:click="newEntry">Entri baru</button>
            </div>
        </form>
    </section>

    <section class="space-y-4">
        <h2 class="text-xl font-medium">Entri Kamus</h2>
        <label class="grid max-w-md gap-1">
            Cari judul atau slug
            <input type="search" wire:model.live.debounce.300ms="search">
        </label>

        <div class="space-y-3">
            @forelse ($this->items as $item)
                <article wire:key="library-item-{{ $item->id }}" class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-medium">{{ $item->title }}</h3>
                        <p>{{ $item->category->name }} · {{ $item->status }} · {{ $item->slug }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" wire:click="edit({{ $item->id }})">Edit</button>
                        <button
                            type="button"
                            wire:click="delete({{ $item->id }})"
                            wire:confirm="Hapus entri Kamus ini?"
                        >
                            Hapus
                        </button>
                    </div>
                </article>
            @empty
                <p>Belum ada entri Kamus.</p>
            @endforelse
        </div>

        {{ $this->items->links() }}
    </section>
</div>
