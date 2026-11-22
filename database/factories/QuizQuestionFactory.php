<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'library_item_id' => null,
            'library_media_id' => null,
            'question_text' => fake()->sentence().'?',
            'question_type' => fake()->randomElement(['sign_to_word', 'word_to_sign', 'media_to_sign']),
            'media_url' => null,
            'points' => 1,
            'sort_order' => 0,
            'explanation' => null,
        ];
    }
}
