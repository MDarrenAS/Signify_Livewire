<?php

use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public array $part = [];
    public int $partIndex = 0;

    public bool $showModal = false;
    public string $question = 'Perhatikan peragaan isyarat berikut. Manakah arti atau huruf yang tepat?';
    public string $videoUrl = '';
    public $quizMediaFile = null;
    public array $options = ['', '', '', ''];
    public int $correctIndex = 0;

    public function saveQuestion(): void
    {
        $this->validate([
            'question'      => 'required|string|max:250',
            'options.0'     => 'required|string|max:100',
            'options.1'     => 'required|string|max:100',
            'options.2'     => 'required|string|max:100',
            'options.3'     => 'required|string|max:100',
            'quizMediaFile' => 'nullable|file|mimes:mp4,webm,gif,png,jpg,jpeg,webp|max:30720',
        ], [
            'question.required'  => 'Teks pertanyaan soal kuis wajib diisi.',
            'options.*.required' => 'Seluruh pilihan opsi jawaban (A, B, C, D) wajib diisi.',
            'quizMediaFile.mimes'=> 'Media soal harus berupa file video (MP4, WEBM) atau gambar (GIF, PNG, JPG).',
        ]);

        $finalMediaUrl = $this->videoUrl;

        if ($this->quizMediaFile) {
            try {
                $path = $this->quizMediaFile->store('quizzes/media', 'public');
                $finalMediaUrl = '/storage/' . $path;
            } catch (\Throwable $e) {
                // fallback
            }
        }

        $this->part['questions'][] = [
            'question'      => $this->question,
            'video_url'     => $finalMediaUrl,
            'options'       => array_values($this->options),
            'correct_index' => (int) $this->correctIndex,
        ];

        $this->dispatch('part-updated', index: $this->partIndex, part: $this->part);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Soal kuis baru berhasil ditambahkan!',
        ]);

        $this->reset(['videoUrl', 'quizMediaFile']);
        $this->question     = 'Perhatikan peragaan isyarat berikut. Manakah arti atau huruf yang tepat?';
        $this->options      = ['', '', '', ''];
        $this->correctIndex = 0;
        $this->showModal    = false;
    }

    public function removeQuestion(int $qIdx): void
    {
        unset($this->part['questions'][$qIdx]);
        $this->part['questions'] = array_values($this->part['questions']);
        $this->dispatch('part-updated', index: $this->partIndex, part: $this->part);

        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Soal kuis #' . ($qIdx + 1) . ' berhasil dihapus.',
        ]);
    }
};
?>

