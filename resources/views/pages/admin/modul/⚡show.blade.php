<?php

use App\Models\LessonItem;
use App\Models\Module;
use App\Models\ModuleLesson;
use App\Models\ModuleLessonQuestion;
use App\Services\CurriculumService;
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
        $this->loadModuleData();
    }

    public function loadModuleData(): void
    {
        $service = app(CurriculumService::class);
        $found = $service->getModuleWithParts($this->modulSlug);

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

        $lessonId = $part['id'] ?? null;
        if (! $lessonId) {
            $lesson = ModuleLesson::where('module_id', $this->modulData['id'] ?? 0)
                ->where('slug', $part['slug'] ?? '')
                ->first();
            $lessonId = $lesson?->id;
        }

        if ($lessonId) {
            $lessonModel = ModuleLesson::find($lessonId);
            if ($lessonModel) {
                $lessonModel->update([
                    'title'         => $part['title'] ?? $lessonModel->title,
                    'xp_reward'     => $part['xp_reward'] ?? $lessonModel->xp_reward,
                    'passing_score' => $part['passing_score'] ?? $lessonModel->passing_score,
                ]);

                if (($part['type'] ?? '') === 'lesson' && isset($part['items'])) {
                    LessonItem::where('module_lesson_id', $lessonId)->delete();
                    foreach ($part['items'] as $i => $item) {
                        LessonItem::create([
                            'module_lesson_id' => $lessonId,
                            'library_item_id'  => $item['library_item_id'] ?? null,
                            'symbol'           => $item['symbol'] ?? null,
                            'title'            => $item['title'] ?? 'Item ' . ($i + 1),
                            'hand_type'        => $item['hand_type'] ?? '1 Tangan',
                            'video_url'        => $item['video_url'] ?? null,
                            'instruction'      => $item['instruction'] ?? $item['description'] ?? null,
                            'tip'              => $item['una_tip'] ?? $item['tip'] ?? null,
                            'sort_order'       => $i + 1,
                        ]);
                    }
                } elseif (($part['type'] ?? '') === 'quiz' && isset($part['questions'])) {
                    ModuleLessonQuestion::where('module_lesson_id', $lessonId)->delete();
                    foreach ($part['questions'] as $i => $q) {
                        ModuleLessonQuestion::create([
                            'module_lesson_id' => $lessonId,
                            'question'         => $q['question'] ?? 'Pertanyaan',
                            'video_url'        => $q['video_url'] ?? $q['media_url'] ?? null,
                            'symbol_hint'      => $q['symbol_hint'] ?? null,
                            'options'          => array_values($q['options'] ?? []),
                            'correct_index'    => (int) ($q['correct_index'] ?? 0),
                            'explanation'      => $q['explanation'] ?? null,
                            'sort_order'       => $i + 1,
                        ]);
                    }
                }
            }
        }

        $this->loadModuleData();
    }

    public function savePart(): void
    {
        $this->validate([
            'partTitle' => 'required|string|min:2|max:80',
            'partXp'    => 'required|integer|min:5|max:500',
        ], [
            'partTitle.required' => 'Judul bagian materi/kuis wajib diisi.',
        ]);

        $module = Module::where('slug', $this->modulSlug)->firstOrFail();
        $order = (int) ($module->lessons()->max('sort_order') ?? 0) + 1;
        $baseSlug = Str::slug($this->partTitle) ?: 'bagian';
        $slug = "{$baseSlug}-{$order}";

        $newLesson = ModuleLesson::create([
            'module_id'     => $module->id,
            'title'         => $this->partTitle,
            'slug'          => $slug,
            'type'          => $this->partType,
            'xp_reward'     => $this->partXp,
            'passing_score' => $this->partPassingScore,
            'sort_order'    => $order,
        ]);

        $this->loadModuleData();
        $this->activePartIndex = max(0, count($this->modulData['parts'] ?? []) - 1);
        $this->reset(['partTitle', 'showPartModal']);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Bagian baru "' . $newLesson->title . '" berhasil ditambahkan!',
        ]);
    }

    public function deletePart(int $index): void
    {
        $part = $this->modulData['parts'][$index] ?? null;
        $title = $part['title'] ?? 'Bagian';

        if ($part && isset($part['id'])) {
            ModuleLesson::where('id', (int) $part['id'])->delete();
        }

        $this->loadModuleData();
        $this->activePartIndex = max(0, min($this->activePartIndex, count($this->modulData['parts'] ?? []) - 1));

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
