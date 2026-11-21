<?php

use App\Support\MockCurriculum;
use Livewire\Component;

new class extends Component
{
    public function mount(string $modul): void
    {
        $foundModul = collect(MockCurriculum::getUnits())
            ->flatMap(fn (array $unit) => $unit['modules'] ?? [])
            ->firstWhere('slug', $modul);

        $firstPart = $foundModul['parts'][0] ?? null;

        abort_if(! $foundModul || ! $firstPart, 404);

        $this->redirectRoute('modul.part', [
            'modul'  => $foundModul['slug'],
            'bagian' => $firstPart['slug'],
        ], navigate: true);
    }
};
?>