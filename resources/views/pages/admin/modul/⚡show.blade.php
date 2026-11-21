<?php

use App\Support\MockCurriculum;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Isi Modul — Admin Signify')] class extends Component
{
    public ?array $modulData = null;
    public string $modulSlug = '';
    public int $activePartIndex = 0;

    public bool $showPartModal = false;
    public string $partType = 'lesson';
    public string $partTitle = '';
    public int $partXp = 20;
    public int $partPassingScore = 80;

    public function mount(string $modul): void
    {
        $this->modulSlug = $modul;
        $allUnits = session('admin_custom_units', MockCurriculum::getUnits());

        $found = collect($allUnits)
            ->flatMap(fn ($u) => $u['modules'] ?? [])
            ->firstWhere('slug', $modul);

        abort_if(! $found, 404);
        $this->modulData = $found;
    }

    public function getActivePartProperty(): ?array
    {
        return $this->modulData['parts'][$this->activePartIndex] ?? null;
    }

    #[On('part-updated')]
    public function syncPart(int $index, array $part): void
    {
        $this->modulData['parts'][$index] = $part;
        $this->saveToSession();
    }

    private function saveToSession(): void
    {
        // TODO (Backend): Ganti update array session ini dengan query update Eloquent
        $allUnits = session('admin_custom_units', MockCurriculum::getUnits());

        foreach ($allUnits as &$unit) {
            foreach ($unit['modules'] as &$m) {
                if (($m['slug'] ?? '') === $this->modulSlug) {
                    $m = $this->modulData;
                    break 2;
                }
            }
        }

        session(['admin_custom_units' => $allUnits]);
    }

    public function savePart(): void
    {
        $this->validate([
            'partTitle' => 'required|string|min:2|max:80',
            'partXp'    => 'required|integer|min:5|max:500',
        ], [
            'partTitle.required' => 'Judul bagian materi/kuis wajib diisi.',
        ]);

        $order = count($this->modulData['parts'] ?? []) + 1;

        $newPart = [
            'slug'          => Str::slug($this->partTitle) . '-' . $order,
            'order'         => $order,
            'type'          => $this->partType,
            'title'         => $this->partTitle,
            'xp_reward'     => $this->partXp,
            'passing_score' => $this->partPassingScore,
            'items'         => [],
            'questions'     => [],
        ];

        $this->modulData['parts'][] = $newPart;
        $this->saveToSession();

        $this->activePartIndex = count($this->modulData['parts']) - 1;
        $this->reset(['partTitle', 'showPartModal']);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Bagian baru "' . $newPart['title'] . '" berhasil ditambahkan!',
        ]);
    }

    public function deletePart(int $index): void
    {
        $title = $this->modulData['parts'][$index]['title'] ?? 'Bagian';
        unset($this->modulData['parts'][$index]);
        $this->modulData['parts'] = array_values($this->modulData['parts']);

        // Rapikan ulang nomor urut (order)
        foreach ($this->modulData['parts'] as $i => &$p) {
            $p['order'] = $i + 1;
        }

        $this->saveToSession();
        $this->activePartIndex = max(0, min($this->activePartIndex, count($this->modulData['parts']) - 1));

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Bagian "' . $title . '" berhasil dihapus.',
        ]);
    }
};
?>

