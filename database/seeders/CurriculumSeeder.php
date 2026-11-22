<?php

namespace Database\Seeders;

use App\Models\LessonLibraryItem;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\Module;
use App\Models\ModuleLesson;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
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

            $globalModSort = 1;
            foreach ($units as $unitData) {
                $modules = $unitData['modules'] ?? [];
                foreach ($modules as $modData) {
                    $level = 'beginner';
                    if (str_contains($unitData['badge'] ?? '', 'MENENGAH')) {
                        $level = 'intermediate';
                    } elseif (str_contains($unitData['badge'] ?? '', 'MAHIR')) {
                        $level = 'advanced';
                    }

                    $module = Module::updateOrCreate(
                        ['slug' => $modData['slug']],
                        [
                            'title' => $modData['title'],
                            'description' => $modData['description'] ?? null,
                            'thumbnail_path' => $modData['icon_url'] ?? null,
                            'level' => $level,
                            'sort_order' => $globalModSort++,
                            'status' => ($modData['status'] ?? '') === 'locked' ? 'draft' : 'published',
                        ]
                    );

                    $parts = $modData['parts'] ?? [];
                    foreach ($parts as $partIndex => $partData) {
                        $lessonType = $partData['type'] ?? 'lesson';

                        $lesson = ModuleLesson::updateOrCreate(
                            [
                                'module_id' => $module->id,
                                'slug' => $partData['slug'],
                            ],
                            [
                                'title' => $partData['title'],
                                'lesson_type' => $lessonType,
                                'summary' => $partData['subtitle'] ?? null,
                                'description' => $partData['category'] ?? null,
                                'sort_order' => $partData['order'] ?? ($partIndex + 1),
                                'status' => 'published',
                                'estimated_minutes' => $lessonType === 'quiz' ? 10 : 5,
                            ]
                        );

                        if ($lessonType === 'lesson') {
                            $items = $partData['items'] ?? [];
                            $savedItems = [];

                            foreach ($items as $itemIdx => $itemData) {
                                $itemSlug = Str::slug($itemData['title']);

                                $category = str_contains($module->slug, 'angka')
                                    ? $kamusAngkaCategory
                                    : $kamusAbjadCategory;

                                $libraryItem = LibraryItem::updateOrCreate(
                                    ['slug' => $itemSlug],
                                    [
                                        'category_id' => $category->id,
                                        'title' => $itemData['title'],
                                        'item_type' => str_contains($module->slug, 'angka') ? 'number' : 'alphabet',
                                        'meaning' => $itemData['symbol'] ?? $itemData['title'],
                                        'description' => $itemData['instruction'] ?? null,
                                        'movement_description' => $itemData['instruction'] ?? null,
                                        'example_usage' => $itemData['una_tip'] ?? ($itemData['tip'] ?? null),
                                        'status' => 'published',
                                        'sort_order' => $itemIdx + 1,
                                    ]
                                );

                                LessonLibraryItem::updateOrCreate(
                                    [
                                        'lesson_id' => $lesson->id,
                                        'library_item_id' => $libraryItem->id,
                                    ],
                                    [
                                        'sort_order' => $itemIdx + 1,
                                    ]
                                );

                                $savedItems[] = [
                                    'id' => $libraryItem->id,
                                    'library_item_id' => $libraryItem->id,
                                    'order' => $itemIdx + 1,
                                    'symbol' => $itemData['symbol'] ?? strtoupper(substr($itemData['title'], 0, 3)),
                                    'title' => $itemData['title'],
                                    'hand_type' => $itemData['hand_type'] ?? '2 Tangan',
                                    'video_url' => $itemData['video_url'] ?? null,
                                    'instruction' => $itemData['instruction'] ?? null,
                                    'description' => $itemData['instruction'] ?? null,
                                    'una_tip' => $itemData['una_tip'] ?? ($itemData['tip'] ?? null),
                                    'tip' => $itemData['una_tip'] ?? ($itemData['tip'] ?? null),
                                ];
                            }

                            $lesson->update([
                                'content' => json_encode(['items' => $savedItems], JSON_UNESCAPED_UNICODE),
                            ]);
                        } elseif ($lessonType === 'quiz') {
                            $questions = $partData['questions'] ?? [];

                            $quiz = Quiz::updateOrCreate(
                                ['lesson_id' => $lesson->id],
                                [
                                    'module_id' => $module->id,
                                    'title' => $lesson->title,
                                    'slug' => $lesson->slug,
                                    'description' => $partData['subtitle'] ?? 'Kuis Evaluasi',
                                    'passing_score' => $partData['passing_score'] ?? 80,
                                    'status' => 'published',
                                ]
                            );

                            QuizQuestion::where('quiz_id', $quiz->id)->delete();

                            $savedQuestions = [];
                            foreach ($questions as $qIdx => $qData) {
                                $options = array_values($qData['options'] ?? []);
                                $correctIndex = (int) ($qData['correct_index'] ?? 0);

                                $qq = QuizQuestion::create([
                                    'quiz_id' => $quiz->id,
                                    'question_text' => $qData['question'] ?? 'Pertanyaan',
                                    'question_type' => 'multiple_choice',
                                    'points' => 1,
                                    'sort_order' => $qIdx + 1,
                                    'explanation' => $qData['explanation'] ?? null,
                                ]);

                                foreach ($options as $optIdx => $optText) {
                                    QuizOption::create([
                                        'question_id' => $qq->id,
                                        'option_text' => $optText,
                                        'is_correct' => $optIdx === $correctIndex,
                                        'sort_order' => $optIdx + 1,
                                    ]);
                                }

                                $savedQuestions[] = [
                                    'id' => $qq->id,
                                    'order' => $qIdx + 1,
                                    'question' => $qData['question'] ?? 'Pertanyaan',
                                    'video_url' => $qData['video_url'] ?? ($qData['media_url'] ?? null),
                                    'symbol_hint' => $qData['symbol_hint'] ?? null,
                                    'options' => $options,
                                    'correct_index' => $correctIndex,
                                    'explanation' => $qData['explanation'] ?? null,
                                ];
                            }

                            $lesson->update([
                                'content' => json_encode(['questions' => $savedQuestions], JSON_UNESCAPED_UNICODE),
                            ]);
                        }
                    }
                }
            }
        });
    }
}