<div class="bg-white border-2 border-b-4 border-[#E4E4E7] rounded-2xl overflow-hidden shadow-2xs">
    {{-- Header Card --}}
    <div class="px-5 py-4 bg-[#FAFAFA] border-b-2 border-[#E4E4E7] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md bg-[#FFF8E6] border border-[#F9B92F] text-[10px] font-extrabold text-[#C98A06] uppercase">
                    Kuis Evaluasi
                </span>
                <span class="text-xs font-bold text-[#71717A]">
                    {{ count($part['questions'] ?? []) }} Soal Terdaftar
                </span>
            </div>
            <h2 class="text-base font-extrabold text-[#1C1C1E] mt-0.5">Daftar Pertanyaan & Evaluasi Kuis</h2>
            <p class="text-xs text-[#71717A] font-medium">
                Reward: <strong class="text-[#C98A06]">+{{ $part['xp_reward'] ?? 0 }} XP</strong>
                • Batas Minimum Lulus: <strong class="text-[#15803D]">{{ $part['passing_score'] ?? 80 }}%</strong>
            </p>
        </div>

        <button
            type="button"
            wire:click="$set('showModal', true)"
            class="px-4 py-2.5 rounded-xl bg-[#22B05B] hover:bg-[#1ea052] border-2 border-b-4 border-[#15803D] active:translate-y-0.5 active:border-b-2 text-white text-xs font-extrabold cursor-pointer transition-all self-start sm:self-auto shadow-xs"
        >
            + Tambah Soal Kuis
        </button>
    </div>

    {{-- Daftar Pertanyaan --}}
    <div class="divide-y divide-[#E4E4E7]">
        @forelse ($part['questions'] ?? [] as $qIdx => $q)
            @php
                $media = $q['video_url'] ?? '';
                if ($media && ! str_starts_with($media, 'http') && ! str_starts_with($media, '/storage/')) {
                    try {
                        $media = \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($media, now()->addMinutes(60));
                    } catch (\Throwable) {
                        $media = \Illuminate\Support\Facades\Storage::disk('s3')->url($media);
                    }
                }
                $isImageOrGif = preg_match('/\.(gif|png|jpe?g|webp|svg)($|\?)/i', $media);
            @endphp
            <div class="p-5 flex flex-col sm:flex-row sm:items-start justify-between gap-4 hover:bg-zinc-50/50 transition-colors">
                <div class="flex items-start gap-4 flex-1">
                    {{-- Media Thumbnail --}}
                    @if (!empty($media))
                        <div class="w-16 h-16 rounded-2xl bg-[#18181B] border-2 border-[#E4E4E7] overflow-hidden flex items-center justify-center shrink-0 shadow-2xs">
                            @if ($isImageOrGif)
                                <img src="{{ $media }}" class="w-full h-full object-cover" />
                            @else
                                <video src="{{ $media }}" muted class="w-full h-full object-cover"></video>
                            @endif
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] flex items-center justify-center text-lg font-extrabold text-[#C98A06] shrink-0">
                            Q{{ $qIdx + 1 }}
                        </div>
                    @endif

                    <div class="space-y-2 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#C98A06]">
                                Pertanyaan #{{ $qIdx + 1 }}
                            </span>
                            @if (!empty($media))
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-[#F0FDF4] border border-[#86EFAC] text-[#15803D]">
                                    {{ $isImageOrGif ? 'Gambar/GIF Terpasang' : 'Video Soal Terpasang' }}
                                </span>
                            @endif
                        </div>

                        <p class="text-sm font-extrabold text-[#1C1C1E] leading-snug">
                            {{ $q['question'] }}
                        </p>

                        {{-- Opsi Jawaban --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                            @foreach ($q['options'] ?? [] as $oIdx => $opt)
                                @php
                                    $optText = is_array($opt) ? ($opt['label'] ?? $opt['text'] ?? '') : $opt;
                                    $isCorrect = (int) ($q['correct_index'] ?? 0) === $oIdx;
                                @endphp
                                <div class="px-3 py-2 rounded-xl text-xs font-extrabold border-2 flex items-center justify-between gap-2 {{ $isCorrect ? 'bg-[#F0FDF4] border-[#22B05B] text-[#15803D]' : 'bg-white border-[#E4E4E7] text-[#52525B]' }}">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-extrabold {{ $isCorrect ? 'bg-[#22B05B] text-white' : 'bg-zinc-100 text-[#71717A]' }}">
                                            {{ chr(65 + $oIdx) }}
                                        </span>
                                        <span>{{ $optText }}</span>
                                    </div>
                                    @if ($isCorrect)
                                        <span class="text-[11px] font-extrabold text-[#22B05B]">Kunci ✓</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Hapus Soal --}}
                <div class="shrink-0 self-end sm:self-start">
                    <button
                        type="button"
                        wire:click="removeQuestion({{ $qIdx }})"
                        wire:confirm="Yakin ingin menghapus soal kuis #{{ $qIdx + 1 }}?"
                        class="px-3 py-1.5 rounded-xl border border-[#FCA5A5] bg-[#FEF2F2] hover:bg-[#FEE2E2] text-[#B91C1C] text-xs font-extrabold cursor-pointer transition-colors"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        @empty
            <div class="p-10 text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-[#FFF8E6] border-2 border-[#F9B92F] mx-auto flex items-center justify-center text-xl">
                    🎯
                </div>
                <h4 class="text-sm font-extrabold text-[#1C1C1E]">Belum Ada Soal Kuis</h4>
                <p class="text-xs text-[#71717A] max-w-sm mx-auto">Tambahkan pertanyaan pilihan ganda disertai media video isyarat atau ilustrasi GIF/gambar untuk menguji kemampuan siswa.</p>
            </div>
        @endforelse
    </div>

    {{-- Modal Tambah Soal Kuis --}}
    <x-admin.modal :show="$showModal" title="Tambah Pertanyaan Kuis Baru" close-action="$set('showModal', false)" max-width="max-w-lg">
        <form wire:submit="saveQuestion" class="space-y-4">
            <div>
                <label class="block text-xs font-extrabold text-[#1C1C1E] mb-1">Teks Pertanyaan</label>
                <textarea
                    wire:model="question"
                    rows="2"
                    placeholder="Contoh: Perhatikan isyarat pada media di atas. Manakah huruf atau arti yang tepat?"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#E4E4E7] text-xs sm:text-sm font-semibold focus:outline-none focus:border-[#F9B92F]"
                ></textarea>
                @error('question') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </div>

            {{-- Uploader Media Soal: Video, GIF, atau Gambar --}}
            <x-admin.file-uploader
                model="quizMediaFile"
                url-model="videoUrl"
                :current-url="$videoUrl"
                accept="video/mp4,video/webm,image/*,.gif"
                label="Media Soal Kuis (Video / GIF / Gambar)"
                hint="Unggah file MP4, GIF, PNG, atau JPG peragaan isyarat soal kuis (Maks. 30MB)"
            >
                @if ($quizMediaFile)
                    @php $isImg = str_contains($quizMediaFile->getMimeType(), 'image'); @endphp
                    <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-44 flex items-center justify-center p-2">
                        @if ($isImg)
                            <img src="{{ $quizMediaFile->temporaryUrl() }}" class="h-full object-contain" />
                        @else
                            <video src="{{ $quizMediaFile->temporaryUrl() }}" controls muted loop class="h-full object-contain"></video>
                        @endif
                    </div>
                @elseif (trim($videoUrl) !== '')
                    @php $isImageOrGif = preg_match('/\.(gif|png|jpe?g|webp|svg)($|\?)/i', $videoUrl); @endphp
                    <div class="rounded-xl overflow-hidden bg-[#18181B] aspect-video max-h-44 flex items-center justify-center p-2">
                        @if ($isImageOrGif)
                            <img src="{{ $videoUrl }}" class="h-full object-contain" />
                        @else
                            <video src="{{ $videoUrl }}" controls muted loop class="h-full object-contain"></video>
                        @endif
                    </div>
                @endif
            </x-admin.file-uploader>

            {{-- Pilihan Jawaban A, B, C, D --}}
            <div class="space-y-2 pt-1">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-extrabold text-[#1C1C1E]">
                        Pilihan Jawaban (Tandai bulatan untuk Kunci Jawaban Benar)
                    </label>
                    <span class="text-[11px] font-extrabold text-[#22B05B]">
                        Kunci: Opsi {{ chr(65 + $correctIndex) }}
                    </span>
                </div>

                @foreach ([0, 1, 2, 3] as $optIdx)
                    <div class="flex items-center gap-2.5 p-1 rounded-xl border border-[#E4E4E7] bg-[#FAFAFA] hover:bg-white transition-colors">
                        <label class="flex items-center gap-2 pl-2.5 cursor-pointer select-none">
                            <input
                                type="radio"
                                wire:model.live="correctIndex"
                                value="{{ $optIdx }}"
                                name="correct_answer_radio"
                                class="text-[#22B05B] focus:ring-[#22B05B] size-4 cursor-pointer"
                            />
                            <span class="w-5 text-xs font-extrabold {{ (int) $correctIndex === $optIdx ? 'text-[#22B05B]' : 'text-[#71717A]' }}">
                                {{ chr(65 + $optIdx) }}.
                            </span>
                        </label>
                        <input
                            type="text"
                            wire:model="options.{{ $optIdx }}"
                            placeholder="Teks Opsi {{ chr(65 + $optIdx) }}"
                            class="flex-1 px-3 py-2 rounded-lg border border-transparent hover:border-[#E4E4E7] focus:border-[#F9B92F] text-xs sm:text-sm font-semibold bg-white focus:outline-none"
                        />
                    </div>
                @endforeach
                @error('options.*') <span class="text-[11px] font-bold text-[#B91C1C]">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E4E4E7]">
                <button
                    type="button"
                    wire:click="$set('showModal', false)"
                    class="px-4 py-2 rounded-xl border border-[#E4E4E7] text-xs font-extrabold text-[#52525B] hover:bg-zinc-100 cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl bg-[#F9B92F] hover:bg-[#fabf45] border-2 border-b-4 border-[#C98A06] active:translate-y-0.5 active:border-b-2 text-xs font-extrabold text-[#1C1C1E] cursor-pointer"
                >
                    Simpan Pertanyaan
                </button>
            </div>
        </form>
    </x-admin.modal>
</div>
