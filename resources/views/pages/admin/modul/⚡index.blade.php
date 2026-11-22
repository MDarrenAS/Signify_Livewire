<?php

use App\Models\Module;
use App\Services\CurriculumService;
use App\Support\MockCurriculum;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')] #[Title('Kelola Modul & Unit — Admin Signify')] class extends Component
{
    use WithFileUploads;

    public array $units = [];
    public string $search = '';
    public string $unitFilter = 'all';

    // State Modal Unit
    public bool $showUnitModal = false;
    public ?int $editUnitIdx = null;
    public int $unitNumber = 1;
    public string $unitLevel = 'TINGKAT DASAR';
    public string $customLevel = '';
    public string $unitTitle = '';
    public string $unitBadge = '';
    public string $unitDescription = '';

    // State Modal Modul
    public bool $showModulModal = false;
    public ?int $editModulUnitIdx = null;
    public ?int $editModulIdx = null;

    public int $formUnitIdx = 0;
    public string $formTitle = '';
    public string $formSubtitle = '';
    public string $formDescription = '';
    public string $formIcon = '🤟';
    public $formIconFile = null;
    public string $formIconUrl = '';
    public int $formXpReward = 50;
    public bool $formIsActive = true;

    public function mount(CurriculumService $service): void
    {
        $this->loadUnits($service);
    }

    public function loadUnits(?CurriculumService $service = null): void
    {
        $service ??= app(CurriculumService::class);
        $this->units = $service->getUnitsForAdmin();
        session(['admin_custom_units' => $this->units]);
    }

    // ==================== UNIT ACTIONS ====================
    public function openCreateUnit(): void
    {
        $this->resetValidation();
        $this->editUnitIdx     = null;
        $this->unitNumber      = count($this->units) + 1;
        $this->unitLevel       = 'TINGKAT DASAR';
        $this->customLevel     = '';
        $this->unitTitle       = '';
        $this->unitDescription = '';
        $this->generateUnitBadge();
        $this->showUnitModal   = true;
    }

    public function updatedUnitNumber(): void
    {
        $this->generateUnitBadge();
    }

    public function updatedUnitLevel(): void
    {
        $this->generateUnitBadge();
    }

    public function updatedCustomLevel(): void
    {
        $this->generateUnitBadge();
    }

    private function generateUnitBadge(): void
    {
        $levelText = $this->unitLevel;
        if ($this->unitLevel === 'CUSTOM') {
            $levelText = strtoupper(trim($this->customLevel));
        }

        $this->unitBadge = 'UNIT ' . $this->unitNumber . ($levelText !== '' ? ' • ' . $levelText : '');
    }

    public function openEditUnit(int $unitIdx): void
    {
        $unit = $this->units[$unitIdx] ?? null;
        if (! $unit) return;

        $this->resetValidation();
        $this->editUnitIdx     = $unitIdx;
        $this->unitNumber      = $unitIdx + 1;
        $this->unitTitle       = $unit['title'] ?? '';
        $this->unitBadge       = $unit['badge'] ?? '';
        $this->unitDescription = $unit['description'] ?? '';

        // Deteksi level dari badge jika ada
        if (str_contains($this->unitBadge, 'TINGKAT MAHIR')) {
            $this->unitLevel = 'TINGKAT MAHIR';
        } elseif (str_contains($this->unitBadge, 'TINGKAT MENENGAH')) {
            $this->unitLevel = 'TINGKAT MENENGAH';
        } elseif (str_contains($this->unitBadge, 'TINGKAT DASAR')) {
            $this->unitLevel = 'TINGKAT DASAR';
        } else {
            $this->unitLevel = 'CUSTOM';
            $parts = explode('•', $this->unitBadge);
            $this->customLevel = trim($parts[1] ?? $this->unitBadge);
        }

        $this->showUnitModal = true;
    }

    public function saveUnit(): void
    {
        $this->validate([
            'unitTitle'       => 'required|string|min:3|max:100',
            'unitBadge'       => 'required|string|max:80',
            'unitDescription' => 'nullable|string|max:300',
        ], [
            'unitTitle.required' => 'Judul unit wajib diisi.',
            'unitBadge.required' => 'Label badge unit wajib diisi.',
        ]);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Informasi Unit berhasil diperbarui!',
        ]);

        $this->loadUnits();
        $this->showUnitModal = false;
    }

    public function deleteUnit(int $unitIdx): void
    {
        $unitName = $this->units[$unitIdx]['title'] ?? 'Unit';
        $this->dispatch('notify', [
            'type'    => 'info',
            'message' => 'Unit "' . $unitName . '" merupakan tingkat kurikulum inti.',
        ]);
    }

    // ==================== MODUL ACTIONS ====================
    public function openCreateModul(int $unitIdx = 0): void
    {
        $this->resetValidation();
        $this->editModulUnitIdx = null;
        $this->editModulIdx     = null;
        $this->formUnitIdx      = $unitIdx;
        $this->formTitle        = '';
        $this->formSubtitle     = '';
        $this->formDescription  = '';
        $this->formIcon         = '🤟';
        $this->formIconFile     = null;
        $this->formIconUrl      = '';
        $this->formXpReward     = 50;
        $this->formIsActive     = true;
        $this->showModulModal   = true;
    }

    public function openEditModul(int $unitIdx, int $modulIdx): void
    {
        $modul = $this->units[$unitIdx]['modules'][$modulIdx] ?? null;
        if (! $modul) return;

        $this->resetValidation();
        $this->editModulUnitIdx = $unitIdx;
        $this->editModulIdx     = $modulIdx;
        $this->formUnitIdx      = $unitIdx;
        $this->formTitle        = $modul['title'] ?? '';
        $this->formSubtitle     = $modul['subtitle'] ?? '';
        $this->formDescription  = $modul['description'] ?? '';
        $this->formIcon         = $modul['icon'] ?? '🤟';
        $this->formIconFile     = null;
        $this->formIconUrl      = $modul['icon_url'] ?? '';
        $this->formXpReward     = (int) ($modul['xp_reward'] ?? 50);
        $this->formIsActive     = ($modul['status'] ?? 'active') !== 'locked';
        $this->showModulModal   = true;
    }

    public function saveModul(): void
    {
        $this->validate([
            'formUnitIdx'  => 'required|integer|min:0',
            'formTitle'    => 'required|string|min:3|max:100',
            'formXpReward' => 'required|integer|min:5|max:1000',
            'formIconFile' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg|max:10240',
        ], [
            'formTitle.required' => 'Judul modul wajib diisi.',
            'formIconFile.mimes' => 'Ikon/gambar harus berupa file JPG, PNG, GIF, WEBP, atau SVG.',
        ]);

        $iconUrl = $this->formIconUrl;
        if ($this->formIconFile) {
            try {
                $path = $this->formIconFile->store('modules/icons', 'public');
                $iconUrl = '/storage/' . $path;
            } catch (\Throwable $e) {
                // fallback
            }
        }

        $levelMap = [
            0 => 'beginner',
            1 => 'intermediate',
            2 => 'advanced',
        ];
        $level = $levelMap[$this->formUnitIdx] ?? ($this->units[$this->formUnitIdx]['level'] ?? 'beginner');

        if ($this->editModulUnitIdx !== null && $this->editModulIdx !== null) {
            $modulData = $this->units[$this->editModulUnitIdx]['modules'][$this->editModulIdx] ?? null;
            if ($modulData && isset($modulData['id'])) {
                Module::where('id', (int) $modulData['id'])->update([
                    'title'          => $this->formTitle,
                    'description'    => $this->formDescription,
                    'thumbnail_path' => $iconUrl ?: null,
                    'level'          => $level,
                    'status'         => $this->formIsActive ? 'published' : 'draft',
                ]);
            }

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Modul "' . $this->formTitle . '" berhasil diperbarui!',
            ]);
        } else {
            $baseSlug = Str::slug($this->formTitle) ?: 'modul-' . rand(100, 999);
            $slug = $baseSlug;
            $counter = 1;
            while (Module::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $maxSort = (int) (Module::where('level', $level)->max('sort_order') ?? 0);

            Module::create([
                'title'          => $this->formTitle,
                'slug'           => $slug,
                'description'    => $this->formDescription,
                'thumbnail_path' => $iconUrl ?: null,
                'level'          => $level,
                'sort_order'     => $maxSort + 1,
                'status'         => $this->formIsActive ? 'published' : 'draft',
                'created_by'     => auth()->id(),
            ]);

            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Modul baru berhasil ditambahkan!',
            ]);
        }

        $this->loadUnits();
        $this->showModulModal = false;
    }

    public function deleteModul(int $unitIdx, int $modulIdx): void
    {
        $title = $this->units[$unitIdx]['modules'][$modulIdx]['title'] ?? 'Modul';
        if (isset($this->units[$unitIdx]['modules'][$modulIdx]['id'])) {
            Module::where('id', (int) $this->units[$unitIdx]['modules'][$modulIdx]['id'])->delete();
        }
        $this->loadUnits();

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Modul "' . $title . '" berhasil dihapus.',
        ]);
    }
};
?>

