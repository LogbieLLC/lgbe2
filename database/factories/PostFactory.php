<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $increment = 1;

        return [
            'title' => 'Post Title ' . $increment++,
            'content' => 'Content for post ' . $increment,
            'type' => 'text',
            'community_id' => Community::factory(),
            'user_id' => User::factory(),
        ];
    }
}
