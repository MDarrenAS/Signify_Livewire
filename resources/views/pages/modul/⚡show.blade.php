<?php

use App\Models\Module;
use Livewire\Component;

new class extends Component
{
    public function mount(string $modul): void
    {
        $foundModul = Module::where('slug', $modul)->with('lessons')->first();
        abort_if(! $foundModul, 404);

        $firstLesson = $foundModul->lessons->first();
        abort_if(! $firstLesson, 404);

        $this->redirectRoute('modul.part', [
            'modul'  => $foundModul->slug,
            'bagian' => $firstLesson->slug,
        ], navigate: true);
    }
};
?>