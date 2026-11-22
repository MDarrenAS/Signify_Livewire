<?php

namespace Database\Seeders;

use App\Models\LessonItem;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\Module;
use App\Models\ModuleLesson;
use App\Models\ModuleLessonQuestion;
use App\Models\Unit;
use App\Support\MockCurriculum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            // Pastikan kategori kamus dasar ada
            $kamusAbjadCategory = LibraryCategory::updateOrCreate(
                ['slug' => 'abjad'],
                [
                    'name' => 'Abjad & Alfabet',
                    'description' => 'Kosakata abjad visual BISINDO dari A hingga Z.',
                    'sort_order' => 1,
                    'status' => 'published',
                ]
            );

            $kamusAngkaCategory = LibraryCategory::updateOrCreate(
                ['slug' => 'angka'],
                [
                    'name' => 'Angka & Hitungan',
                    'description' => 'Kosakata angka dan nominal bilangan BISINDO.',
                    'sort_order' => 2,
                    'status' => 'published',
                ]
            );

            $units = MockCurriculum::getUnits();

            foreach ($units as $unitIndex => $unitData) {
                $unit = Unit::updateOrCreate(
                    ['unit_number' => $unitData['id'] ?? ($unitIndex + 1)],
                    [
                        'level' => 'TINGKAT DASAR',
                        'badge' => $unitData['badge'] ?? 'UNIT '.($unitIndex + 1).' • TINGKAT DASAR',
                        'title' => $unitData['title'] ?? 'Unit '.($unitIndex + 1),
                        'description' => $unitData['description'] ?? null,
                        'sort_order' => $unitIndex + 1,
                        'is_active' => ! ($unitData['is_locked'] ?? false),
                    ]
                );

                $modules = $unitData['modules'] ?? [];
                foreach ($modules as $modIndex => $modData) {
                    $module = Module::updateOrCreate(
                        ['slug' => $modData['slug']],
                        [
                            'unit_id' => $unit->id,
                            'title' => $modData['title'],
                            'icon_emoji' => $modData['icon_emoji'] ?? '🤟',
                            'icon_url' => $modData['icon_url'] ?? null,
                            'subtitle' => $modData['subtitle'] ?? null,
                            'description' => $modData['description'] ?? null,
                            'xp_reward' => $modData['xp_reward'] ?? 50,
                            'visual_content' => $modData['visual_content'] ?? null,
                            'tag' => $modData['tag'] ?? null,
                            'is_active' => ($modData['status'] ?? '') !== 'locked',
                            'sort_order' => $modIndex + 1,
                        ]
                    );

                    $parts = $modData['parts'] ?? [];
                    foreach ($parts as $partIndex => $partData) {
                        $lesson = ModuleLesson::updateOrCreate(
                            [
                                'module_id' => $module->id,
                                'slug' => $partData['slug'],
                            ],
                            [
                                'title' => $partData['title'],
                                'type' => $partData['type'] ?? 'lesson',
                                'subtitle' => $partData['subtitle'] ?? null,
                                'category' => $partData['category'] ?? null,
                                'badge_text' => $partData['badge_text'] ?? null,
                                'xp_reward' => $partData['xp_reward'] ?? 10,
                                'passing_score' => $partData['passing_score'] ?? 80,
                                'sort_order' => $partData['order'] ?? ($partIndex + 1),
                            ]
                        );

                        if (($partData['type'] ?? 'lesson') === 'lesson') {
                            $items = $partData['items'] ?? [];
                            foreach ($items as $itemIdx => $item) {
                                $itemTitle = $item['title'] ?? 'Item '.($itemIdx + 1);
                                $itemSlug = Str::slug($itemTitle);

                                // Hubungkan ke entri Kamus (LibraryItem)
                                $category = str_contains(strtolower($module->title), 'angka')
                                    ? $kamusAngkaCategory
                                    : $kamusAbjadCategory;

                                $libraryItem = LibraryItem::updateOrCreate(
                                    ['slug' => $itemSlug],
                                    [
                                        'category_id' => $category->id,
                                        'title' => $itemTitle,
                                        'item_type' => str_contains(strtolower($module->title), 'angka') ? 'number' : 'alphabet',
                                        'meaning' => 'Isyarat BISINDO untuk '.$itemTitle,
                                        'movement_description' => $item['instruction'] ?? null,
                                        'status' => 'published',
                                    ]
                                );

                                LessonItem::updateOrCreate(
                                    [
                                        'module_lesson_id' => $lesson->id,
                                        'title' => $itemTitle,
                                    ],
                                    [
                                        'library_item_id' => $libraryItem->id,
                                        'symbol' => $item['symbol'] ?? null,
                                        'hand_type' => $item['hand_type'] ?? '1 Tangan',
                                        'video_url' => $item['video_url'] ?? null,
                                        'instruction' => $item['instruction'] ?? null,
                                        'tip' => $item['una_tip'] ?? $item['tip'] ?? null,
                                        'sort_order' => $item['order'] ?? ($itemIdx + 1),
                                    ]
                                );
                            }
                        } elseif (($partData['type'] ?? '') === 'quiz') {
                            $questions = $partData['questions'] ?? [];
                            foreach ($questions as $qIdx => $q) {
                                ModuleLessonQuestion::updateOrCreate(
                                    [
                                        'module_lesson_id' => $lesson->id,
                                        'sort_order' => $q['order'] ?? ($qIdx + 1),
                                    ],
                                    [
                                        'question' => $q['question'],
                                        'video_url' => $q['media_url'] ?? $q['video_url'] ?? null,
                                        'symbol_hint' => $q['symbol_hint'] ?? null,
                                        'options' => array_values($q['options'] ?? []),
                                        'correct_index' => (int) ($q['correct_index'] ?? 0),
                                        'explanation' => $q['explanation'] ?? null,
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        });
    }
}
