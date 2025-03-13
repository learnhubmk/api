<?php

namespace App\Website\Database\Factories;

use App\Website\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Framework\Models\User;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'user_id' => User::query()->inRandomOrder()->value('id'),
        ];
    }
}
