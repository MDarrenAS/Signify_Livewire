<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'module_id' => null,
            'lesson_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(),
            'level' => fake()->randomElement(['Pemula', 'Menengah', 'Mahir']),
            'xp_reward' => fake()->randomElement([30, 40, 50, 75, 100]),
            'icon_emoji' => fake()->randomElement(['🎯', '🔤', '👋', '⏱️', '🔢']),
            'icon_url' => null,
            'passing_score' => 70,
            'status' => 'published',
            'sort_order' => 0,
            'time_limit_minutes' => null,
            'attempt_limit' => null,
            'created_by' => User::factory(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
