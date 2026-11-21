<?php

namespace Database\Factories;

use App\Models\LibraryMedia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LibraryMedia>
 */
class LibraryMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'library_item_id' => null,
            'media_type' => 'image',
            'storage_path' => 'kamus/'.Str::uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 1024,
            'alt_text' => fake()->optional()->sentence(4),
            'sort_order' => 0,
            'is_primary' => false,
            'thumbnail_path' => null,
            'caption' => null,
            'duration_seconds' => null,
            'uploaded_by' => null,
        ];
    }
}
