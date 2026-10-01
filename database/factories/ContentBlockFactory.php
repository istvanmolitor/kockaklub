<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentBlock>
 */
class ContentBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'body' => fake()->paragraphs(3, true),
            'sort_order' => 0,
        ];
    }
}
