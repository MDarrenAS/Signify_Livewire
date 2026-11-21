<?php

use App\Models\LibraryItem;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kamus BISINDO')] class extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    #[Computed]
    public function item(): LibraryItem
    {
        return LibraryItem::query()
            ->where('slug', $this->slug)
            ->where('status', 'published')
            ->whereHas('category', fn (Builder $query) => $query->where('status', 'published'))
            ->with(['category', 'media'])
            ->firstOrFail();
    }
};
?>

<div class="mx-auto max-w-4xl space-y-6 p-6">
    <a href="{{ route('kamus.index') }}" wire:navigate class="text-sm underline">
        Kembali ke Kamus
    </a>

    <header class="space-y-2">
        <p>{{ $this->item->category->name }} · {{ ucfirst($this->item->item_type) }}</p>
        <h1 class="text-3xl font-semibold">{{ $this->item->title }}</h1>

        @if ($this->item->meaning)
            <p>{{ $this->item->meaning }}</p>
        @endif

        @if ($this->item->description)
            <p>{{ $this->item->description }}</p>
        @endif

        @if ($this->item->movement_description)
            <p><strong>Deskripsi gerakan:</strong> {{ $this->item->movement_description }}</p>
        @endif

        @if ($this->item->example_usage)
            <p><strong>Contoh penggunaan:</strong> {{ $this->item->example_usage }}</p>
        @endif
    </header>

    <section aria-label="Media pembelajaran" class="grid gap-4 sm:grid-cols-2">
        @forelse ($this->item->media as $media)
            <figure wire:key="kamus-media-{{ $media->id }}" class="space-y-2">
                @if ($media->media_type === 'image')
                    <img
                        src="{{ $media->temporaryUrl() }}"
                        alt="{{ $media->alt_text ?: $media->pivot->caption ?: $this->item->title }}"
                        class="h-auto max-w-full rounded"
                    >
                @else
                    <video controls preload="metadata" class="h-auto max-w-full rounded">
                        <source src="{{ $media->temporaryUrl() }}" type="{{ $media->mime_type }}">
                        Peramban Anda tidak mendukung video.
                    </video>
                @endif

                @if ($media->pivot->caption)
                    <figcaption>{{ $media->pivot->caption }}</figcaption>
                @endif
            </figure>
        @empty
            <p>Belum ada media pembelajaran.</p>
        @endforelse
    </section>
</div>
