<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ban>
 */
class BanFactory extends Factory
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
            'user_id' => User::factory(),
            'community_id' => Community::factory(),
            'banned_by' => User::factory(),
            'reason' => 'Violation of community rules ' . $increment++,
            'expires_at' => now()->addDays(30),
        ];
    }

    /**
     * Indicate that the ban is permanent.
     */
    public function permanent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => null,
            ];
        });
    }

    /**
     * Indicate that the ban is temporary.
     */
    public function temporary(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => fake()->dateTimeBetween('now', '+1 year'),
            ];
        });
    }
}
