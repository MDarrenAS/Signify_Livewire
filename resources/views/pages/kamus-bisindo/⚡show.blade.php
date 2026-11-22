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

<div class="mx-auto max-w-5xl space-y-8">
    <a
        href="{{ route('kamus.index') }}"
        wire:navigate
        class="inline-flex items-center gap-2 text-sm font-medium text-[#828282] transition hover:text-[#B58A2A]"
    >
        <span aria-hidden="true">←</span>
        Kembali ke Kamus
    </a>

    <section class="rounded-3xl border border-[#EEEEEE] bg-white p-6 sm:p-8">
        <div class="space-y-5">
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-[#FEF5E0] px-4 py-2 text-xs font-semibold text-[#A87919]">
                    {{ $this->item->category->name }}
                </span>

                <span class="text-xs text-[#999999]">
                    {{ ucfirst($this->item->item_type) }}
                </span>
            </div>

            <div class="space-y-3">
                <h1 class="text-3xl font-bold text-[#252525] sm:text-4xl">
                    {{ $this->item->title }}
                </h1>

                @if ($this->item->meaning)
                    <p class="text-base leading-7 text-[#686868]">
                        {{ $this->item->meaning }}
                    </p>
                @endif
            </div>
        </div>
    </section>

    <section class="space-y-5">
        <div>
            <h2 class="text-xl font-bold text-[#252525]">
                Media Isyarat
            </h2>
            <p class="mt-1 text-sm text-[#828282]">
                Perhatikan gambar atau video berikut untuk mempelajari isyarat.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            @forelse ($this->item->media as $media)
                <figure
                    wire:key="kamus-media-{{ $media->id }}"
                    class="overflow-hidden rounded-2xl border border-[#EAEAEA] bg-white"
                >
                    <div class="bg-[#FEF8EA] p-3">
                        @if ($media->media_type === 'image')
                            <img
                                src="{{ $media->temporaryUrl() }}"
                                alt="{{ $media->alt_text ?: $media->pivot->caption ?: $this->item->title }}"
                                class="mx-auto max-h-96 w-full rounded-xl object-contain"
                            >
                        @else
                            <video
                                controls
                                preload="metadata"
                                class="w-full rounded-xl"
                            >
                                <source
                                    src="{{ $media->temporaryUrl() }}"
                                    type="{{ $media->mime_type }}"
                                >
                                Peramban Anda tidak mendukung video.
                            </video>
                        @endif
                    </div>

                    @if ($media->pivot->caption)
                        <figcaption class="p-4 text-sm text-[#828282]">
                            {{ $media->pivot->caption }}
                        </figcaption>
                    @endif
                </figure>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-[#D8D8D8] bg-white p-8 text-center">
                    <p class="text-sm text-[#828282]">
                        Belum ada media pembelajaran untuk isyarat ini.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    @if ($this->item->description || $this->item->movement_description || $this->item->example_usage)
        <section class="space-y-5">
            <h2 class="text-xl font-bold text-[#252525]">
                Informasi Isyarat
            </h2>

            @if ($this->item->description)
                <div class="rounded-2xl border border-[#EAEAEA] bg-white p-6">
                    <h3 class="font-semibold text-[#252525]">
                        Deskripsi
                    </h3>
                    <p class="mt-3 text-sm leading-7 text-[#828282]">
                        {{ $this->item->description }}
                    </p>
                </div>
            @endif

            @if ($this->item->movement_description)
                <div class="rounded-2xl border border-[#EAEAEA] bg-white p-6">
                    <h3 class="font-semibold text-[#252525]">
                        Deskripsi Gerakan
                    </h3>
                    <p class="mt-3 text-sm leading-7 text-[#828282]">
                        {{ $this->item->movement_description }}
                    </p>
                </div>
            @endif

            @if ($this->item->example_usage)
                <div class="rounded-2xl border border-[#F5DFAE] bg-[#FEF8EA] p-6">
                    <h3 class="font-semibold text-[#946C19]">
                        Contoh Penggunaan
                    </h3>
                    <p class="mt-3 text-sm leading-7 text-[#686868]">
                        {{ $this->item->example_usage }}
                    </p>
                </div>
            @endif
        </section>
    @endif

    <div class="pt-2">
        <a
            href="{{ route('kamus.index') }}"
            wire:navigate
            class="inline-flex items-center gap-2 rounded-xl bg-[#F9B92F] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#E5A51F]"
        >
            <span aria-hidden="true">←</span>
            Kembali ke Kamus
        </a>
    </div>
</div>