<div class="space-y-6">
    {{-- Header Modul --}}
    <x-admin.page-header
        :title="$modulData['title'] ?? 'Modul'"
        :subtitle="$modulData['subtitle'] ?? $modulData['description'] ?? 'Kelola urutan materi peragaan isyarat dan kuis evaluasi'"
        :back-url="route('admin.modul.index')"
        back-label="← Kembali ke Daftar Modul"
    >
        <x-slot:prefix>
            <div class="w-12 h-12 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-2xl shrink-0 overflow-hidden shadow-2xs">
                @if (!empty($modulData['icon_url']))
                    <img src="{{ $modulData['icon_url'] }}" alt="{{ $modulData['title'] }}" class="w-full h-full object-cover" />
                @else
                    {{ $modulData['icon'] ?? '🤟' }}
                @endif
            </div>
        </x-slot:prefix>

        <x-slot:actions>
            <button
                type="button"
                wire:click="$set('showPartModal', true)"
                class="inline-flex items-center gap-2 bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all cursor-pointer shadow-xs"
            >
                <flux:icon.plus class="size-4" />
                <span>Tambah Bagian</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Tab Navigasi Bagian-bagian Modul --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-[#E4E4E7]">
        @forelse ($modulData['parts'] ?? [] as $idx => $part)
            @php $isQuizTab = ($part['type'] ?? 'lesson') === 'quiz'; @endphp
            <div class="flex items-center shrink-0">
                <button
                    type="button"
                    wire:click="$set('activePartIndex', {{ $idx }})"
                    class="px-4 py-2.5 rounded-xl border-2 text-xs font-extrabold transition-all flex items-center gap-2 cursor-pointer {{ $activePartIndex === $idx ? 'bg-[#FFF8E6] border-[#F9B92F] text-[#1C1C1E] shadow-2xs' : 'bg-white border-[#E4E4E7] text-[#71717A] hover:text-[#1C1C1E]' }}"
                >
                    <span>Bagian {{ $part['order'] ?? ($idx + 1) }}: {{ $part['title'] }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $isQuizTab ? 'bg-[#F9B92F] text-[#1C1C1E]' : 'bg-[#F4F4F5] text-[#52525B]' }}">
                        {{ $isQuizTab ? 'KUIS' : 'MATERI' }}
                    </span>
                </button>

                @if ($activePartIndex === $idx && count($modulData['parts']) > 1)
                    <button
                        type="button"
                        wire:click="deletePart({{ $idx }})"
                        wire:confirm="Yakin ingin menghapus bagian '{{ $part['title'] }}' ini?"
                        class="ml-1 p-2 rounded-lg text-zinc-400 hover:text-red-600 hover:bg-red-50 text-xs font-bold cursor-pointer"
                        title="Hapus Bagian Ini"
                    >
                        ✕
                    </button>
                @endif
            </div>
        @empty
            <div class="py-2 text-xs font-semibold text-[#71717A]">
                Modul ini belum memiliki bagian. Klik tombol "+ Tambah Bagian" di atas untuk menambahkan materi peragaan atau kuis.
            </div>
        @endforelse
    </div>

    {{-- Editor Konten (Lesson Editor atau Quiz Editor) --}}
    @if ($this->activePart)
        @if (($this->activePart['type'] ?? 'lesson') === 'quiz')
            <livewire:admin.modul.quiz-editor
                :part="$this->activePart"
                :part-index="$activePartIndex"
                :key="'quiz-editor-'.$activePartIndex.'-'.count($this->activePart['questions'] ?? [])"
            />
        @else
            <livewire:admin.modul.lesson-editor
                :part="$this->activePart"
                :part-index="$activePartIndex"
                :key="'lesson-editor-'.$activePartIndex.'-'.count($this->activePart['items'] ?? [])"
            />
        @endif
    @else
        {{-- Empty State jika belum ada bagian sama sekali --}}
        <div class="bg-white border-2 border-dashed border-[#E4E4E7] rounded-3xl p-10 text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] mx-auto flex items-center justify-center text-2xl">
                📖
            </div>
            <h3 class="text-base font-extrabold text-[#1C1C1E]">Modul Belum Memiliki Bagian</h3>
            <p class="text-xs text-[#71717A] max-w-md mx-auto">
                Tambahkan bagian pertama untuk modul ini. Anda dapat memilih tipe <strong>Materi Belajar (Lesson)</strong> untuk peragaan gerakan isyarat atau <strong>Kuis Evaluasi (Quiz)</strong>.
            </p>
            <button
                type="button"
                wire:click="$set('showPartModal', true)"
                class="px-5 py-2.5 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] text-xs font-extrabold text-[#1C1C1E] cursor-pointer inline-flex items-center gap-1.5"
            >
                <flux:icon.plus class="size-4" />
                <span>+ Buat Bagian Pertama</span>
            </button>
        </div>
    @endif

    {{-- Modal Tambah Bagian (Modular Component) --}}
    <x-admin.modul.part-modal
        :show="$showPartModal"
        :part-type="$partType"
    />
</div>
