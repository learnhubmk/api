<?php

namespace App\Website\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Framework\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Website\Models\Model>
 */
class AuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->name,
            'last_name' => fake()->lastName,
            'user_id' => User::all()->random()->id,
        ];
    }
}
