<?php

namespace App\Website\Database\Factories;

use App\Website\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Framework\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Model>
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
            'first_name' => fake()->name,
            'last_name' => fake()->lastName,
            'user_id' => User::all()->random()->id,
        ];
    }
}
