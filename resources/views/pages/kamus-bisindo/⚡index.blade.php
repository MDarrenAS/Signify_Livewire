
<?php

use App\Models\LibraryItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Kamus BISINDO')] class extends Component
{
    use WithPagination;

    #[Computed]
    public function items(): LengthAwarePaginator
    {
        return LibraryItem::query()
            ->where('status', 'published')
            ->whereHas('category', fn (Builder $query) => $query->where('status', 'published'))
            ->with(['category', 'media'])
            ->orderBy('sort_order')
            ->paginate(12);
    }
};

?>

<div class="space-y-8">
    <section class="space-y-3">
        <div class="inline-flex items-center gap-2 rounded-full bg-[#FEF5E0] px-4 py-2 text-xs font-semibold text-[#A87919]">
            <span class="h-2 w-2 rounded-full bg-[#F9B92F]"></span>
            Pustaka Bahasa Isyarat
        </div>

        <h1 class="text-3xl font-bold text-[#252525]">
            Kamus BISINDO
        </h1>

        <p class="max-w-2xl text-sm leading-7 text-[#828282]">
            Temukan berbagai kosakata Bahasa Isyarat Indonesia dan pelajari
            bagaimana menggunakannya untuk berkomunikasi sehari-hari.
        </p>
    </section>

    <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->items as $item)
            <article
                wire:key="library-item-{{ $item->id }}"
                class="group flex flex-col overflow-hidden rounded-2xl border border-[#EAEAEA] bg-white transition duration-200 hover:-translate-y-1 hover:border-[#F9B92F] hover:shadow-lg"
            >
                <div class="flex min-h-36 items-center justify-center bg-[#FEF8EA] p-6">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white text-3xl font-bold text-[#F0AD20] shadow-sm">
                        {{ mb_strtoupper(mb_substr($item->title, 0, 1)) }}
                    </div>
                </div>

                <div class="flex flex-1 flex-col p-5">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <span class="rounded-full bg-[#FEF5E0] px-3 py-1 text-xs font-medium text-[#A87919]">
                            {{ $item->category->name }}
                        </span>

                        <span class="text-xs text-[#999999]">
                            {{ ucfirst($item->item_type) }}
                        </span>
                    </div>

                    <h2 class="text-lg font-bold text-[#252525]">
                        {{ $item->title }}
                    </h2>

                    @if ($item->meaning)
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-[#828282]">
                            {{ $item->meaning }}
                        </p>
                    @else
                        <p class="mt-2 text-sm text-[#A0A0A0]">
                            Pelajari isyarat ini lebih lanjut.
                        </p>
                    @endif

                    <a
                        href="{{ route('kamus.show', ['slug' => $item->slug]) }}"
                        wire:navigate
                        class="mt-6 flex items-center justify-between rounded-xl bg-[#FEF5E0] px-4 py-3 text-sm font-semibold text-[#946C19] transition group-hover:bg-[#F9B92F] group-hover:text-white"
                    >
                        Lihat detail
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-[#D8D8D8] bg-white px-6 py-16 text-center">
                <h2 class="text-lg font-semibold text-[#252525]">
                    Belum ada isyarat
                </h2>
                <p class="mt-2 text-sm text-[#828282]">
                    Isyarat yang sudah diterbitkan akan muncul di sini.
                </p>
            </div>
        @endforelse
    </section>

    <div>
        {{ $this->items->links() }}
    </div>
</div>