@props([
    'title',
    'subtitle' => null,
    'backUrl' => null,
    'backLabel' => '← Kembali',
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        @if ($backUrl)
            <a href="{{ $backUrl }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-extrabold text-[#71717A] hover:text-[#1C1C1E] mb-1.5">
                {{ $backLabel }}
            </a>
        @endif

        <div class="flex items-center gap-3">
            {{ $prefix ?? '' }}
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-[#1C1C1E] tracking-tight">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="text-xs sm:text-sm text-[#71717A] font-medium mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>