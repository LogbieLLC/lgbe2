<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
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
            'content' => 'Comment content ' . $increment++,
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'parent_comment_id' => null,
        ];
    }

    /**
     * Indicate that the comment is a reply to another comment.
     */
    public function asReply(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_comment_id' => Comment::factory(),
            ];
        });
    }
}