<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Modul & Kurikulum Belajar"
        subtitle="Atur hierarki Unit pembelajaran dan isi Modul materi serta kuis peragaannya."
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="openCreateUnit"
                class="inline-flex items-center gap-2 bg-white hover:bg-zinc-50 border-2 border-b-4 border-[#E4E4E7] active:translate-y-0.5 active:border-b-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all cursor-pointer"
            >
                <flux:icon.plus class="size-4 text-[#C98A06]" />
                <span>Tambah Unit Baru</span>
            </button>

            <button
                type="button"
                wire:click="openCreateModul(0)"
                class="inline-flex items-center gap-2 bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-extrabold text-[#1C1C1E] transition-all cursor-pointer shadow-xs"
            >
                <flux:icon.book-open class="size-4" />
                <span>Tambah Modul</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filter & Search Bar Responsif --}}
    <div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <input
                type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Cari modul belajar berdasarkan judul atau deskripsi..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-medium focus:outline-none focus:border-[#F9B92F] transition-colors"
            />
            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#71717A] pointer-events-none">
                <flux:icon.magnifying-glass class="size-4" />
            </div>
        </div>

        <select
            wire:model.live="unitFilter"
            class="px-4 py-2.5 rounded-xl border-2 border-[#E4E4E7] text-xs sm:text-sm font-extrabold bg-white text-[#1C1C1E] focus:outline-none focus:border-[#F9B92F] cursor-pointer"
        >
            <option value="all">Semua Unit ({{ count($units) }})</option>
            @foreach ($units as $idx => $unit)
                <option value="{{ $idx }}">{{ $unit['title'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Daftar Unit & Modul (Modular Component) --}}
    <div class="space-y-6">
        @forelse ($units as $uIdx => $unit)
            @if ($unitFilter === 'all' || (string) $unitFilter === (string) $uIdx)
                <x-admin.modul.unit-card
                    :unit="$unit"
                    :u-idx="$uIdx"
                    :search="$search"
                    :can-delete="count($units) > 1"
                />
            @endif
        @empty
            <div class="bg-white border-2 border-dashed border-[#E4E4E7] rounded-3xl p-12 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] mx-auto flex items-center justify-center text-3xl">
                    📚
                </div>
                <h3 class="text-base font-extrabold text-[#1C1C1E]">Belum Ada Unit Pembelajaran</h3>
                <p class="text-xs text-[#71717A] max-w-sm mx-auto">Mulai dengan membuat unit pertama Anda untuk menampung kurikulum modul isyarat BISINDO.</p>
                <button
                    type="button"
                    wire:click="openCreateUnit"
                    class="px-5 py-2.5 rounded-xl bg-[#F9B92F] border-2 border-[#C98A06] text-xs font-extrabold text-[#1C1C1E] cursor-pointer inline-flex items-center gap-1.5"
                >
                    + Buat Unit Pertama
                </button>
            </div>
        @endforelse
    </div>

    {{-- Modal Tambah / Edit Unit --}}
    <x-admin.modul.unit-modal
        :show="$showUnitModal"
        :edit-index="$editUnitIdx"
        :unit-number="$unitNumber"
        :unit-level="$unitLevel"
        :custom-level="$customLevel"
    />

    {{-- Modal Tambah / Edit Modul --}}
    <x-admin.modul.modul-modal
        :show="$showModulModal"
        :edit-index="$editModulIdx"
        :units="$units"
        :icon-file="$formIconFile"
        :icon-url="$formIconUrl"
    />
</div>
