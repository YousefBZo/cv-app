<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
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
            'headline' => fake()->jobTitle(),
            'summary' => fake()->paragraph(),
            'location' => fake()->city() . ', ' . fake()->country(),
            'phone' => fake()->phoneNumber(),
            'contact_email' => fake()->safeEmail(),
            'website' => fake()->url(),
            'linkedin' => 'https://linkedin.com/in/' . fake()->userName(),
            'github' => 'https://github.com/' . fake()->userName(),
        ];
    }
}
