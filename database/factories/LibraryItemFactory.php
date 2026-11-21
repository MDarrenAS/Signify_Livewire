<?php

namespace Database\Factories;

use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryItem>
 */
class LibraryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => LibraryCategory::factory(),
            'title' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'item_type' => 'word',
            'meaning' => fake()->optional()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'sort_order' => 0,
            'status' => 'published',
            'movement_description' => fake()->optional()->sentence(),
            'example_usage' => fake()->optional()->sentence(),
            'created_by' => null,
        ];
    }
}
