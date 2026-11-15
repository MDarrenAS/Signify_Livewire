<?php

use App\Models\LibraryItem;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
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
        ->with(['category','media'])
        ->orderBy('sort_order')
        ->paginate(12);
    }
};
?>

<div>
        <h1>Kamus BISINDO</h1>

    @forelse ($this->items as $item)
        <article wire:key="library-item-{{ $item->id }}">
            <h2>{{ $item->title }}</h2>
            <p>{{ $item->category->name }}</p>

            @if ($item->meaning)
                <p>{{ $item->meaning }}</p>
            @endif

            <a href="{{ route('kamus.show', ['slug' => $item->slug]) }}" wire:navigate>
                Lihat detail
            </a>
        </article>
    @empty
        <p>Belum ada isyarat yang diterbitkan.</p>
    @endforelse

    {{ $this->items->links() }}
</div>
