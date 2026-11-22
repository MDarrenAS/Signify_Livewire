<?php

namespace Database\Factories;

use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizOption>
 */
class QuizOptionFactory extends Factory
{
    protected $model = QuizOption::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => QuizQuestion::factory(),
            'library_item_id' => null,
            'library_media_id' => null,
            'option_text' => fake()->word(),
            'media_url' => null,
            'is_correct' => false,
            'sort_order' => 0,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
        ]);
    }
}
