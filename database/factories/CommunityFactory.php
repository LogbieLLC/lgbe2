<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Community>
 */
class CommunityFactory extends Factory
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
            'name' => 'Community ' . $increment++,
            'description' => 'Description for community ' . $increment,
            'rules' => "1. Be respectful\n2. No spam\n3. Follow guidelines",
            'created_by' => User::factory(),
        ];
    }
}
