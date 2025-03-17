<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'votable_id' => Post::factory(),
            'votable_type' => Post::class,
            'vote_type' => 'up',
        ];
    }

    /**
     * Indicate that the vote is for a post.
     */
    public function forPost(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'votable_id' => Post::factory(),
                'votable_type' => Post::class,
            ];
        });
    }

    /**
     * Indicate that the vote is for a comment.
     */
    public function forComment(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'votable_id' => Comment::factory(),
                'votable_type' => Comment::class,
            ];
        });
    }

    /**
     * Indicate that the vote is an upvote.
     */
    public function upvote(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'vote_type' => 'up',
            ];
        });
    }

    /**
     * Indicate that the vote is a downvote.
     */
    public function downvote(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'vote_type' => 'down',
            ];
        });
    }
}
