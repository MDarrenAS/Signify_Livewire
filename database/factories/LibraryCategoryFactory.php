<?php

namespace Database\Factories;

use App\Models\LibraryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryCategory>
 */
class LibraryCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->optional()->sentence(),
            'sort_order' => 0,
            'status' => 'published',
            'created_by' => null,
        ];
    }
}
