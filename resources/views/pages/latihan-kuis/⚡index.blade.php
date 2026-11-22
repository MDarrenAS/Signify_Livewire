
<?php

use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Latihan dan Kuis')] class extends Component
{
    public array $packages = [];

    public function mount(): void
    {
        $allPackages = session('admin_quiz_packages', []);

        $this->packages = array_values(array_filter($allPackages, function ($package) {
            return ($package['is_active'] ?? false)
                && count($package['questions'] ?? []) > 0;
        }));
    }
};
?>

<div class="space-y-7 pb-10">

    <header class="space-y-2">
        <span class="inline-flex rounded-full bg-[#FFF3D5] px-3 py-1 text-xs font-extrabold uppercase tracking-wider text-[#C98A06]">
            Asah Kemampuanmu
        </span>

        <h1 class="text-2xl font-extrabold tracking-tight text-[#1C1C1E] sm:text-[28px]">
            Latihan dan Kuis
        </h1>

        <p class="max-w-2xl text-sm font-medium text-[#828282] sm:text-[15px]">
            Uji pemahamanmu tentang Bahasa Isyarat Indonesia melalui latihan interaktif dan tantangan kuis yang menyenangkan.
        </p>
    </header>

    <section class="relative overflow-hidden rounded-2xl border-2 border-[#F9D78F] bg-[#FDE9BF] p-6 sm:p-8">
        <div class="relative z-10 max-w-xl space-y-3">
            <span class="text-xs font-extrabold uppercase tracking-widest text-[#A87513]">
                Belajar sambil bermain
            </span>

            <h2 class="text-xl font-extrabold leading-snug text-[#1C1C1E] sm:text-2xl">
                Satu langkah kecil, kemampuan bahasa isyarat semakin hebat!
            </h2>

            <p class="text-sm font-medium leading-relaxed text-[#6F5A32]">
                Pilih latihan sesuai kemampuanmu, jawab setiap tantangan, dan kumpulkan XP dari setiap pencapaian.
            </p>
        </div>

        <div class="absolute -right-8 -bottom-12 h-40 w-40 rounded-full bg-[#F9B92F]/30 sm:h-52 sm:w-52"></div>
        <div class="absolute -top-10 right-10 h-24 w-24 rounded-full bg-white/30"></div>
    </section>

    <section class="space-y-4">
        <div>
            <h2 class="text-lg font-extrabold text-[#1C1C1E] sm:text-xl">
                Pilih Paket Latihan
            </h2>
            <p class="mt-1 text-sm text-[#828282]">
                Temukan tantangan yang sesuai dengan kemampuanmu.
            </p>
        </div>

        @forelse ($packages as $package)
            @php
                $slug = Str::slug($package['title']);
                $level = $package['level'] ?? 'Pemula';
                $levelColor = match ($level) {
                    'Menengah' => 'bg-[#EAF1FF] text-[#315A9B]',
                    'Mahir' => 'bg-[#F3E8FF] text-[#7E22CE]',
                    default => 'bg-[#F0FDF4] text-[#15803D]',
                };
            @endphp

            <article wire:key="quiz-package-{{ $slug }}"
                class="group flex flex-col gap-5 rounded-2xl border-2 border-b-4 border-[#E4E4E7] bg-white p-5 transition-all hover:-translate-y-1 hover:border-[#F9B92F] sm:flex-row sm:items-center sm:p-6">

                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-[#F9B92F] bg-[#FFF8E6] text-3xl">
                    @if (!empty($package['icon_url']))
                        <img src="{{ $package['icon_url'] }}" alt="{{ $package['title'] }}" class="h-full w-full object-cover">
                    @else
                        {{ $package['icon_emoji'] ?? '🎯' }}
                    @endif
                </div>

                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-lg px-2.5 py-1 text-xs font-extrabold {{ $levelColor }}">
                            {{ $level }}
                        </span>

                        <span class="text-xs font-semibold text-[#828282]">
                            {{ count($package['questions'] ?? []) }} Soal
                        </span>
                    </div>

                    <h3 class="text-base font-extrabold text-[#1C1C1E] sm:text-lg">
                        {{ $package['title'] }}
                    </h3>

                    <p class="text-sm leading-relaxed text-[#828282]">
                        {{ $package['subtitle'] ?? '' }}
                    </p>
                </div>

                <div class="flex items-center justify-between gap-4 border-t border-[#E4E4E7] pt-4 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                    <div class="flex items-center gap-1.5 text-sm font-extrabold text-[#C98A06]">
                        <span>★</span>
                        <span>+{{ $package['xp_reward'] ?? 0 }} XP</span>
                    </div>

                    <a href="{{ route('latihan.show', ['slug' => $slug]) }}"
                        wire:navigate
                        class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-b-4 border-[#C98A06] bg-[#F9B92F] px-5 py-2.5 text-sm font-extrabold text-[#1C1C1E] transition-all hover:bg-[#FAD078] active:translate-y-0.5 active:border-b-2">
                        Mulai Kuis
                        <span>→</span>
                    </a>
                </div>
            </article>
        @empty
            <div class="flex flex-col items-center rounded-2xl border-2 border-dashed border-[#E4E4E7] bg-white px-5 py-12 text-center sm:py-16">
                <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-2xl bg-[#FFF8E6] text-4xl">
                    📝
                </div>

                <h3 class="text-lg font-extrabold text-[#1C1C1E]">
                    Belum Ada Paket Latihan
                </h3>

                <p class="mt-2 max-w-md text-sm leading-relaxed text-[#828282]">
                    Belum ada paket kuis yang tersedia. Silakan kembali lagi nanti untuk mencoba tantangan baru!
                </p>
            </div>
        @endforelse
    </section>

</div>