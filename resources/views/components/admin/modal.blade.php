@props([
    'show' => false,
    'title' => '',
    'closeAction' => '',
    'maxWidth' => 'max-w-lg',
])

@if ($show)
    <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full {{ $maxWidth }} bg-white border-2 border-b-4 border-[#E4E4E7] rounded-3xl p-6 space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-extrabold text-[#1C1C1E]">{{ $title }}</h3>
                @if ($closeAction)
                    <button
                        type="button"
                        wire:click="{{ $closeAction }}"
                        class="w-8 h-8 rounded-lg hover:bg-zinc-100 flex items-center justify-center font-extrabold text-[#71717A] hover:text-[#1C1C1E] cursor-pointer"
                    >
                        ✕
                    </button>
                @endif
            </div>

            {{ $slot }}
        </div>
    </div>
@endif